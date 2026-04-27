<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ProductionRawMaterials extends Model
{
    protected $table = 'production_raw_materials';
    protected $primaryKey = 'raw_material_id';
    public $timestamps = false;

    protected $fillable = ['production_id', 'product_id', 'quantity_used'];

    public function production()
    {
        return $this->belongsTo(Production::class, 'production_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}