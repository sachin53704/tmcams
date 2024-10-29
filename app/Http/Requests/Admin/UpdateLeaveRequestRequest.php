<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeaveRequestRequest extends FormRequest
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
        $rules =  [
            'page_type' => 'required',
            'emp_code' => 'required',
            'name' => 'required',
            'ward' => 'required',
            'department' => 'required',
            'date' => 'required_if:page_type,half_day|date',

            'leave_type_id' => 'required_unless:page_type,half_day',
            'from_date' => 'required_unless:page_type,half_day|date',
            'file' => 'nullable|mimes:png,jpg,jpeg,pdf',
            'remark' => 'required',
        ];

        if( request()->page_type != 'half_day' && request()->leave_type_id != '7' )
        {
            $rules['to_date'] = 'required|date|after_or_equal:from_date';
            $rules['no_of_days'] = 'required|numeric|max:30';
        }

        return $rules;
    }


    public function messages(): array
    {
        return [
            'date.required_if' => 'Date field is required',
            'leave_type_id.required_unless' => 'Leave Type field is required',
            'from_date.required_unless' => 'From date field is required',
            'file.required_unless' => 'The file field is required',
        ];
    }
}