<?php

namespace App\Http\Controllers;

use App\Http\Requests\CaseHearingRequest;
use App\Models\CaseHearing;
use App\Models\LegalCase;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class CaseHearingCalendarController extends Controller
{
    public function index(): View
    {
        return view('content.hearings.calendar', [
            'cases' => $this->caseOptions(),
        ]);
    }

    public function events(Request $request): JsonResponse
    {
        $start = $this->parseDate($request->input('start'));
        $end = $this->parseDate($request->input('end'));
        $types = collect((array) $request->input('types', []))
            ->filter(fn ($type) => in_array($type, array_keys(LegalCase::typeOptions()), true))
            ->values();

        $hearings = CaseHearing::query()
            ->with('legalCase')
            ->whereNotNull('next_hearing_at')
            ->when($start, fn ($query) => $query->whereDate('next_hearing_at', '>=', $start))
            ->when($end, fn ($query) => $query->whereDate('next_hearing_at', '<', $end))
            ->when($types->isNotEmpty(), function ($query) use ($types) {
                $query->whereHas('legalCase', fn ($caseQuery) => $caseQuery->whereIn('case_type', $types));
            })
            ->orderBy('next_hearing_at')
            ->get()
            ->map(fn (CaseHearing $hearing) => $this->toCalendarEvent($hearing))
            ->values();

        return response()->json($hearings);
    }

    public function store(CaseHearingRequest $request): JsonResponse
    {
        $request->validate([
            'next_hearing_at' => ['required', 'date', 'after_or_equal:hearing_date'],
        ], [], [
            'next_hearing_at' => 'الجلسة القادمة',
        ]);

        $hearing = CaseHearing::create($request->payload());
        $hearing->load('legalCase');

        return response()->json([
            'message' => 'تم تسجيل الجلسة على التقويم بنجاح.',
            'event' => $this->toCalendarEvent($hearing),
        ], 201);
    }

    protected function caseOptions()
    {
        return LegalCase::query()
            ->latest()
            ->get()
            ->map(function (LegalCase $case) {
                return [
                    'id' => $case->id,
                    'label' => trim($case->case_number . ' - ' . $case->parties_summary, ' -'),
                    'type_label' => $case->case_type_label,
                ];
            });
    }

    protected function toCalendarEvent(CaseHearing $hearing): array
    {
        $case = $hearing->legalCase;
        $caseType = $case?->case_type ?? LegalCase::TYPE_CIVIL;
        $title = collect([$case?->case_number, $case?->parties_summary])->filter()->implode(' - ');

        return [
            'id' => (string) $hearing->id,
            'title' => $title ?: 'جلسة قضية',
            'start' => $hearing->next_hearing_at?->format('Y-m-d'),
            'allDay' => true,
            'display' => 'block',
            'extendedProps' => [
                'calendar' => $caseType,
                'color' => $this->eventColor($caseType),
                'hearing_date' => $hearing->hearing_date?->format('Y-m-d'),
                'next_hearing_at' => $hearing->next_hearing_at?->format('Y-m-d'),
                'roll_number' => $hearing->roll_number,
                'court_decision' => $hearing->court_decision,
                'notes' => $hearing->notes,
                'edit_url' => route('hearings.edit', $hearing),
                'list_url' => route('hearings.index', ['case' => $hearing->legal_case_id]),
                'case' => [
                    'id' => $case?->id,
                    'number' => $case?->case_number,
                    'type' => $caseType,
                    'type_label' => $case?->case_type_label,
                    'parties' => $case?->parties_summary,
                    'court_name' => $case?->primary_court_name ?: $case?->appeal_court_name,
                    'circuit_number' => $case?->circuit_number ?: $case?->appeal_circuit_number,
                    'edit_url' => $case ? route('cases.edit', $case) : null,
                ],
            ],
        ];
    }

    protected function eventColor(string $caseType): string
    {
        return match ($caseType) {
            LegalCase::TYPE_CRIMINAL => 'warning',
            LegalCase::TYPE_CIVIL => 'primary',
            default => 'secondary',
        };
    }

    protected function parseDate(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (Throwable) {
            return null;
        }
    }
}
