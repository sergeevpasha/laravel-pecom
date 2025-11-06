<?php
declare(strict_types=1);

namespace SergeevPasha\Pecom\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PecomDeliveryStatusRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<mixed>
     */
    public function rules(): array
    {
        return [
            'cargo_code' => ['required', 'string'],
        ];
    }
}