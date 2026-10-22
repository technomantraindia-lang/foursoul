<?php

declare(strict_types=1);

namespace App\Http\Requests\Learn;

use Illuminate\Foundation\Http\FormRequest;

class SubmitAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'content' => ['nullable', 'string', 'max:20000'],
            'file' => ['nullable', 'file', 'max:10240'],
        ];
    }
}
