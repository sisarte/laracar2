<?php

namespace App\Http\Requests\Vehicle;

use App\Enums\FuelType;
use App\Enums\Transmission;
use App\Enums\VehicleStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $currentYear = (int) date('Y');

        return [
            'model_version_id' => ['required', 'exists:model_versions,id'],
            'year' => ['required', 'integer', 'min:1900', 'max:'.($currentYear + 1)],
            'price' => ['required', 'numeric', 'min:0'],
            'mileage' => ['required', 'integer', 'min:0'],
            'color' => ['required', 'string', 'max:50'],
            'fuel_type' => ['required', Rule::enum(FuelType::class)],
            'transmission' => ['required', Rule::enum(Transmission::class)],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::enum(VehicleStatus::class)],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'max:5120'],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['integer', 'exists:media,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'model_version_id.required' => 'Selecione uma versão do modelo.',
            'model_version_id.exists' => 'A versão selecionada não existe.',
            'year.required' => 'O ano é obrigatório.',
            'year.min' => 'O ano deve ser no mínimo 1900.',
            'year.max' => 'O ano não pode ser maior que o próximo ano.',
            'price.required' => 'O preço é obrigatório.',
            'price.min' => 'O preço deve ser um valor positivo.',
            'mileage.required' => 'A quilometragem é obrigatória.',
            'mileage.min' => 'A quilometragem deve ser um valor positivo.',
            'color.required' => 'A cor é obrigatória.',
            'color.max' => 'A cor deve ter no máximo 50 caracteres.',
            'fuel_type.required' => 'O tipo de combustível é obrigatório.',
            'transmission.required' => 'O tipo de câmbio é obrigatório.',
            'description.max' => 'A descrição deve ter no máximo 5000 caracteres.',
            'status.required' => 'O status é obrigatório.',
            'images.*.image' => 'Cada arquivo deve ser uma imagem válida.',
            'images.*.max' => 'Cada imagem deve ter no máximo 5MB.',
        ];
    }
}
