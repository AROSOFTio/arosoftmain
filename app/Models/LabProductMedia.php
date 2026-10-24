<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LabProductMedia extends Model {
 protected $table='lab_product_media';
 protected $fillable=['product_id','type','path_or_url','alt_text','sort_order'];
}
