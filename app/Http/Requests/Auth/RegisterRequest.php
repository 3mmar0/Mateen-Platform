<?php

namespace App\Http\Requests\Auth;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:40'],
            'role' => ['required', Rule::enum(Role::class)],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'subject' => ['nullable', 'string', 'max:80'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            if ($this->input('role') !== Role::Teacher->value) {
                return;
            }
            if (! $this->filled('subject_id') && ! $this->filled('subject')) {
                $v->errors()->add('subject_id', 'يجب اختيار المادة للمعلمة.');
            }
        });
    }
}
