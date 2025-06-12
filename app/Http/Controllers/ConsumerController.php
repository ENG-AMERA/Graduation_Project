<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Services\productservice;
use App\Http\Repositories\ProductRepository;

class ConsumerController extends Controller
{
    protected $productrepo;
    protected $productservice;

    public function __construct(ProductRepository $productrepo ,productservice $productservice)
    {
        $this->productrepo = $productrepo;
        $this->productservice = $productservice;

    }
    public function ShowProductsOfCategory($pharmaid,$categoryid){
     return $this->productrepo->ShowProductsOfCategory($pharmaid,$categoryid);
    }
   Public function Allcategories(){
        return $this->productrepo->Allcategories();
    }
    public function AddToCart(Request $request){
        return $this->productservice->addtocart($request);
    }
    public function AddOnewithoutaddtocart(Request $request)
    {
        return $this->productrepo->AddOnewithoutaddtocart($request);
    }

      public function MinusOnewithoutaddtocart(Request $request)
    {
        return $this->productrepo->MinusOnewithoutaddtocart($request);
    }

    public function EditCartAddOne($itemid){
        return $this->productrepo->EditCartAddOne($itemid);
    }

    public function EditCartMinusOne($itemid){
        return $this->productrepo->EditCartMinusOne($itemid);
    }


}
