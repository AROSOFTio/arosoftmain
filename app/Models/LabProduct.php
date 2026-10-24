<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LabProduct extends Model {
 protected $table='lab_products';
 protected $fillable=['name','slug','kind','summary','description','icon_path','price','currency','billing_type','status','featured','published_at'];
 protected $casts=['featured'=>'boolean','published_at'=>'datetime','price'=>'decimal:2'];
 public function links(){return $this->hasMany(LabProductLink::class,'product_id');}
 public function media(){return $this->hasMany(LabProductMedia::class,'product_id')->orderBy('sort_order');}
}
