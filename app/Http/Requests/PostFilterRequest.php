<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostFilterRequest extends FormRequest
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
            'q' => ['nullable', 'string', 'max:120'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function searchQuery(): string
    {
        return trim((string) $this->validated('q', ''));
    }

    public function canonicalUrl(): string
    {
        $page = (int) $this->validated('page', 1);

        return url()->query($this->url(), $page > 1 ? ['page' => $page] : []);
    }
}
