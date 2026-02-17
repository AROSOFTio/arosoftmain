<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('manage-blog');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'admin_emails' => ['nullable', 'string', 'max:1000'],
            'sidebar_cache_ttl_minutes' => ['required', 'integer', 'min:5', 'max:180'],
            'feed_cache_ttl_minutes' => ['required', 'integer', 'min:5', 'max:180'],
            'view_count_window_hours' => ['required', 'integer', 'min:1', 'max:48'],
            'dashboard_theme_default' => ['required', Rule::in(['light', 'dark'])],
        ];
    }
}

