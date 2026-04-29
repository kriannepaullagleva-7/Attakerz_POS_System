<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $primaryKey = 'id';

    protected $fillable = [
        'supplier_name',
        'contact_number',
        'address',
    ];

    public function stockIns()
    {
        return $this->hasMany(StockIn::class, 'supplier_id');
    }
}