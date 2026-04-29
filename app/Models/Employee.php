<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'contact_number',
        'address',
        'role',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class, 'employee_id');
    }

    public function stockIns()
    {
        return $this->hasMany(StockIn::class, 'employee_id');
    }

    public function productions()
    {
        return $this->hasMany(Production::class, 'employee_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}