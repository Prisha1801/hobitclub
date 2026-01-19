<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    // public function authorize(): bool
    // {
    //     return false;
    // }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'category_id'      => 'sometimes|exists:service_categories,id',
            'name'             => 'sometimes|string',
            'base_price'       => 'sometimes|numeric|min:0',
            'discount_price'   => 'sometimes|numeric|min:0',
            'duration_minutes' => 'sometimes|integer|min:1',
            'commission_type'  => 'sometimes|in:flat,percentage',
            'commission_value' => 'sometimes|numeric|min:0',
            'status'           => 'sometimes|boolean',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->commission_type) {
            $this->merge([
                'commission_type' => strtolower($this->commission_type),
            ]);
        }
    }
}
