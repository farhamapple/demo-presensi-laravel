<?php

namespace App\Models;

use Database\Factories\OfficeLocationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'address', 'polygon_coordinates', 'is_active'])]
class OfficeLocation extends Model
{
    /** @use HasFactory<OfficeLocationFactory> */
    use HasFactory;

    /**
     * Scope a query to only include active office locations.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Relationship to attendances.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'polygon_coordinates' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
