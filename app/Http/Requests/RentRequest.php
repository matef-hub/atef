<?php

namespace App\Http\Requests;

class RentRequest extends LegalResourceRequest
{
    public function rules(): array
    {
        return [
            'tenant_name' => ['required', 'string', 'max:250'],
            'renter_name' => ['required', 'string', 'max:250'],
            'home_data' => ['required', 'string', 'max:250'],
            'date_sign' => ['required', 'date'],
            'date_duration' => ['required', 'string', 'max:250'],
            'date_end' => ['required', 'date', 'after_or_equal:date_sign'],
            'insurance_mon' => ['nullable', 'numeric', 'min:0'],
            'Monthly_rent' => ['required', 'numeric', 'min:0'],
            'add_notes' => ['nullable', 'string', 'max:250'],
            'con_pdf' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'con_word' => ['nullable', 'file', 'mimes:doc,docx', 'max:10240'],
        ];
    }

    public function attributes(): array
    {
        return [
            'tenant_name' => 'اسم المستأجر',
            'renter_name' => 'اسم المؤجر',
            'home_data' => 'بيانات الوحدة',
            'date_sign' => 'تاريخ التوقيع',
            'date_duration' => 'مدة العقد',
            'date_end' => 'تاريخ النهاية',
            'insurance_mon' => 'التأمين',
            'Monthly_rent' => 'الإيجار الشهري',
            'add_notes' => 'ملاحظات إضافية',
            'con_pdf' => 'ملف العقد PDF',
            'con_word' => 'ملف العقد Word',
        ];
    }

    protected function fileFields(): array
    {
        return ['con_pdf', 'con_word'];
    }
}
