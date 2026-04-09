<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class LegalResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function payload(): array
    {
        return $this->safe()->except($this->fileFields());
    }

    abstract protected function fileFields(): array;
}
