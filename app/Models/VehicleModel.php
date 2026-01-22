<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleModel extends Model
{
    /** @use HasFactory<\Database\Factories\VehicleModelFactory> */
    use HasFactory;

    protected $fillable = [
        'make_id',
        'name',
    ];

    /**
     * @return BelongsTo<Make, $this>
     */
    public function make(): BelongsTo
    {
        return $this->belongsTo(Make::class);
    }

    /**
     * @return HasMany<ModelVersion, $this>
     */
    public function modelVersions(): HasMany
    {
        return $this->hasMany(ModelVersion::class);
    }
}
