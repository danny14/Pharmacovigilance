<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Representa un artículo dentro de una orden de compra.
 */
class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'medication_id'];

    /**
     * Obtiene la orden a la que pertenece este artículo.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Obtiene el medicamento correspondiente a este artículo.
     */
    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }
}
