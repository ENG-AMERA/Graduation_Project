<?php
namespace App\Http\Repositories;

use App\Models\Cart;
use App\Models\Cart_Item;
use App\Models\Type;
use App\Models\Product;
use App\Models\Category;
use App\Models\Pharmacist;
use App\Models\Recommendation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ProductRepository{

    public function Addmainproduct($pharmaid,$request)
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
            'pharma_id' => $pharmaid,
            'has_variants'=>$request->has_types,
        ]);


     return $product;
}

public function addproductwithtypes($pharmaid,$request)
{

   $product= $this->Addmainproduct($pharmaid,$request);
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

public function ShowProductsOfCategoryph($categoryid)
{
    $user_id=Auth::id();
    $pharmacist=Pharmacist::where('user_id',$user_id)->first();
    $pharmaid=$pharmacist->pharma->id;
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


  public function createcart($user_id,$pharma_id){
        $cart=Cart::create([
        'user_id'=>$user_id,
        'pharma_id'=>$pharma_id,
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

  public function evaluateproduct($product_id,$evaluation){
    $product=Product::where('id',$product_id)->first();
    $product->people=$product->people + 1;
    $product->evaluation=$product->evaluation + $evaluation;
    $product->finalevaluation=$product->evaluation/$product->people;
    $product->save();
}

public function addrecommendation(array $data)
{
    $id = Auth::id();

    $recommendation = Recommendation::create([
        'content'     => $data['content'],
        'product_id'  => $data['product_id'],
        'user_id'     => $id,
        'starnumber'  => $data['starnumber'],
        ]);

        $this->evaluateproduct($data['product_id'] , $data['starnumber']);

    if ($recommendation) {
        $pharmacist = Pharmacist::find($data['pharmacist_id']);

        if (!$pharmacist) {
            return response()->json(['message' => 'Pharmacist not found'], 404);
        }

        if ($pharmacist->accept_point == 1) {
            // Add 5 points to the authenticated user
            $user = User::find($id);
            $user->points += 5;
            $user->save();
        }
    }

    return response()->json(['message' => 'Recommendation added successfully. and point added to user'], 201);
}

public function showRecommendationOfProduct($id){
  $recommendation = Recommendation::with('user')
        ->where('product_id', $id)
        ->get();
    return response()->json($recommendation);
}


public function deleteRecommendation($id){
    $user_id=Auth::id();
    $recommendation=Recommendation::where('id',$id)->first();
    if($recommendation->user_id==$user_id){
         $recommendation->delete();
     return response()->json(['message' => 'recommendation deleted successfully.'], 201);

    }
    else{
     return response()->json(['message' => 'forbidden.'], 201);
    }

}

public function editquantityofproduct($request){
    $product=Product::where('id',$request->product_id)->first();
    $product->quantity=$request->quantity;
    $product->save();
}

 public function editquantityoftype($request){
    $type=Type::where('id',$request->type_id)->first();
    $type->quantity=$request->quantity;
    $type->save();
}

public function getproductofcart(){
    $user_id=Auth::id();
    $item=Cart::where('user_id',$user_id)->with('cart_item.type')
    ->with('cart_item.product')
    ->with('pharma')
    ->get();
     return response()->json([
        'cart info' => $item
    ], 200);
}


}

