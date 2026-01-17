<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($this->user()->id),
            ],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => '이메일은 필수입니다.',
            'email.email' => '유효한 이메일 주소를 입력해주세요.',
            'email.unique' => '이미 사용중인 이메일입니다.',
            'password.required' => '비밀번호는 필수입니다.',
        ];
    }
}
