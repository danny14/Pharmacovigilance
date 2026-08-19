<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Representa un medicamento en el catálogo.
 */
class Medication extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'lot_number'];

    /**
     * Obtiene los elementos de orden que incluyen este medicamento.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
