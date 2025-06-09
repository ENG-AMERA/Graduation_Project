<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\TypeRequest;
use App\Http\Requests\ProductRequest;
use App\Http\Repositories\ProductRepository;
use App\Http\Services\productservice;

class PharmacistController extends Controller
{
       protected $productrepo;
       protected $productservice;


    public function __construct(ProductRepository $productrepo , productservice $productservice )
    {
        $this->productrepo = $productrepo;
         $this->productservice = $productservice;

    }
    public function Addproduct(ProductRequest $request)
    {
        return $this->productservice->addproduct($request);
    }



    Public function Allcategories(){
        return $this->productrepo->Allcategories();
    }

     public function ShowProductsOfCategory($pharmaid,$categoryid){
     return $this->productrepo->ShowProductsOfCategory($pharmaid,$categoryid);
    }


}
