<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\LabProduct;
use App\Models\LabSoftwareRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
class LabPublishingController extends Controller {
 public function index(){return view('admin.labs.index',['products'=>LabProduct::latest()->paginate(25),'requests'=>LabSoftwareRequest::latest()->limit(25)->get()]);}
 public function create(){return view('admin.labs.edit',['product'=>new LabProduct]);}
 public function edit(LabProduct $product){return view('admin.labs.edit',compact('product'));}
 public function store(Request $request){$data=$this->validated($request);$product=DB::transaction(function() use($data){$p=LabProduct::create($data['product']);$this->syncLinks($p,$data);return $p;});return redirect()->route('admin.labs.edit',$product)->with('notice','Product saved');}
 public function update(Request $request,LabProduct $product){$data=$this->validated($request,$product);DB::transaction(function() use($product,$data){$product->update($data['product']);$this->syncLinks($product,$data);});return back()->with('notice','Product updated');}
 private function validated(Request $request,?LabProduct $product=null):array {
  $v=$request->validate([
   'name'=>'required|string|max:190','kind'=>'required|in:desktop,web_app,mobile_app,website',
   'summary'=>'nullable|string|max:1000','description'=>'nullable|string|max:15000',
   'price'=>'nullable|numeric|min:0|max:999999999','currency'=>'required|in:USD,UGX',
   'billing_type'=>'required|in:free,one_time,subscription,quote','status'=>'required|in:draft,published,archived',
   'featured'=>'nullable|boolean','demo_url'=>'nullable|url|starts_with:https://|max:2048',
   'android_url'=>'nullable|url|starts_with:https://|max:2048','ios_url'=>'nullable|url|starts_with:https://|max:2048',
   'download_url'=>'nullable|url|starts_with:https://|max:2048','screenshots'=>'nullable|string|max:12000'
  ]);
  $slug=Str::slug($v['name']);
  if(!$product || $product->name!==$v['name']){
   $base=$slug;$i=2;
   while(LabProduct::where('slug',$slug)->when($product,fn($q)=>$q->where('id','!=',$product->id))->exists())$slug=$base.'-'.$i++;
  }else $slug=$product->slug;
  $p=array_intersect_key($v,array_flip(['name','kind','summary','description','price','currency','billing_type','status']));
  $p['slug']=$slug;$p['featured']=$request->boolean('featured');
  $p['published_at']=$p['status']==='published'?($product?->published_at ?? now()):null;
  return ['product'=>$p,'links'=>array_intersect_key($v,array_flip(['demo_url','android_url','ios_url','download_url'])),'screenshots'=>$v['screenshots']??''];
 }
 private function syncLinks(LabProduct $product,array $data):void {
  $product->links()->delete();
  foreach(['demo_url'=>['web','demo'],'android_url'=>['android','store'],'ios_url'=>['ios','store'],'download_url'=>['windows','download']] as $key=>$pair){
   if(!empty($data['links'][$key]))$product->links()->create(['platform'=>$pair[0],'purpose'=>$pair[1],'url'=>$data['links'][$key]]);
  }
  $product->media()->where('type','screenshot')->delete();
  $urls=array_filter(array_map('trim',preg_split('/\r?\n/',$data['screenshots'])));
  foreach(array_slice($urls,0,15) as $i=>$url)if(filter_var($url,FILTER_VALIDATE_URL)&&str_starts_with($url,'https://'))$product->media()->create(['type'=>'screenshot','path_or_url'=>$url,'sort_order'=>$i]);
 }
}
