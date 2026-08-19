<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Representa a un cliente en el sistema.
 */
class Customer extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'phone'];

    /**
     * Obtiene las órdenes asociadas a este cliente.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Obtiene las alertas de farmacovigilancia enviadas a este cliente.
     */
    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }
}
