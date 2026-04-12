<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class LegalCase extends Model
{
    use HasFactory;

    public const TYPE_CIVIL = 'civil';
    public const TYPE_CRIMINAL = 'criminal';

    public const APPEAL_SESSION_PERIOD_MORNING = 'morning';
    public const APPEAL_SESSION_PERIOD_EVENING = 'evening';

    protected $fillable = [
        'case_type',
        'primary_party_name',
        'opponent_party_name',
        'case_number',
        'case_filed_at',
        'first_session_at',
        'subject',
        'primary_court_name',
        'circuit_number',
        'report_date',
        'detention_order_number',
        'judgment_issued_at',
        'has_appeal',
        'appeal_appellant_name',
        'appeal_respondent_name',
        'appeal_number',
        'appeal_court_name',
        'appeal_circuit_number',
        'appeal_session_period',
        'appeal_first_session_at',
    ];

    protected $casts = [
        'case_filed_at' => 'date',
        'first_session_at' => 'date',
        'report_date' => 'date',
        'judgment_issued_at' => 'date',
        'appeal_first_session_at' => 'date',
        'has_appeal' => 'boolean',
    ];

    protected $appends = [
        'case_type_label',
        'appeal_deadline_at',
        'appeal_session_period_label',
        'primary_party_label',
        'opponent_party_label',
        'parties_summary',
    ];

    public function attachments(): HasMany
    {
        return $this->hasMany(CaseAttachment::class);
    }

    public function hearings(): HasMany
    {
        return $this->hasMany(CaseHearing::class);
    }

    public function getCaseTypeLabelAttribute(): string
    {
        return static::typeOptions()[$this->case_type] ?? $this->case_type;
    }

    public function getAppealDeadlineAtAttribute(): ?Carbon
    {
        if (!$this->judgment_issued_at) {
            return null;
        }

        return $this->judgment_issued_at->copy()->addDays(40);
    }

    public function getAppealSessionPeriodLabelAttribute(): ?string
    {
        if (!$this->appeal_session_period) {
            return null;
        }

        return static::appealSessionPeriodOptions()[$this->appeal_session_period] ?? $this->appeal_session_period;
    }

    public function getPrimaryPartyLabelAttribute(): string
    {
        return $this->isCivil() ? 'المدعي' : 'الشاكي';
    }

    public function getOpponentPartyLabelAttribute(): string
    {
        return $this->isCivil() ? 'المدعى عليه' : 'المتهم';
    }

    public function getPartiesSummaryAttribute(): string
    {
        return collect([$this->primary_party_name, $this->opponent_party_name])
            ->filter()
            ->implode(' / ');
    }

    public function isCivil(): bool
    {
        return $this->case_type === self::TYPE_CIVIL;
    }

    public function isCriminal(): bool
    {
        return $this->case_type === self::TYPE_CRIMINAL;
    }

    public static function typeOptions(): array
    {
        return [
            self::TYPE_CIVIL => 'مدنية',
            self::TYPE_CRIMINAL => 'جنائية',
        ];
    }

    public static function appealSessionPeriodOptions(): array
    {
        return [
            self::APPEAL_SESSION_PERIOD_MORNING => 'صباحي',
            self::APPEAL_SESSION_PERIOD_EVENING => 'مسائي',
        ];
    }

    public static function availablePrimaryCourts(): Collection
    {
        return static::query()
            ->whereNotNull('primary_court_name')
            ->where('primary_court_name', '!=', '')
            ->orderBy('primary_court_name')
            ->distinct()
            ->pluck('primary_court_name');
    }

    public static function availableAppealCourts(): Collection
    {
        return static::query()
            ->whereNotNull('appeal_court_name')
            ->where('appeal_court_name', '!=', '')
            ->orderBy('appeal_court_name')
            ->distinct()
            ->pluck('appeal_court_name');
    }
}
