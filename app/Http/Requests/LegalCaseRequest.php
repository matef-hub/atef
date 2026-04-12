<?php

namespace App\Http\Requests;

use App\Models\LegalCase;
use Illuminate\Validation\Rule;

class LegalCaseRequest extends LegalResourceRequest
{
    protected function prepareForValidation(): void
    {
        $fields = [
            'primary_party_name',
            'opponent_party_name',
            'case_number',
            'subject',
            'primary_court_name',
            'circuit_number',
            'detention_order_number',
            'appeal_appellant_name',
            'appeal_respondent_name',
            'appeal_number',
            'appeal_court_name',
            'appeal_circuit_number',
        ];

        $payload = [
            'has_appeal' => $this->boolean('has_appeal'),
        ];

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
            'case_type' => ['required', Rule::in(array_keys(LegalCase::typeOptions()))],
            'primary_party_name' => ['required', 'string', 'max:250'],
            'opponent_party_name' => ['required', 'string', 'max:250'],
            'case_number' => ['required', 'string', 'max:250'],
            'case_filed_at' => [
                Rule::requiredIf(fn (): bool => $this->input('case_type') === LegalCase::TYPE_CIVIL),
                'nullable',
                'date',
            ],
            'first_session_at' => ['required', 'date'],
            'subject' => [
                Rule::requiredIf(fn (): bool => $this->input('case_type') === LegalCase::TYPE_CIVIL),
                'nullable',
                'string',
                'max:2000',
            ],
            'primary_court_name' => [
                Rule::requiredIf(fn (): bool => $this->input('case_type') === LegalCase::TYPE_CIVIL),
                'nullable',
                'string',
                'max:250',
            ],
            'circuit_number' => [
                Rule::requiredIf(fn (): bool => $this->input('case_type') === LegalCase::TYPE_CIVIL),
                'nullable',
                'string',
                'max:100',
            ],
            'report_date' => [
                Rule::requiredIf(fn (): bool => $this->input('case_type') === LegalCase::TYPE_CRIMINAL),
                'nullable',
                'date',
            ],
            'detention_order_number' => ['nullable', 'string', 'max:150'],
            'judgment_issued_at' => [
                Rule::requiredIf(fn (): bool => $this->input('case_type') === LegalCase::TYPE_CIVIL && $this->boolean('has_appeal')),
                'nullable',
                'date',
            ],
            'has_appeal' => [
                Rule::requiredIf(fn (): bool => $this->input('case_type') === LegalCase::TYPE_CIVIL),
                'boolean',
            ],
            'appeal_appellant_name' => [
                Rule::requiredIf(fn (): bool => $this->input('case_type') === LegalCase::TYPE_CIVIL && $this->boolean('has_appeal')),
                'nullable',
                'string',
                'max:250',
            ],
            'appeal_respondent_name' => [
                Rule::requiredIf(fn (): bool => $this->input('case_type') === LegalCase::TYPE_CIVIL && $this->boolean('has_appeal')),
                'nullable',
                'string',
                'max:250',
            ],
            'appeal_number' => [
                Rule::requiredIf(fn (): bool => $this->input('case_type') === LegalCase::TYPE_CIVIL && $this->boolean('has_appeal')),
                'nullable',
                'string',
                'max:250',
            ],
            'appeal_court_name' => [
                Rule::requiredIf(fn (): bool => $this->input('case_type') === LegalCase::TYPE_CIVIL && $this->boolean('has_appeal')),
                'nullable',
                'string',
                'max:250',
            ],
            'appeal_circuit_number' => [
                Rule::requiredIf(fn (): bool => $this->input('case_type') === LegalCase::TYPE_CIVIL && $this->boolean('has_appeal')),
                'nullable',
                'string',
                'max:100',
            ],
            'appeal_session_period' => [
                Rule::requiredIf(fn (): bool => $this->input('case_type') === LegalCase::TYPE_CIVIL && $this->boolean('has_appeal')),
                'nullable',
                Rule::in(array_keys(LegalCase::appealSessionPeriodOptions())),
            ],
            'appeal_first_session_at' => [
                Rule::requiredIf(fn (): bool => $this->input('case_type') === LegalCase::TYPE_CIVIL && $this->boolean('has_appeal')),
                'nullable',
                'date',
            ],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'mimes:pdf,doc,docx,png,jpg,jpeg', 'max:10240'],
            'remove_attachment_ids' => ['nullable', 'array'],
            'remove_attachment_ids.*' => [
                Rule::exists('case_attachments', 'id')
                    ->where(fn ($query) => $query->where('legal_case_id', $this->route('case')?->id)),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'case_type' => 'نوع القضية',
            'primary_party_name' => 'الطرف الأول',
            'opponent_party_name' => 'الطرف الثاني',
            'case_number' => 'رقم الدعوى',
            'case_filed_at' => 'تاريخ رفع الدعوى',
            'first_session_at' => 'تاريخ أول جلسة',
            'subject' => 'موضوع الدعوى',
            'primary_court_name' => 'المحكمة',
            'circuit_number' => 'رقم الدائرة',
            'report_date' => 'تاريخ تحرير المحضر',
            'detention_order_number' => 'رقم حصر الحبس',
            'judgment_issued_at' => 'تاريخ الحكم',
            'has_appeal' => 'بيانات الاستئناف',
            'appeal_appellant_name' => 'اسم المستأنف',
            'appeal_respondent_name' => 'اسم المستأنف ضده',
            'appeal_number' => 'رقم الاستئناف',
            'appeal_court_name' => 'محكمة الاستئناف',
            'appeal_circuit_number' => 'رقم دائرة الاستئناف',
            'appeal_session_period' => 'الفترة',
            'appeal_first_session_at' => 'أول جلسة لنظر الاستئناف',
            'attachments' => 'المستندات',
            'attachments.*' => 'مرفق القضية',
        ];
    }

    public function payload(): array
    {
        $data = $this->safe()->except(['attachments', 'remove_attachment_ids']);

        if (($data['case_type'] ?? null) === LegalCase::TYPE_CIVIL) {
            $data['report_date'] = null;
            $data['detention_order_number'] = null;
        }

        if (($data['case_type'] ?? null) === LegalCase::TYPE_CRIMINAL) {
            $data['case_filed_at'] = null;
            $data['subject'] = null;
            $data['primary_court_name'] = null;
            $data['circuit_number'] = null;
            $data['judgment_issued_at'] = null;
            $data['has_appeal'] = false;
            $data['appeal_appellant_name'] = null;
            $data['appeal_respondent_name'] = null;
            $data['appeal_number'] = null;
            $data['appeal_court_name'] = null;
            $data['appeal_circuit_number'] = null;
            $data['appeal_session_period'] = null;
            $data['appeal_first_session_at'] = null;
        }

        if (!($data['has_appeal'] ?? false)) {
            $data['appeal_appellant_name'] = null;
            $data['appeal_respondent_name'] = null;
            $data['appeal_number'] = null;
            $data['appeal_court_name'] = null;
            $data['appeal_circuit_number'] = null;
            $data['appeal_session_period'] = null;
            $data['appeal_first_session_at'] = null;
        }

        return $data;
    }

    protected function fileFields(): array
    {
        return ['attachments'];
    }
}
