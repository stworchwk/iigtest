<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'username' => 'required|unique:users|min:5|max:12',
            'password' => 'confirmed|min:6',
            'first_name' => 'required|min:5|max:50',
            'last_name' => 'required|min:5|max:50',
            'image' => 'image|mimes:jpeg,png,jpg|max:2048'
        ];
    }
}
