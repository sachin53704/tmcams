<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'department_id' => 'required|exists:departments,id',
            'sub_department_id' => 'nullable',
            'emp_code' => 'nullable|max:15|regex:/^[A-Z0-9]+$/',
            'dob' => 'nullable',
            'gender' => ['required', Rule::in(['m', 'f', 'o'])],
            'role' => 'required',
            'name' => 'required',
            'email' => 'required|email',
            'mobile' => 'nullable|digits:10',
        ];
    }
}
