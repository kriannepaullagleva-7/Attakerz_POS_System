<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['product_name','category','unit','price'];

    public function inventory()
    {
        return $this->hasOne(Inventory::class, 'product_id');
    }
}
