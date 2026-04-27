<?php
// ─── Production.php ───────────────────────────────────────────────────────────
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Production extends Model
{
    protected $fillable = ['employee_id', 'date'];

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