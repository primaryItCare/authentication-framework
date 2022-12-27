<?php

namespace AUTHWRAP\Userform\Requests;

use Illuminate\Foundation\Http\FormRequest; 

class FilterList extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'draw' => 'required|integer',
            'start' => 'required|integer',
            'length' => 'required|integer',
            'order' => 'nullable',
        ];
    }
}
