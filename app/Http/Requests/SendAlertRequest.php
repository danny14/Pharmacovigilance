<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendAlertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Ya protegido por middleware auth:sanctum
    }

    public function rules(): array
    {
        return [
            'order_ids' => ['required', 'array', 'min:1'],
            'order_ids.*' => ['integer', 'exists:orders,id'],
            'lot' => ['required', 'string', 'max:255'],
        ];
    }
}
