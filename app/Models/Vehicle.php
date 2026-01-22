<?php

namespace App\Models;

use App\Enums\FuelType;
use App\Enums\Transmission;
use App\Enums\VehicleStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Vehicle extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\VehicleFactory> */
    use HasFactory;

    use InteractsWithMedia;

    protected $fillable = [
        'model_version_id',
        'year',
        'price',
        'mileage',
        'color',
        'fuel_type',
        'transmission',
        'description',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'float',
            'fuel_type' => FuelType::class,
            'transmission' => Transmission::class,
            'status' => VehicleStatus::class,
        ];
    }

    /**
     * @return BelongsTo<ModelVersion, $this>
     */
    public function modelVersion(): BelongsTo
    {
        return $this->belongsTo(ModelVersion::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images');
    }
}
