<?php
namespace App\Http\Controllers\Labs;
use App\Http\Controllers\Controller;
use App\Models\LabSoftwareRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
class SoftwareRequestController extends Controller {
 public function create(){return view('pages.lab-request');}
 public function store(Request $request){
  $v=$request->validate([
   'contact_name'=>'required|string|max:120','email'=>'required|email|max:190','phone'=>'nullable|string|max:60',
   'organization'=>'nullable|string|max:160','request_type'=>'required|in:website,ecommerce,web_system,mobile,desktop,integration,other',
   'project_title'=>'required|string|max:190','problem_statement'=>'required|string|min:15|max:5000',
   'objectives'=>'nullable|string|max:5000','required_features'=>'nullable|string|max:5000',
   'budget_range'=>'nullable|string|max:120','target_date'=>'nullable|date|after_or_equal:today',
   'additional_notes'=>'nullable|string|max:5000'
  ]);
  $v['reference']='LAB-'.strtoupper(Str::random(12));
  $v['required_features']=array_filter(array_map('trim',preg_split('/[\r\n]+/',$v['required_features']??'')));
  $v['status']='new';
  LabSoftwareRequest::create($v);
  return redirect()->route('labs.request.create')->with('request_reference',$v['reference']);
 }
}
