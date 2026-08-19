<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Representa una orden de compra de un cliente.
 */
class Order extends Model
{
    use HasFactory;

    protected $fillable = ['customer_id', 'purchase_date'];

    protected $casts = [
        'purchase_date' => 'datetime',
    ];

    /**
     * Obtiene el cliente que realizó la orden.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Obtiene los elementos individuales de esta orden.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Obtiene las alertas enviadas por esta orden.
     */
    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }
}
