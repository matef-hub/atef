<?php

namespace App\Http\Controllers;

use App\Models\CaseHearing;
use App\Models\Contract;
use App\Models\Document;
use App\Models\LegalCase;
use App\Models\Rent;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->startOfDay();
        $renewalWindowEnd = $today->copy()->addDays(7)->endOfDay();
        $requiredSignatureCount = count(Contract::signOptions());

        $totalContracts = Contract::query()->count();
        $activeCasesCount = LegalCase::query()->count();
        $pendingSignaturesCount = Contract::query()
            ->get(['id', 'sign'])
            ->filter(fn (Contract $contract) => count($contract->sign) < $requiredSignatureCount)
            ->count();

        $appealDeadlineCases = LegalCase::query()
            ->where('case_type', LegalCase::TYPE_CIVIL)
            ->whereNotNull('judgment_issued_at')
            ->where('has_appeal', false)
            ->whereDate('judgment_issued_at', '<=', $today)
            ->whereDate('judgment_issued_at', '>=', $today->copy()->subDays(40))
            ->orderByDesc('judgment_issued_at')
            ->get()
            ->map(fn (LegalCase $case) => [
                'title' => $case->case_number ?: 'قضية مدنية',
                'subtitle' => $case->parties_summary,
                'deadline_at' => optional($case->appeal_deadline_at)->format('Y-m-d'),
                'days_remaining' => $case->appeal_deadline_at
                    ? $today->diffInDays($case->appeal_deadline_at, false)
                    : null,
                'edit_url' => route('cases.edit', $case),
            ]);

        $upcomingCaseHearings = CaseHearing::query()
            ->with('legalCase')
            ->whereNotNull('next_hearing_at')
            ->whereDate('next_hearing_at', '>=', $today)
            ->whereDate('next_hearing_at', '<=', $renewalWindowEnd)
            ->orderBy('next_hearing_at')
            ->get()
            ->map(fn (CaseHearing $hearing) => [
                'title' => $hearing->legalCase?->case_number ?: 'جلسة قادمة',
                'subtitle' => $hearing->legalCase?->parties_summary,
                'hearing_at' => optional($hearing->next_hearing_at)->format('Y-m-d'),
                'decision' => $hearing->court_decision,
                'edit_url' => route('hearings.edit', $hearing),
            ]);

        $expiringRentContracts = Rent::query()
            ->whereNotNull('date_end')
            ->whereDate('date_end', '>=', $today)
            ->whereDate('date_end', '<=', $renewalWindowEnd)
            ->orderBy('date_end')
            ->get()
            ->map(fn (Rent $rent) => [
                'title' => $rent->home_data ?: 'عقد إيجار بدون عنوان',
                'subtitle' => collect([$rent->tenant_name, $rent->renter_name])
                    ->filter()
                    ->implode(' • '),
                'expires_at' => optional($rent->date_end)->format('Y-m-d'),
                'days_remaining' => $rent->date_end
                    ? $today->diffInDays($rent->date_end, false)
                    : null,
                'renew_url' => route('rents.edit', $rent),
            ]);

        $expiringDocuments = Document::query()
            ->whereNotNull('docu_expiry_date')
            ->whereDate('docu_expiry_date', '>=', $today)
            ->whereDate('docu_expiry_date', '<=', $renewalWindowEnd)
            ->orderBy('docu_expiry_date')
            ->get()
            ->map(fn (Document $document) => [
                'title' => $document->docu_name ?: 'مستند قانوني',
                'number' => $document->doc_numer ?: 'بدون رقم',
                'expires_at' => optional($document->docu_expiry_date)->format('Y-m-d'),
                'review_url' => route('documents.edit', $document),
            ]);

        $caseOverview = [
            'civil' => LegalCase::query()->where('case_type', LegalCase::TYPE_CIVIL)->count(),
            'criminal' => LegalCase::query()->where('case_type', LegalCase::TYPE_CRIMINAL)->count(),
            'appeal_windows' => $appealDeadlineCases->count(),
            'upcoming_hearings' => $upcomingCaseHearings->count(),
        ];

        $urgentAlertsCount = $appealDeadlineCases->count()
            + $upcomingCaseHearings->count()
            + $expiringRentContracts->count()
            + $expiringDocuments->count();

        $recentContracts = Contract::query()
            ->latest()
            ->take(8)
            ->get()
            ->map(function (Contract $contract) use ($requiredSignatureCount) {
                $status = $this->resolveContractStatus($contract, $requiredSignatureCount);

                return [
                    'number' => $contract->contr_number ?: '—',
                    'project' => $contract->proje_name ?: 'غير محدد',
                    'supplier' => $contract->suppli_name ?: 'غير محدد',
                    'supplier_initials' => $this->makeInitials($contract->suppli_name),
                    'status_label' => $status['label'],
                    'status_class' => $status['class'],
                    'edit_url' => route('contracts.edit', $contract),
                ];
            });

        $latestActivities = $this->buildLatestActivities();
        $contractInflow = $this->buildContractInflow();

        return view('content.dashboard.index', compact(
            'totalContracts',
            'activeCasesCount',
            'pendingSignaturesCount',
            'urgentAlertsCount',
            'caseOverview',
            'appealDeadlineCases',
            'upcomingCaseHearings',
            'expiringRentContracts',
            'expiringDocuments',
            'recentContracts',
            'latestActivities',
            'contractInflow'
        ));
    }

    protected function buildLatestActivities(): Collection
    {
        $caseActivities = LegalCase::query()
            ->latest('updated_at')
            ->take(4)
            ->get()
            ->map(fn (LegalCase $case) => $this->makeActivityItem(
                model: $case,
                createTitle: 'تمت إضافة قضية جديدة',
                updateTitle: 'تم تحديث قضية',
                description: collect([
                    $case->case_number ? 'رقم ' . $case->case_number : null,
                    $case->parties_summary ?: null,
                ])->filter()->implode(' • '),
                badge: 'القضايا',
                badgeClass: 'warning',
                icon: 'tabler-scale',
                editUrl: route('cases.edit', $case),
            ));

        $hearingActivities = CaseHearing::query()
            ->with('legalCase')
            ->latest('updated_at')
            ->take(4)
            ->get()
            ->map(fn (CaseHearing $hearing) => $this->makeActivityItem(
                model: $hearing,
                createTitle: 'تم تسجيل جلسة جديدة',
                updateTitle: 'تم تحديث جلسة',
                description: collect([
                    $hearing->legalCase?->case_number,
                    $hearing->hearing_date ? 'جلسة ' . $hearing->hearing_date->format('Y-m-d') : null,
                    $hearing->roll_number ? 'رول ' . $hearing->roll_number : null,
                ])->filter()->implode(' • '),
                badge: 'الجلسات',
                badgeClass: 'info',
                icon: 'tabler-calendar-event',
                editUrl: route('hearings.edit', $hearing),
            ));

        $contractActivities = Contract::query()
            ->latest('updated_at')
            ->take(4)
            ->get()
            ->map(fn (Contract $contract) => $this->makeActivityItem(
                model: $contract,
                createTitle: 'تمت إضافة عقد جديد',
                updateTitle: 'تم تحديث عقد',
                description: collect([
                    $contract->contr_number ? 'رقم ' . $contract->contr_number : null,
                    $contract->proje_name ?: 'مشروع غير محدد',
                ])->filter()->implode(' • '),
                badge: 'العقود',
                badgeClass: 'primary',
                icon: 'tabler-file-text',
                editUrl: route('contracts.edit', $contract),
            ));

        $documentActivities = Document::query()
            ->latest('updated_at')
            ->take(4)
            ->get()
            ->map(fn (Document $document) => $this->makeActivityItem(
                model: $document,
                createTitle: 'تمت إضافة مستند قانوني',
                updateTitle: 'تم تحديث مستند قانوني',
                description: collect([
                    $document->docu_name ?: 'مستند بدون اسم',
                    $document->docu_issu_from,
                ])->filter()->implode(' • '),
                badge: 'المستندات',
                badgeClass: 'secondary',
                icon: 'tabler-file-certificate',
                editUrl: route('documents.edit', $document),
            ));

        $rentActivities = Rent::query()
            ->latest('updated_at')
            ->take(4)
            ->get()
            ->map(fn (Rent $rent) => $this->makeActivityItem(
                model: $rent,
                createTitle: 'تمت إضافة عقد إيجار',
                updateTitle: 'تم تحديث عقد إيجار',
                description: collect([
                    $rent->home_data ?: 'وحدة غير محددة',
                    $rent->tenant_name,
                ])->filter()->implode(' • '),
                badge: 'الإيجارات',
                badgeClass: 'success',
                icon: 'tabler-home-dollar',
                editUrl: route('rents.edit', $rent),
            ));

        return $caseActivities
            ->concat($hearingActivities)
            ->concat($contractActivities)
            ->concat($documentActivities)
            ->concat($rentActivities)
            ->sortByDesc('timestamp')
            ->take(6)
            ->values();
    }

    protected function makeActivityItem(
        object $model,
        string $createTitle,
        string $updateTitle,
        string $description,
        string $badge,
        string $badgeClass,
        string $icon,
        string $editUrl
    ): array {
        $isUpdated = $model->updated_at && $model->created_at
            ? $model->updated_at->diffInMinutes($model->created_at) >= 1
            : false;

        $timestamp = $isUpdated ? $model->updated_at : $model->created_at;

        return [
            'title' => $isUpdated ? $updateTitle : $createTitle,
            'description' => $description,
            'badge' => $badge,
            'badge_class' => $badgeClass,
            'icon' => $icon,
            'url' => $editUrl,
            'time' => $timestamp
                ? $timestamp->locale(app()->getLocale())->diffForHumans()
                : 'الآن',
            'timestamp' => $timestamp?->getTimestamp() ?? 0,
        ];
    }

    protected function buildContractInflow(): array
    {
        $startMonth = now()->startOfMonth()->subMonths(5);
        $months = collect(range(0, 5))
            ->map(fn (int $offset) => $startMonth->copy()->addMonths($offset));

        return [
            'categories' => $months
                ->map(fn (CarbonInterface $month) => $month->locale(app()->getLocale())->translatedFormat('M Y'))
                ->all(),
            'series' => $months
                ->map(fn (CarbonInterface $month) => Contract::query()
                    ->whereBetween('created_at', [
                        $month->copy()->startOfMonth(),
                        $month->copy()->endOfMonth(),
                    ])
                    ->count())
                ->all(),
        ];
    }

    protected function resolveContractStatus(Contract $contract, int $requiredSignatureCount): array
    {
        $completedSignatures = count($contract->sign);

        if ($completedSignatures === 0) {
            return ['label' => 'جديد', 'class' => 'secondary'];
        }

        if ($completedSignatures < $requiredSignatureCount) {
            return ['label' => 'بانتظار توقيع', 'class' => 'info'];
        }

        return ['label' => 'مكتمل', 'class' => 'success'];
    }

    protected function makeInitials(?string $value): string
    {
        $parts = collect(preg_split('/\s+/u', trim((string) $value)))
            ->filter()
            ->take(2)
            ->map(fn (string $part) => mb_substr($part, 0, 1, 'UTF-8'))
            ->implode('');

        return $parts !== '' ? $parts : 'LG';
    }
}
