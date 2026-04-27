<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = ['employee_id', 'date', 'total_amount', 'cash_paid'];

    public function details()
    {
        return $this->hasMany(SaleDetail::class, 'sale_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}