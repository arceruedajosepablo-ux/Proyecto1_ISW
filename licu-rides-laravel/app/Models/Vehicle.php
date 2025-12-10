<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    protected $fillable = [
        'user_id',
        'placa',
        'color',
        'marca',
        'modelo',
        'anio',
        'capacidad',
        'foto',
    ];

    protected $casts = [
        'anio' => 'integer',
        'capacidad' => 'integer',
    ];

    /**
     * User (driver) that owns the vehicle
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Rides using this vehicle
     */
    public function rides(): HasMany
    {
        return $this->hasMany(Ride::class);
    }
}
