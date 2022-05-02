<?php

namespace App\Http\Requests;

class ApplicationsRequest extends BaseFormRequest
{

    public function storeRules()
    {
        return [
            'student_id' => ['required' , 'integer' , 'exists:students,id'],
            'company_id' => ['required','integer','exists:companies,id'],
            'internship_post_id' => ['required', 'integer', 'exists:internship_posts,id'],
            'status'           =>['integer','digits_between:1,5'] ,
            'cancel_status' => ['nullable','boolean'],
            'status' => ['nullable','integer'],
            'is_admin_read' => ['nullable','boolean'],
        ];
    }

    public function updateRules()
    {
        return [
            'student_id' => ['required' , 'integer' , 'exists:students,id'],
            'company_id' => ['required','integer','exists:companies,id'],
            'internship_post_id' => ['required', 'integer', 'exists:internship_posts,id'],
            'cancel_status' => ['nullable','boolean'],
            'status' => ['nullable','integer'],
            'is_admin_read' => ['nullable','boolean'],
        ];
    }
}
