<?php

declare(strict_types=1);

namespace App\Http\Requests\Image;

use Illuminate\Foundation\Http\FormRequest;

final class UploadImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'image.required' => 'Image file is required',
            'image.image' => 'The file must be an image',
            'image.mimes' => 'The image must be a file of type: jpg, jpeg, png, gif, webp',
            'image.max' => 'The image must not be larger than 5MB',
        ];
    }
}
