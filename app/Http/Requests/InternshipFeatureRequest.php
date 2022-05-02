<?php

namespace App\Http\Requests;

class InternshipFeatureRequest extends BaseFormRequest
{
  
    public function storeRules()
    {
        return [

            // name and display order should be unique
            'name' => 'required|string|unique:internship_features,name',
            'display_order' => 'required|integer|unique:internship_features,display_order',
        ];
    }

    public function updateRules()
    {
        return [

            'name' => 'sometimes|string|',
            'display_order' => 'sometimes|integer',
         
        ];
    }

    public function indexRules()
    {
        return [
            'paginate' => ['required', 'integer', 'gte:0'],
            'page' => ['required', 'integer', 'gt:0'],
            'sort_by' => ['sometimes', 'string', 'in:id,created_at,display_order'],
            'sort_by_order' => ['required_with:sort_by', 'string', 'in:asc,desc'],
            'search' => ['sometimes', 'string'],
        ];
    }
}
