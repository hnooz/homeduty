<?php

namespace App\Http\Requests\Api\V1;

use App\Concerns\ProfileValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
{
    use ProfileValidationRules;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return $this->profileRules($this->user()?->id);
    }
}
