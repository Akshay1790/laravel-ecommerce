<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
// use Intervention\Image\Facades\Image;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Nette\Utils\Image;



class AdminController extends Controller
{
    public function index(){
        return view('admin.index');
    }

    public function brands(){
        $brands = Brand::orderBy('id','DESC')->paginate(10);
        return view('admin.brands',compact('brands'));
    }

    public function add_brand(){
        return view('admin.brand-add');
    }

    public function brand_store(Request $request ){
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug',
            'image' => 'mimes:png,jpg,jpeg|max:2048',
        ]);

        $brand = new Brand();
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);
        $image = $request->file('image');
        $file_extension = $image->getClientOriginalExtension(); // ✅ FIX
        $file_name = Carbon::now()->timestamp . '.' . $file_extension;
        $this->GenerateBrandThumbnailsImage($image,$file_name);
        $brand->image = $file_name;
        $brand->save();
        return redirect()->route('admin.brands')->with('status','Brand has been added succesfully!');
    }
    
    public function brand_edit($id){
        $brand = Brand::find($id);
        return view('admin.brand-edit',compact('brand'));
    }

    public function brand_update(Request $request)
    {
        $request->validate([
            'name'  => 'required',
            'slug'  => 'required|unique:brands,slug,' . $request->id,
            'image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
        ]);

        $brand = Brand::findOrFail($request->id);

        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);

        if ($request->hasFile('image')) {

            if ($brand->image && File::exists(public_path('uploads/brands/' . $brand->image))) {
                File::delete(public_path('uploads/brands/' . $brand->image));
            }

            $image = $request->file('image');
            $file_name = time() . '.' . $image->getClientOriginalExtension();

            $this->GenerateBrandThumbnailsImage($image, $file_name);
            $brand->image = $file_name;
        }

        $brand->save();

        return redirect()
            ->route('admin.brands')
            ->with('status', 'Brand has been updated successfully!');
    }

    
    public function GenerateBrandThumbnailsImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/brands');

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        $manager = new ImageManager(new Driver());

        $img = $manager->read($image->getRealPath());

        $img->cover(124, 124, 'top')
            ->save($destinationPath . '/' . $imageName);
    }

    public function brand_delete($id)
    {
        $brand = Brand::findOrFail($id);

        if ($brand->image && File::exists(public_path('uploads/brands/' . $brand->image))) {
            File::delete(public_path('uploads/brands/' . $brand->image));
        }

        $brand->delete();

        return redirect()
            ->route('admin.brands')
            ->with('status', 'Brand has been deleted successfully!');
    }

    public function categories(){
        $categories = Category::orderBy('id','DESC')->paginate(10);
        return view('admin.categories',compact('categories'));
    }

    public function category_add(){
        return view('admin.category-add');
    }

    public function category_store(Request $request){
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug',
            'image' => 'mimes:png,jpg,jpeg|max:2048',
        ]);

        $category = new Category();
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        $image = $request->file('image');
        $file_extension = $image->getClientOriginalExtension(); // ✅ FIX
        $file_name = Carbon::now()->timestamp . '.' . $file_extension;
        $this->GenerateCategoryThumbnailsImage($image,$file_name);
        $category->image = $file_name;
        $category->save();
        return redirect()->route('admin.categories')->with('status','Category has been added succesfully!');
    }
    public function GenerateCategoryThumbnailsImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/categories');

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        $manager = new ImageManager(new Driver());

        $img = $manager->read($image->getRealPath());

        $img->cover(124, 124, 'top')
            ->save($destinationPath . '/' . $imageName);
    }

    public function category_edit($id){
        $category = Category::find($id);
        return view('admin.category-edit', compact('category'));
    }

    public function category_update(Request $request){
        $request->validate([
            'name'  => 'required',
            'slug'  => 'required|unique:categories,slug,' . $request->id,
            'image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
        ]);

        $category = Category::findOrFail($request->id);

        $category->name = $request->name;
        $category->slug = Str::slug($request->name);

        if ($request->hasFile('image')) {

            if ($category->image && File::exists(public_path('uploads/categories/' . $category->image))) {
                File::delete(public_path('uploads/categories/' . $category->image));
            }

            $image = $request->file('image');
            $file_name = time() . '.' . $image->getClientOriginalExtension();

            $this->GenerateCategoryThumbnailsImage($image, $file_name);
            $category->image = $file_name;
        }

        $category->save();

        return redirect()
            ->route('admin.categories')
            ->with('status', 'Category has been updated successfully!');
    }

    public function category_delete($id){
        $category = Category::find($id);
        if(File::exists(public_path('uploads/categories').'/'.$category->image)){
            File::delete(public_path('uploads/categories').'/' .$category->image);
        }
        $category->delete();
        return redirect()->route('admin.categories')->with('status','Category has been deleted successfully!');
    }

    public function products(){
        $products = Product::orderBy('created_at', 'DESC')->paginate(10);
        return view('admin.products',compact('products'));
    }

    public function product_add(){
        $categories = Category::select('id','name')->orderby('name')->get();
        $brands = Brand::select('id','name')->orderBy('name')->get();
        return view('admin.product-add', compact('categories','brands'));
    }

    public function product_store(Request $request){
        
        // CLEAN PRICE FIRST
    $request->merge([
        'regular_price' => preg_replace('/[^0-9.]/', '', $request->regular_price),
        'sale_price' => preg_replace('/[^0-9.]/', '', $request->sale_price),
    ]);
    $request->validate([
        'name' => 'required',
        'short_description' => 'required',
        'description' => 'required',
        'regular_price' => 'required|numeric',
        'sale_price' => 'required|numeric',
        'SKU' => 'required',
        'stock_status' => 'required',
        'featured' => 'required',
        'quantity' => 'required',
        'image' => 'required|mimes:png,jpg,jpeg|max:2048',
        'images.*' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        'category_id' => 'required|integer|exists:categories,id',
        'brand_id' => 'required|integer|exists:brands,id',
    ]);

    $product = new Product();

    $product->name = $request->name;

    /*
    |--------------------------------------------------------------------------
    | Unique Slug Generator
    |--------------------------------------------------------------------------
    */
    $slug = Str::slug($request->name);
    $originalSlug = $slug;
    $count = 1;

    while (Product::where('slug', $slug)->exists()) {
        $slug = $originalSlug . '-' . $count++;
    }

    $product->slug = $slug;

    $product->short_description = $request->short_description;
    $product->description = $request->description;
    // Remove comma if user enters 2,222
    $product->regular_price = str_replace(',', '', $request->regular_price);
    $product->sale_price = str_replace(',', '', $request->sale_price);
    $product->SKU = $request->SKU;
    $product->stock_status = $request->stock_status;
    $product->featured = $request->featured;
    $product->quantity = $request->quantity;
    $product->category_id = $request->category_id;
    $product->brand_id = $request->brand_id;

    $current_timestamp = Carbon::now()->timestamp;

    /*
    |--------------------------------------------------------------------------
    | Main Image Upload
    |--------------------------------------------------------------------------
    */
    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $imageName = $current_timestamp . '.' . $image->getClientOriginalExtension();

        $this->GenerateProductThumbnailImage($image, $imageName);

        $product->image = $imageName;
    }

    /*
    |--------------------------------------------------------------------------
    | Gallery Images Upload
    |--------------------------------------------------------------------------
    */
    $gallery_arr = [];
    $counter = 1;

    if ($request->hasFile('images')) {

        foreach ($request->file('images') as $file) {

            $gextension = $file->getClientOriginalExtension();
            $gfileName = $current_timestamp . "-" . $counter . "." . $gextension;

            $this->GenerateProductThumbnailImage($file, $gfileName);

            $gallery_arr[] = $gfileName;
            $counter++;
        }

        $product->images = implode(',', $gallery_arr);
    }

    $product->save();

    return redirect()->route('admin.products')
        ->with('status', 'Product has been added successfully!');
    }


    public function GenerateProductThumbnailImage($image, $imageName){
    $destinationPath = public_path('uploads/products');
    $destinationPathThumbnail = public_path('uploads/products/thumbnails');

    if (!file_exists($destinationPath)) {
        mkdir($destinationPath, 0755, true);
    }

    if (!file_exists($destinationPathThumbnail)) {
        mkdir($destinationPathThumbnail, 0755, true);
    }

    $manager = new ImageManager(new Driver());

    // MAIN IMAGE
    $mainImage = $manager->read($image->getRealPath());
    $mainImage->cover(540, 689)
              ->save($destinationPath . '/' . $imageName);

    // THUMBNAIL
    $thumbImage = $manager->read($image->getRealPath());
    $thumbImage->cover(104, 104)
    ->save($destinationPathThumbnail . '/' . $imageName);
    }

    public function product_edit($id){
        $product = Product::find($id);
        $categories = Category::select('id','name')->orderby('name')->get();
        $brands = Brand::select('id','name')->orderBy('name')->get();
        return view('admin.product-edit',compact('product', 'categories', 'brands'));
    }

    public function product_update(Request $request){
        $product = Product::findOrFail($request->id);

        // CLEAN PRICE FIRST
        $request->merge([
            'regular_price' => preg_replace('/[^0-9.]/', '', $request->regular_price),
            'sale_price' => preg_replace('/[^0-9.]/', '', $request->sale_price),
        ]);
    $request->validate([
        'name' => 'required',
        'short_description' => 'required',
        'description' => 'required',
        'regular_price' => 'required|numeric',
        'sale_price' => 'required|numeric',
        'SKU' => 'required',
        'stock_status' => 'required',
        'featured' => 'required',
        'quantity' => 'required',
        'image' => 'mimes:png,jpg,jpeg|max:2048',
        'images.*' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        'category_id' => 'required|integer|exists:categories,id',
        'brand_id' => 'required|integer|exists:brands,id',
    ]);
    // $product = Product::find($request->id);
    // $product = new Product();

    $product->name = $request->name;

    /*
    |--------------------------------------------------------------------------
    | Unique Slug Generator
    |--------------------------------------------------------------------------
    */
    $slug = Str::slug($request->name);
    $originalSlug = $slug;
    $count = 1;

    while (
        Product::where('slug', $slug)
            ->where('id', '!=', $product->id)
            ->exists()
    ) {
        $slug = $originalSlug . '-' . $count++;
    }

    $product->slug = $slug;
    $product->short_description = $request->short_description;
    $product->description = $request->description;
    // Remove comma if user enters 2,222
    $product->regular_price = str_replace(',', '', $request->regular_price);
    $product->sale_price = str_replace(',', '', $request->sale_price);
    $product->SKU = $request->SKU;
    $product->stock_status = $request->stock_status;
    $product->featured = $request->featured;
    $product->quantity = $request->quantity;
    $product->category_id = $request->category_id;
    $product->brand_id = $request->brand_id;

    $current_timestamp = Carbon::now()->timestamp;
    /*
    |--------------------------------------------------------------------------
    | Main Image Upload
    |--------------------------------------------------------------------------
    */
    if ($request->hasFile('image')) {
         if(File::exists(public_path('uploads/products').'/'.$product->image)){
            File::delete(public_path('uploads/products').'/'.$product->image);
        }
        if(File::exists(public_path('uploads/products/thumbnails').'/'.$product->image)){
            File::delete(public_path('uploads/products/thumbnails').'/'.$product->image);
        }
        $image = $request->file('image');
        $imageName = $current_timestamp . '.' . $image->getClientOriginalExtension();

        $this->GenerateProductThumbnailImage($image, $imageName);

        $product->image = $imageName;
    }

    /*
    |--------------------------------------------------------------------------
    | Gallery Images Upload
    |--------------------------------------------------------------------------
    */
    $gallery_arr = [];
    $counter = 1;

    if ($request->hasFile('images')) {
        foreach(explode(',',$product->images) as $ofile){
            if(File::exists(public_path('uploads/products').'/'.$ofile)){
            File::delete(public_path('uploads/products').'/'.$ofile);
        }
        if(File::exists(public_path('uploads/products/thumbnails').'/'.$ofile)){
            File::delete(public_path('uploads/products/thumbnails').'/'.$ofile);
        }
        }  

        foreach ($request->file('images') as $file) {

            $gextension = $file->getClientOriginalExtension();
            $gfileName = $current_timestamp . "-" . $counter . "." . $gextension;

            $this->GenerateProductThumbnailImage($file, $gfileName);

            $gallery_arr[] = $gfileName;
            $counter++;
        }

        $product->images = implode(',', $gallery_arr);
    }

        $product->save();
        return redirect()->route('admin.products')->with('status','Product has been updated successfully!');
    }

    public function product_delete($id){
        $product = Product::find($id);
        if(File::exists(public_path('uploads/products').'/'.$product->image)){
            File::delete(public_path('uploads/products').'/'.$product->image);
        }
        if(File::exists(public_path('uploads/products/thumbnails').'/'.$product->image)){
            File::delete(public_path('uploads/products/thumbnails').'/'.$product->image);
        }

        foreach(explode(',',$product->images) as $ofile){
            if(File::exists(public_path('uploads/products').'/'.$ofile)){
            File::delete(public_path('uploads/products').'/'.$ofile);
            }
            if(File::exists(public_path('uploads/products/thumbnails').'/'.$ofile)){
                File::delete(public_path('uploads/products/thumbnails').'/'.$ofile);
            }
        }

        $product->delete();
        return redirect()->route('admin.products')->with('status','Product has been deleted successfully!');
    }
    


}
