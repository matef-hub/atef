<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Document;
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

        $activeCasesCount = Document::query()
            ->where(function ($query) use ($today) {
                $query->whereNull('docu_expiry_date')
                    ->orWhereDate('docu_expiry_date', '>=', $today);
            })
            ->count();

        $pendingSignaturesCount = Contract::query()
            ->get(['id', 'sign'])
            ->filter(fn (Contract $contract) => count($contract->sign) < $requiredSignatureCount)
            ->count();

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

        $urgentAlertsCount = $expiringRentContracts->count() + $expiringDocuments->count();

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
            'expiringRentContracts',
            'expiringDocuments',
            'recentContracts',
            'latestActivities',
            'contractInflow'
        ));
    }

    protected function buildLatestActivities(): Collection
    {
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
                createTitle: 'تمت إضافة ملف قانوني',
                updateTitle: 'تم تحديث ملف قانوني',
                description: collect([
                    $document->docu_name ?: 'مستند بدون اسم',
                    $document->docu_issu_from,
                ])->filter()->implode(' • '),
                badge: 'الملفات',
                badgeClass: 'warning',
                icon: 'tabler-gavel',
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
                badgeClass: 'info',
                icon: 'tabler-home-dollar',
                editUrl: route('rents.edit', $rent),
            ));

        return $contractActivities
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
