<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'inventories';

    protected $fillable = [
        'product_id',
        'quantity_on_hand',
        'border_point',
        'last_updated',
    ];

    protected $casts = [
        'last_updated' => 'datetime',
    ];

    // Automatically update last_updated timestamp on save
    protected static function booted()
    {
        static::saving(function ($inv) {
            $inv->last_updated = now();
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function isLow(): bool
    {
        return $this->quantity_on_hand <= ($this->border_point ?? 10);
    }

    public function statusLabel(): string
    {
        if ($this->quantity_on_hand <= 0) return 'Out of Stock';
        if ($this->isLow()) return 'Low';
        return 'Medium';
    }
}