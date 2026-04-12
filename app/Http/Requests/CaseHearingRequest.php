<?php

namespace App\Http\Requests;

class CaseHearingRequest extends LegalResourceRequest
{
    protected function prepareForValidation(): void
    {
        $fields = ['roll_number', 'court_decision', 'notes'];
        $payload = [];

        foreach ($fields as $field) {
            $payload[$field] = is_string($this->input($field))
                ? trim($this->input($field))
                : $this->input($field);
        }

        $this->merge($payload);
    }

    public function rules(): array
    {
        return [
            'legal_case_id' => ['required', 'exists:legal_cases,id'],
            'hearing_date' => ['required', 'date'],
            'roll_number' => ['nullable', 'string', 'max:100'],
            'court_decision' => ['required', 'string', 'max:3000'],
            'next_hearing_at' => ['nullable', 'date', 'after_or_equal:hearing_date'],
            'notes' => ['nullable', 'string', 'max:3000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'legal_case_id' => 'القضية',
            'hearing_date' => 'تاريخ الجلسة',
            'roll_number' => 'رقم الرول',
            'court_decision' => 'قرار المحكمة',
            'next_hearing_at' => 'الجلسة القادمة',
            'notes' => 'ملاحظات',
        ];
    }

    protected function fileFields(): array
    {
        return [];
    }
}
