<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
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
            'emp_code'=> 'required|regex:/^[A-Z0-9]+$/',
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
            'contractor'=> 'nullable',
            'device_id'=> 'nullable',
            'department_id'=> 'required',
            'sub_department_id'=> 'nullable',
            'is_ot'=> ['required', Rule::in(['y', 'n'])],
            'is_divyang'=> ['required', Rule::in(['y', 'n'])],
            'is_half_day_on_saturday'=> ['required', Rule::in(['y', 'n'])],
            'shift_id'=> 'required_if:is_rotational,0',
            'in_time'=> 'nullable',
            'ward_id'=> 'required',
            'clas_id'=> 'nullable',
            'designation_id'=> 'nullable',
        ];
    }
}
