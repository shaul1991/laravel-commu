<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'bio' => ['nullable', 'string', 'max:200'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => '이름은 필수입니다.',
            'name.max' => '이름은 최대 50자까지 입력할 수 있습니다.',
            'bio.max' => '소개는 최대 200자까지 입력할 수 있습니다.',
        ];
    }
}
