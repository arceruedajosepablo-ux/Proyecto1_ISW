<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ride extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'vehicle_id',
        'nombre',
        'origen',
        'destino',
        'fecha',
        'hora',
        'costo',
        'espacios',
    ];

    protected $casts = [
        'fecha' => 'date',
        'costo' => 'decimal:2',
        'espacios' => 'integer',
    ];

    /**
     * User (driver) that created the ride
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Vehicle used for the ride
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Reservations for this ride
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Get available spaces (only count accepted reservations)
     */
    public function getEspaciosDisponiblesAttribute(): int
    {
        $reservados = $this->reservations()
            ->where('status', 'accepted')
            ->sum('seats');
        
        return $this->espacios - $reservados;
    }

    /**
     * Check if ride has available spaces
     */
    public function hasAvailableSpaces(int $seatsNeeded = 1): bool
    {
        return $this->espacios_disponibles >= $seatsNeeded;
    }
}
