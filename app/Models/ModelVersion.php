<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModelVersion extends Model
{
    /** @use HasFactory<\Database\Factories\ModelVersionFactory> */
    use HasFactory;

    protected $fillable = [
        'vehicle_model_id',
        'fipe_code',
        'name',
    ];

    /**
     * @return BelongsTo<VehicleModel, $this>
     */
    public function vehicleModel(): BelongsTo
    {
        return $this->belongsTo(VehicleModel::class);
    }

    /**
     * @return HasMany<Vehicle, $this>
     */
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }
}
