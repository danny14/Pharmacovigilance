<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Representa una alerta de farmacovigilancia enviada a un cliente.
 */
class Alert extends Model
{
    use HasFactory;

    protected $fillable = ['customer_id', 'order_id', 'sent_at'];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    /**
     * Obtiene el cliente al que se envió la alerta.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Obtiene la orden por la cual se generó la alerta.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
