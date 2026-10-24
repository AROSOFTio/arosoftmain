<?php
namespace App\Http\Controllers\Labs;
use App\Http\Controllers\Controller;
use App\Models\LabProduct;
use Illuminate\Http\Request;
class MarketplaceController extends Controller {
 public function index(Request $request) {
  $kind=$request->query('kind');
  abort_if($kind && !in_array($kind,['desktop','web_app','mobile_app','website']),404);
  $products=LabProduct::query()->where('status','published')->when($kind,fn($q)=>$q->where('kind',$kind))
   ->when($request->filled('q'),fn($q)=>$q->where('name','like','%'.addcslashes($request->query('q'),'_%').'%'))
   ->orderByDesc('featured')->latest('published_at')->paginate(12)->withQueryString();
  return view('pages.lab-catalogue',compact('products','kind'));
 }
 public function show(string $slug) {
  $product=LabProduct::where('slug',$slug)->where('status','published')->with(['links','media'])->firstOrFail();
  return view('pages.lab-product',compact('product'));
 }
}
