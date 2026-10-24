<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LabSoftwareRequest extends Model {
 protected $table='lab_software_requests';
 protected $fillable=['reference','contact_name','email','phone','organization','request_type','project_title','problem_statement','objectives','target_platforms','required_features','user_roles','integrations','estimated_users','budget_range','target_date','existing_system','additional_notes','status','assigned_to'];
 protected $casts=['target_platforms'=>'array','required_features'=>'array','user_roles'=>'array','integrations'=>'array','target_date'=>'date'];
}
