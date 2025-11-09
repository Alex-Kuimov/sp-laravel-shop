<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'status',
        'total',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total' => 'decimal:2',
    ];
    
    /**
     * Get the user that owns the order.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the products for the order.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_products')
            ->withPivot('quantity', 'price');
    }
    
    /**
     * Get the order products for the order.
     */
    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class);
    }
    
    /**
     * Get the payment for the order.
     */
    public function payment()
    {
        return $this->hasOne(OrderPayment::class);
    }
    
    /**
     * Get the history for the order.
     */
    public function history()
    {
        return $this->hasMany(OrderHistory::class);
    }
}
