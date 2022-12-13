<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class AuthRequest extends FormRequest
{

    /**
     * 确定用户是否授权发出此请求
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * 请求验证规则
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'name' => 'required|unique:users|min:6|max:30',
            'email' => 'required|email:rfc|max:255',
            'password' => 'required|min:6|confirmed',
        ];

        return $rules;
    }

//    protected function failedValidation(Validator $validator)
//    {
//        throw new HttpResponseException(response()->json([
//            'error' => (new ValidationException($validator))->errors()
//        ], JsonResponse::HTTP_BAD_REQUEST));
//    }
}
