<?php

namespace App\Models;

use App\Models\Concerns\HasStoredFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rent extends Model
{
    use HasFactory;
    use HasStoredFiles;

    protected $fillable = [
        'tenant_name',
        'renter_name',
        'home_data',
        'date_sign',
        'date_duration',
        'date_end',
        'insurance_mon',
        'Monthly_rent',
        'add_notes',
        'con_pdf',
        'con_word',
    ];

    protected $casts = [
        'date_sign' => 'date',
        'date_end' => 'date',
    ];

    protected $appends = [
        'con_pdf_url',
        'con_word_url',
    ];

    public function getConPdfUrlAttribute(): ?string
    {
        return $this->storedFileUrl('con_pdf');
    }

    public function getConWordUrlAttribute(): ?string
    {
        return $this->storedFileUrl('con_word');
    }
}
