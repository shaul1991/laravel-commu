<?php

declare(strict_types=1);

namespace App\Http\Requests\Article;

use App\Domain\Core\Article\ValueObjects\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categories = array_column(Category::cases(), 'value');

        return [
            'title' => ['required', 'string', 'max:200'],
            'content' => ['required', 'string', 'max:100000'],
            'category' => ['required', 'string', Rule::in($categories)],
            'is_draft' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => '제목은 필수입니다.',
            'title.max' => '제목은 최대 200자까지 입력할 수 있습니다.',
            'content.required' => '본문은 필수입니다.',
            'content.max' => '본문은 최대 100,000자까지 입력할 수 있습니다.',
            'category.required' => '카테고리는 필수입니다.',
            'category.in' => '유효하지 않은 카테고리입니다.',
        ];
    }
}
