<?php

namespace App\Http\Requests;

use App\Models\Contract;
use Closure;

class ContractRequest extends LegalResourceRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'suppli_name' => is_string($this->input('suppli_name'))
                ? trim($this->input('suppli_name'))
                : $this->input('suppli_name'),
            'sign' => $this->input('sign', []),
        ]);
    }

    public function rules(): array
    {
        return [
            'proje_name' => ['required', 'string', 'max:250'],
            'suppli_name' => ['required', 'string', 'max:250'],
            'proje_data' => ['nullable', 'string', 'max:250'],
            'Esnad_date' => ['required', 'date'],
            'Contar_date' => ['required', 'date'],
            'sign' => [
                'nullable',
                'array',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (!is_array($value)) {
                        return;
                    }

                    $allowedValues = array_unique([
                        ...Contract::signOptions(),
                        ...($this->route('contract')?->sign ?? []),
                    ]);
                    $invalidValues = array_diff($value, $allowedValues);

                    if ($invalidValues !== []) {
                        $fail('جهات التوقيع تحتوي على قيمة غير صالحة.');
                    }
                },
            ],
            'Pdf_image' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ];
    }

    public function attributes(): array
    {
        return [
            'proje_name' => 'اسم المشروع',
            'suppli_name' => 'اسم المورد',
            'proje_data' => 'بيانات المشروع',
            'Esnad_date' => 'تاريخ الإسناد',
            'Contar_date' => 'تاريخ العقد',
            'sign' => 'جهات التوقيع',
            'Pdf_image' => 'ملف العقد',
        ];
    }

    protected function fileFields(): array
    {
        return ['Pdf_image'];
    }
}
