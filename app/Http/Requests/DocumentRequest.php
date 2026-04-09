<?php

namespace App\Http\Requests;

class DocumentRequest extends LegalResourceRequest
{
    public function rules(): array
    {
        return [
            'docu_name' => ['required', 'string', 'max:250'],
            'doc_numer' => ['required', 'string', 'max:250'],
            'docu_issu_from' => ['required', 'string', 'max:250'],
            'docu_iss_date' => ['required', 'date'],
            'docu_expiry_date' => ['required', 'date', 'after_or_equal:docu_iss_date'],
            'docu_pdf' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ];
    }

    public function attributes(): array
    {
        return [
            'docu_name' => 'اسم المستند',
            'doc_numer' => 'رقم المستند',
            'docu_issu_from' => 'جهة الإصدار',
            'docu_iss_date' => 'تاريخ الإصدار',
            'docu_expiry_date' => 'تاريخ الانتهاء',
            'docu_pdf' => 'ملف المستند',
        ];
    }

    protected function fileFields(): array
    {
        return ['docu_pdf'];
    }
}
