<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateNotificationSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email_on_comment' => ['sometimes', 'boolean'],
            'email_on_reply' => ['sometimes', 'boolean'],
            'email_on_follow' => ['sometimes', 'boolean'],
            'email_on_like' => ['sometimes', 'boolean'],
            'push_enabled' => ['sometimes', 'boolean'],
        ];
    }
}
