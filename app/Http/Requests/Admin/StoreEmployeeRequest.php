<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
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
            'emp_code'=> 'required|unique:app_users,emp_code|regex:/^[A-Z0-9]+$/',
            'name'=> 'required',
            'email'=> 'nullable|sometimes|email',
            'mobile'=> 'nullable|sometimes|digits:10',
            'dob'=> 'nullable|sometimes|date',
            'doj'=> 'nullable|sometimes|date',
            'gender'=> ['required', Rule::in(['m', 'f', 'o'])],
            'permanent_address'=> 'nullable',
            'present_address'=> 'nullable',
            'is_rotational'=> 'required',
            'employee_type'=> 'required',
            'device_id'=> 'required',
            'department_id'=> 'required',
            'sub_department_id'=> 'nullable',
            'shift_id'=> 'required_if:is_rotational,0',
            'in_time'=> 'nullable',
            'ward_id'=> 'required',
            'clas_id'=> 'required',
            'designation_id'=> 'nullable',
            'is_ot'=> ['required', Rule::in(['y', 'n'])],
            'is_divyang'=> ['required', Rule::in(['y', 'n'])],
        ];
    }
}
