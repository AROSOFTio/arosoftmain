<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LabProductLink extends Model {
 protected $table='lab_product_links';
 protected $fillable=['product_id','platform','purpose','url','label'];
}
