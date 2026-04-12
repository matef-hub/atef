<?php

namespace App\Http\Controllers;

use App\Http\Requests\CaseHearingRequest;
use App\Models\CaseHearing;
use App\Models\LegalCase;
use Illuminate\Http\Request;

class CaseHearingController extends Controller
{
    public function index(Request $request)
    {
        $selectedCase = $request->filled('case')
            ? LegalCase::query()->find($request->integer('case'))
            : null;

        $hearings = CaseHearing::query()
            ->with('legalCase')
            ->when($selectedCase, fn ($query) => $query->where('legal_case_id', $selectedCase->id))
            ->latest('hearing_date')
            ->get();

        return view('content.hearings.index', [
            'hearings' => $hearings,
            'selectedCase' => $selectedCase,
        ]);
    }

    public function create(Request $request)
    {
        return view('content.hearings.create', [
            'cases' => $this->caseOptions(),
            'selectedCaseId' => $request->integer('case') ?: null,
        ]);
    }

    public function store(CaseHearingRequest $request)
    {
        $hearing = CaseHearing::create($request->payload());

        return redirect()
            ->route('hearings.index', ['case' => $hearing->legal_case_id])
            ->with('success', 'تم تسجيل الجلسة بنجاح.');
    }

    public function show(CaseHearing $hearing)
    {
        return redirect()->route('hearings.edit', $hearing);
    }

    public function edit(CaseHearing $hearing)
    {
        return view('content.hearings.edit', [
            'hearing' => $hearing,
            'cases' => $this->caseOptions(),
            'selectedCaseId' => $hearing->legal_case_id,
        ]);
    }

    public function update(CaseHearingRequest $request, CaseHearing $hearing)
    {
        $hearing->fill($request->payload());
        $hearing->save();

        return redirect()
            ->route('hearings.index', ['case' => $hearing->legal_case_id])
            ->with('success', 'تم تحديث بيانات الجلسة بنجاح.');
    }

    public function destroy(CaseHearing $hearing)
    {
        $caseId = $hearing->legal_case_id;
        $hearing->delete();

        return redirect()
            ->route('hearings.index', ['case' => $caseId])
            ->with('success', 'تم حذف الجلسة بنجاح.');
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
}
