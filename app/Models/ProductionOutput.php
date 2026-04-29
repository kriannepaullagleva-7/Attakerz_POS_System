<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionOutput extends Model
{
    protected $table = 'production_output';

    protected $primaryKey = 'output_id';

    public $timestamps = false;

    protected $fillable = [
        'production_id',
        'product_id',
        'quantity_produced'
    ];

    public function production()
    {
        return $this->belongsTo(
            Production::class,
            'production_id',      // FK in this table
            'production_id'       // PK in productions table
        );
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}