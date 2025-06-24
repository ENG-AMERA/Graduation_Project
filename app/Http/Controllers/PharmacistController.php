<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\TypeRequest;
use App\Http\Requests\ProductRequest;
use App\Http\Services\productservice;
use App\Http\Repositories\ArticleRepository;
use App\Http\Repositories\ProductRepository;
use App\Http\Repositories\CartOrdersRepository;

class PharmacistController extends Controller
{
       protected $productrepo;
       protected $productservice;
       protected $articlerepo;
       protected $cartorderrepo;

    public function __construct(ProductRepository $productrepo , productservice $productservice ,
     ArticleRepository $articlerepo , CartOrdersRepository $cartorderrepo)
    {
        $this->productrepo = $productrepo;
         $this->productservice = $productservice;
         $this->articlerepo = $articlerepo;
         $this->cartorderrepo = $cartorderrepo;

    }
    public function Addproduct(ProductRequest $request)
    {
        return $this->productservice->addproduct($request);
    }



    Public function Allcategories(){
        return $this->productrepo->Allcategories();
    }

     public function ShowProductsOfCategoryph($categoryid){
     return $this->productrepo->ShowProductsOfCategoryph($categoryid);
    }

    public function addarticel(Request $request){
        return $this->articlerepo->addarticle($request);
    }

    public function showmyarticles(){
          return $this->articlerepo->showmyarticles();

    }

    public function deletearticle($id){
        return $this->articlerepo->deletearticle($id);
    }

    public function editcontent(Request $request){
        return $this->articlerepo->editcontent($request);
    }

     public function edittopic(Request $request){
        return $this->articlerepo->edittopic($request);
    }

     public function getallcartorderforpharmacist()
     {
        return $this->cartorderrepo->getallcartorderforpharmacist();
    }
      public function acceptcartorder($cartorder_id)
     {
        return $this->cartorderrepo->acceptcartorder($cartorder_id);
    }

     Public function editproduct(Request $request){
        return $this->productrepo->editquantityofproduct($request);
    }
     Public function edittype(Request $request){
        return $this->productrepo->editquantityoftype($request);
    }







}
