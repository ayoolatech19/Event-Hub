<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
           'category_id'  => 'required|exists:categories,id',
        'title'        => 'required|string|max:255',
        'description'  => 'required|string',
        'venue'        => 'required|string|max:255',
        'date'         => 'required|date|after:now',
        'capacity'     => 'required|integer|min:1',
        'price'        => 'required|numeric|min:0',
        'banner_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        'status'       => 'nullable|in:draft,published',
        ];
    }
}
