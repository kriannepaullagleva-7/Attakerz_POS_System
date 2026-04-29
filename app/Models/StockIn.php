<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockIn extends Model
{
    protected $table = 'stock_ins';

    protected $fillable = [
        'supplier_id',
        'employee_id',
        'date',
        'total_cost',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function details()
    {
        return $this->hasMany(StockInDetail::class, 'stock_in_id');
    }
}