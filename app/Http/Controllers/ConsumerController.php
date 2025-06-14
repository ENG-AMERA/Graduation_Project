<?php

namespace App\Http\Controllers;

use App\Http\Repositories\ArticleRepository;
use Illuminate\Http\Request;
use App\Http\Services\productservice;
use App\Http\Repositories\ProductRepository;
use App\Http\Requests\AddRecommendationRequest;

class ConsumerController extends Controller
{
    protected $productrepo;
    protected $productservice;
    protected $articlerepo;

    public function __construct(ProductRepository $productrepo ,productservice $productservice ,ArticleRepository $articlerepo)
    {
        $this->productrepo = $productrepo;
        $this->productservice = $productservice;
        $this->articlerepo = $articlerepo;

    }
    public function ShowProductsOfCategoryc($pharmaid,$categoryid){
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

        public function getAllArticles(){
        return $this->articlerepo->getAllArticles();
    }

    public function addlike($id){
        return $this->articlerepo->addlike($id);
    }

        public function adddislike($id){
        return $this->articlerepo->adddislike($id);
    }

         public function removedislike($id){
        return $this->articlerepo->removedislike($id);
    }

         public function removelike($id){
        return $this->articlerepo->removelike($id);
    }

    public function evaluateproduct(Request $request){
        return $this->productrepo->evaluateproduct($request);
    }

   public function addrecommendation(AddRecommendationRequest $request)
{
    return $this->productrepo->addrecommendation($request->validated());
}

     public function showRecommendationOfProduct($product_id)
     {
        return $this->productrepo->showRecommendationOfProduct($product_id);
    }
    public function deleteRecommendation($recommendation_id)
     {
        return $this->productrepo->deleteRecommendation($recommendation_id);
    }


}
