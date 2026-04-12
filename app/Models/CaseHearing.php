<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseHearing extends Model
{
    use HasFactory;

    protected $fillable = [
        'legal_case_id',
        'hearing_date',
        'roll_number',
        'court_decision',
        'next_hearing_at',
        'notes',
    ];

    protected $casts = [
        'hearing_date' => 'date',
        'next_hearing_at' => 'date',
    ];

    public function legalCase(): BelongsTo
    {
        return $this->belongsTo(LegalCase::class);
    }
}
