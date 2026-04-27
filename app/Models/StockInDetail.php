<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockInDetail extends Model
{
    public $timestamps = false;

    protected $fillable = ['stock_in_id', 'product_id', 'quantity', 'cost_per_unit'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function stockIn()
    {
        return $this->belongsTo(StockIn::class, 'stock_in_id');
    }
}
