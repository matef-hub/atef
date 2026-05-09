<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Rent;
use Illuminate\Support\Collection;

class LegalReportController extends Controller
{
    public function index()
    {
        $today = now()->startOfDay();
        $requiredSignatureCount = count(Contract::signOptions());

        $contractRows = Contract::query()
            ->latest('updated_at')
            ->get()
            ->map(fn (Contract $contract) => $this->mapContractRow($contract, $requiredSignatureCount));

        $leaseRows = Rent::query()
            ->latest('updated_at')
            ->get()
            ->map(fn (Rent $rent) => $this->mapLeaseRow($rent, $today));

        $reportRows = $contractRows
            ->concat($leaseRows)
            ->sortByDesc(fn (array $row) => $row['sort_date'] ?: '0000-00-00')
            ->values();

        $availableDates = $reportRows
            ->pluck('filter_date')
            ->filter()
            ->sort()
            ->values();

        return view('content.reports.legal-center', [
            'reportRows' => $reportRows,
            'today' =>  now()->format('Y-m-d'),
            'generatedOn' => now()->format('Y-m-d'),
            'defaultPeriodFrom' => now()->startOfMonth()->format('Y-m-d'),
            'defaultPeriodTo' => now()->format('Y-m-d'),
        ]);
    }

    protected function mapContractRow(Contract $contract, int $requiredSignatureCount): array
    {
        $contractorName = $contract->suppli_name ?: 'غير محدد';
        $contractorId = $this->resolveContractorIdentifier($contractorName, $contract->id);
        $status = $this->resolveContractStatus($contract, $requiredSignatureCount);
        $signatureSummary = collect($contract->sign)
            ->filter()
            ->values();
        $filterDate = $contract->Contar_date?->format('Y-m-d')
            ?? $contract->Esnad_date?->format('Y-m-d')
            ?? optional($contract->created_at)->format('Y-m-d');

        $details = collect([
            $contract->proje_data ?: null,
        ])->filter();

        return [
            'row_id' => 'contract-' . $contract->id,
            'record_type' => 'contract',
            'display_type' => 'عقد عام',
            'reference' => $contract->contr_number ?: 'بدون رقم',
            'subject' => $contract->proje_name ?: 'مشروع غير محدد',
            'contract_details' => $details->implode(' • '),
            'contractor_id' => $contractorId,
            'contractor_name' => $contractorName,
            'parties' => $contractorName,
            'status' => $status['label'],
            'status_class' => $status['class'],
            'start_date' => optional($contract->Esnad_date)->format('Y-m-d'),
            'end_date' => optional($contract->Contar_date)->format('Y-m-d'),
            'signatures' => $signatureSummary->isNotEmpty()
                ? $signatureSummary->implode(' - ')
                : 'لا توجد توقيعات مسجلة',
            'filter_date' => $filterDate,
            'sort_date' => $filterDate,
            'is_expired' => false,
            'text_report' => collect([
                'عقد رقم ' . ($contract->contr_number ?: 'بدون رقم'),
                $contract->proje_name ? 'خاص بمشروع ' . $contract->proje_name : null,
                'المقاول: ' . $contractorName,
                'الحالة القانونية: ' . $status['label'],
            ])->filter()->implode(' • '),
            'search_index' => collect([
                $contract->contr_number,
                $contract->proje_name,
                $contract->proje_data,
                $contractorId,
                $contractorName,
                $status['label'],
                $filterDate,
                $signatureSummary->implode(' '),
            ])->filter()->implode(' '),
        ];
    }

    protected function mapLeaseRow(Rent $rent, \Carbon\CarbonInterface $today): array
    {
        $contractorName = $rent->renter_name ?: 'غير محدد';
        $tenantName = $rent->tenant_name ?: 'غير محدد';
        $contractorId = $this->resolveContractorIdentifier($contractorName, $rent->id);
        $isExpired = $rent->date_end?->lt($today) ?? false;
        $statusLabel = $isExpired ? 'منتهي' : 'ساري';
        $filterDate = $rent->date_end?->format('Y-m-d')
            ?? $rent->date_sign?->format('Y-m-d')
            ?? optional($rent->created_at)->format('Y-m-d');

        return [
            'row_id' => 'lease-' . $rent->id,
            'record_type' => 'lease',
            'display_type' => $isExpired ? 'إيجار منتهي' : 'عقد إيجار',
            'reference' => 'LEASE-' . str_pad((string) $rent->id, 4, '0', STR_PAD_LEFT),
            'subject' => $rent->home_data ?: 'وحدة غير محددة',
            'contract_details' => collect([
                $rent->date_duration ? 'مدة العقد: ' . $rent->date_duration : null,
                $rent->add_notes ?: null,
            ])->filter()->implode(' • '),
            'contractor_id' => $contractorId,
            'contractor_name' => $contractorName,
            'parties' => collect([$tenantName, $contractorName])->filter()->implode(' • '),
            'status' => $statusLabel,
            'status_class' => $isExpired ? 'danger' : 'success',
            'start_date' => optional($rent->date_sign)->format('Y-m-d'),
            'end_date' => optional($rent->date_end)->format('Y-m-d'),
            'signatures' => '—',
            'filter_date' => $filterDate,
            'sort_date' => $filterDate,
            'is_expired' => $isExpired,
            'text_report' => collect([
                'عقد إيجار للوحدة ' . ($rent->home_data ?: 'غير المحددة'),
                'المؤجر: ' . $contractorName,
                'المستأجر: ' . $tenantName,
                'الحالة القانونية: ' . $statusLabel,
            ])->filter()->implode(' • '),
            'search_index' => collect([
                $tenantName,
                $contractorName,
                $contractorId,
                $rent->home_data,
                $rent->date_duration,
                $statusLabel,
                optional($rent->date_sign)->format('Y-m-d'),
                optional($rent->date_end)->format('Y-m-d'),
                $rent->add_notes,
            ])->filter()->implode(' '),
        ];
    }

    protected function resolveContractStatus(Contract $contract, int $requiredSignatureCount): array
    {
        $completedSignatures = count($contract->sign);

        if ($completedSignatures === 0) {
            return ['label' => 'جديد', 'class' => 'secondary'];
        }

        if ($completedSignatures < $requiredSignatureCount) {
            return ['label' => 'قيد الاستكمال', 'class' => 'warning'];
        }

        return ['label' => 'مكتمل', 'class' => 'success'];
    }

    protected function resolveContractorIdentifier(?string $name, int $fallbackId): string
    {
        $normalized = preg_replace('/\s+/u', ' ', trim((string) $name));

        if ($normalized === '' || $normalized === 'غير محدد') {
            return 'CTR-' . str_pad((string) $fallbackId, 4, '0', STR_PAD_LEFT);
        }

        return 'CTR-' . strtoupper(substr(hash('crc32b', mb_strtolower($normalized, 'UTF-8')), 0, 8));
    }
}
