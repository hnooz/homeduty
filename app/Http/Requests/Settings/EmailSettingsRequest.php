<?php

namespace App\Http\Requests\Settings;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmailSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'mail_mailer' => ['required', 'string', Rule::in(['smtp', 'sendmail', 'resend', 'log', 'array'])],
            'resend_api_key' => ['nullable', 'string', 'max:255', 'required_if:mail_mailer,resend'],
            'mail_host' => ['nullable', 'string', 'max:255'],
            'mail_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_encryption' => ['nullable', 'string', Rule::in(['tls', 'ssl', ''])],
            'mail_from_address' => ['required', 'string', 'email', 'max:255'],
            'mail_from_name' => ['required', 'string', 'max:255'],
        ];
    }
}
