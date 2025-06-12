<?php
namespace App\Http\Repositories;

use App\Models\Cart;
use App\Models\Cart_Item;
use App\Models\Type;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class ProductRepository{

    public function Addmainproduct($request)
{
   if ($request->hasFile('image')) {
    $image = $request->file('image');
    $imageExtension = $image->getClientOriginalExtension();
    $imageName = time() . '_' . uniqid() . '.' . $imageExtension;
    $imagePath = 'products';
    $image->move(public_path($imagePath), $imageName);
    $imageRelativePath = $imagePath . '/' . $imageName;

}

       $product= Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'image' => $imageRelativePath,
            'category_id' => $request->category_id,
            'pharma_id' => $request->pharma_id,
            'has_variants'=>$request->has_types,
        ]);


     return $product;
}

public function addproductwithtypes($request)
{

   $product= $this->Addmainproduct($request);
   $productid=$product->id;
     $names = $request->input('tname');
    $prices = $request->input('tprice');
    $quantities = $request->input('tquantity');
    $images = $request->file('timage');

    $count = count($names);

       for ($i = 0; $i < $count; $i++) {
        $imageRelativePath = null;

        if (isset($images[$i])) {
            $image = $images[$i];
            $imageExtension = $image->getClientOriginalExtension();
            $imageName = time() . '_' . uniqid() . '.' . $imageExtension;
            $imagePath = 'types';
            $image->move(public_path($imagePath), $imageName);
            $imageRelativePath = $imagePath . '/' . $imageName;
        }

        Type::create([
            'name' => $names[$i],
            'product_id' => $productid,
            'price' => $prices[$i],
            'quantity' => $quantities[$i],
            'image' => $imageRelativePath,

        ]);
    }


    return response()->json(['message' => 'product and types stored successfully.'], 201);

}

public function Allcategories(){
 $categories=Category::get();
 return response()->json($categories, 201);
}

public function ShowProductsOfCategory($pharmaid,$categoryid)
{
    $products = Product::where('pharma_id', $pharmaid)
                ->where('category_id', $categoryid)
                ->with('types')
                ->get();

    return response()->json([
        'products' => $products,
    ], 200);

}


  public function addnewtype($request,$cart){
   $type=Type::where('id',$request->type_id)->first();
    $price=$request->quantity*$type->price;
           Cart_Item::create([
           'product_id'=>$request->product_id,
           'type_id'=>$request->type_id,
           'quantity'=>$request->quantity,
           'cart_id'=>$cart->id,
           'totalprice'=>$price,

        ]);
        $newprice=$cart->totalprice+$price;
        $cart->totalprice=$newprice;
        $cart->save();
  }


  public function addnewproduct($request,$cart){
   $product=Product::where('id',$request->product_id)->first();
    $price=$request->quantity*$product->price;
           Cart_Item::create([
           'product_id'=>$request->product_id,
           'type_id'=>$request->type_id,
           'quantity'=>$request->quantity,
           'cart_id'=>$cart->id,
           'totalprice'=>$price,

        ]);
        $newprice=$cart->totalprice+$price;
        $cart->totalprice=$newprice;
        $cart->save();
  }





  public function addold($request,$existingItem,$cart){
    $product=Product::where('id',$request->product_id)->first();
       $existingItem->quantity+= $request->quantity;
         $existingItem->save();

        $price=$request->quantity*$product->price;
        if($request->type_id)
        {
        $type=Type::where('id',$request->type_id)->first();
        $price=$request->quantity*$type->price;
        }
        $existingItem->totalprice=$existingItem->totalprice+$price;
        $existingItem->save();
           $newprice=$cart->totalprice+$price;
        $cart->totalprice=$newprice;
        $cart->save();

  }


  public function createcart($user_id){
        $cart=Cart::create([
        'user_id'=>$user_id,
        'totalprice'=>'0'
    ]);
    return $cart;
  }




  public function AddOnewithoutaddtocart($request)
  {
   $newprice = 0;
   if($request->type_id){
    $type=Type::where('id',$request->type_id)->first();
    $lastnumber=$request->lastnumber;
    $newnumber=$lastnumber + 1;
    $newprice=$type->price*$newnumber;
   }
   else{
    $product=Product::where('id',$request->product_id)->first();
    $lastnumber=$request->lastnumber;
    $newnumber=$lastnumber + 1;
    $newprice=$product->price*$newnumber;
   }

   return $newprice;

  }

    public function MinusOnewithoutaddtocart($request)
  {
   $newprice = 0;
   if($request->type_id){
    $type=Type::where('id',$request->type_id)->first();
    $lastnumber=$request->lastnumber;
    $newnumber=$lastnumber - 1;
    $newprice=$type->price*$newnumber;
   }
   else{
    $product=Product::where('id',$request->product_id)->first();
    $lastnumber=$request->lastnumber;
    $newnumber=$lastnumber - 1;
    $newprice=$product->price*$newnumber;
   }

   return $newprice;

  }



  public function EditCartAddOne($itemid)
  {

    $Item=Cart_Item::where('id',$itemid)->first();
    $Item->quantity=$Item->quantity + 1;
     if($Item->type_id){
    $type=Type::where('id',$Item->type_id)->first();
    $cart=Cart::where('id',$Item->cart_id)->first();
    if($type->quantity>=$type->quantity+1){
    $price=$type->price;
    $Item->totalprice=$Item->totalprice+$price;
    $cart->totalprice=$cart->totalprice+$price;
   }
   else{
     return response()->json(['message' => ' quantity is not available.'], 200);
   }



}
   else{
    $product=Product::where('id',$Item->product_id)->first();
    $cart=Cart::where('id',$Item->cart_id)->first();
    if($product->quantity>=$product->quantity+1){
    $price=$product->price;
    $Item->totalprice=$Item->totalprice+$price;
    $cart->totalprice=$cart->totalprice+$price;
   }
      else
      {
     return response()->json(['message' => ' quantity is not available.'], 200);
   }

}
   $Item->save();
   $cart->save();
return response()->json(['message' => 'product added successfully.'], 201);
  }


    public function EditCartMinusOne($itemid)
    {
    $user_id=Auth::id();
    $Item=Cart_Item::where('id',$itemid)->first();
    $Item->quantity=$Item->quantity - 1;
     if($Item->type_id){
    $type=Type::where('id',$Item->type_id)->first();
    $cart=Cart::where('id',$Item->cart_id)->first();
    $price=$type->price;
    $Item->totalprice=$Item->totalprice-$price;
    $cart->totalprice=$cart->totalprice-$price;
   }
   else{
    $product=Product::where('id',$Item->product_id)->first();
    $cart=Cart::where('id',$Item->cart_id)->first();
    $price=$product->price;
    $Item->totalprice=$Item->totalprice-$price;
    $cart->totalprice=$cart->totalprice-$price;
   }
   $Item->save();
   $cart->save();
return response()->json(['message' => 'product minused successfully.'], 201);
  }


}
