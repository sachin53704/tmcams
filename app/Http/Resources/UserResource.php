<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    use CommonResponseFormat;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $userArray = [];
        $userArray = [
            'id' => $this->id,
            'emp_code' => $this->emp_code,
            'name' => $this->name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'dob' => $this->dob,
            'gender' => $this->gender,
            'gender_text' => $this->gender_text,
            'in_time' => $this->in_time,
            'ward_id' => $this->ward_id,
            'ward' => $this->whenLoaded('ward') ? $this->ward->name : '',
            'department_id' => $this->department_id,
            'department' => $this->whenLoaded('department')? $this->department->name : '',
            'token' => $this->token,
        ];


        $userArray['shift_id'] = $this->employee->shift_id;
        $userArray['shift'] = $this->employee->shift->name;
        $userArray['class_id'] = $this->employee->clas_id;
        $userArray['class'] = $this->employee->clas->name;
        $userArray['designation_id'] = $this->employee->designation_id;
        $userArray['designation'] = $this->employee->designation->name;
        $userArray['doj'] = $this->employee->doj;
        $userArray['is_ot'] = $this->employee->is_ot;
        $userArray['is_divyang'] = $this->employee->is_divyang;
        $userArray['permanent_address'] = $this->employee->permanent_address;
        $userArray['present_address'] = $this->employee->present_address;


        return $userArray;
    }
}
