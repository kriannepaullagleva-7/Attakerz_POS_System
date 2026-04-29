<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Production extends Model
{
    protected $table = 'productions';

    protected $primaryKey = 'production_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = ['employee_id', 'date'];
    
    public $timestamps = true;
    
    protected $casts = [
        'date' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function rawMaterials()
    {
        return $this->hasMany(ProductionRawMaterials::class, 'production_id');
    }

    public function outputs()
    {
        return $this->hasMany(ProductionOutput::class, 'production_id');
    }
}   