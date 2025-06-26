<?php

namespace App\Http\Services;

use App\Models\Cart;
use App\Models\Type;
use App\Models\Product;
use App\Models\Cart_Item;
use App\Models\Pharmacist;
use Illuminate\Support\Facades\Auth;
use App\Http\Repositories\ProductRepository;

class productservice{

    protected $ProductRepository;

    public function __construct(ProductRepository $ProductRepository)
    {
        $this-> ProductRepository = $ProductRepository;
    }

    public function addproduct($request){
    $user_id=Auth::id();
    $pharmacist=Pharmacist::where('user_id',$user_id)->first();
    $pharmaid=$pharmacist->pharma->id;
        if ($request->has_types=='1'){
             return $this->ProductRepository->addproductwithtypes($pharmaid,$request);
        }
           if ($request->has_types=='0'){
             return $this->ProductRepository->Addmainproduct($pharmaid ,$request);
        }
    }


    public function addtocart($request)
    {
        $user_id=Auth::id();
        $cart=Cart::where('user_id',$user_id)
        ->where('pharma_id',$request->pharma_id)->first();

        if($request->type_id)
        {

            $type=Type::where('id',$request->type_id)->first();
               if($type->quantity>=$request->quantity)
    {
            if($cart){
                         $existingItem = Cart_Item::where('cart_id', $cart->id)
        ->where('product_id', $request->product_id)
        ->where('type_id', $request->type_id)
        ->first();
        if($existingItem ){

            $this->ProductRepository->addold($request,$existingItem,$cart);

        }
            if(!$existingItem ){
                $this->ProductRepository->addnewtype($request,$cart);

        }

        }
        if(!$cart)
        {
            $cart=$this->ProductRepository->createcart($user_id,$request->pharma_id);
            $this->ProductRepository->addnewtype($request,$cart);

        }
    }

    else{
             return response()->json(['message' => ' quantity is not available.'], 200);
    }


    }
        else{
            $product=Product::where('id',$request->product_id)->first();

            if($product->quantity>=$request->quantity)
    {
             if($cart)
             {
             $existingItem = Cart_Item::where('cart_id', $cart->id)
        ->where('product_id', $request->product_id)
        ->first();
          if($existingItem ){
            $this->ProductRepository->addold($request,$existingItem,$cart);

        }
            if(!$existingItem ){
                $this->ProductRepository->addnewproduct($request,$cart);

        }
             }


         if(!$cart)
        {
           $cart=$this->ProductRepository->createcart($user_id,$request->pharma_id);
            $this->ProductRepository->addnewproduct($request,$cart);

        }
    }
    else {
        return response()->json(['message' => ' quantity is not available.'], 200);
        }


    }


    }










}
