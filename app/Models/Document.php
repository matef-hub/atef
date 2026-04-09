<?php

namespace App\Models;

use App\Models\Concerns\HasStoredFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;
    use HasStoredFiles;

    protected $fillable = [
        'docu_name',
        'doc_numer',
        'docu_issu_from',
        'docu_iss_date',
        'docu_expiry_date',
        'docu_pdf',
    ];

    protected $casts = [
        'docu_iss_date' => 'date',
        'docu_expiry_date' => 'date',
    ];

    protected $appends = [
        'docu_pdf_url',
    ];

    public function getDocuPdfUrlAttribute(): ?string
    {
        return $this->storedFileUrl('docu_pdf');
    }
}
