<?php

namespace App\Http\Controllers;

use App\Http\Repositories\ArticleRepository;
use Illuminate\Http\Request;
use App\Http\Requests\TypeRequest;
use App\Http\Requests\ProductRequest;
use App\Http\Repositories\ProductRepository;
use App\Http\Services\productservice;

class PharmacistController extends Controller
{
       protected $productrepo;
       protected $productservice;
       protected $articlerepo;


    public function __construct(ProductRepository $productrepo , productservice $productservice , ArticleRepository $articlerepo )
    {
        $this->productrepo = $productrepo;
         $this->productservice = $productservice;
         $this->articlerepo = $articlerepo;

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





}
