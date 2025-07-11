<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Services\productservice;
use App\Http\Repositories\ArticleRepository;
use App\Http\Repositories\authrepo;
use App\Http\Repositories\ProductRepository;
use App\Http\Requests\AddRecommendationRequest;
use App\Http\Requests\ConfirmCartOrderRequest;
use App\Http\Repositories\CartOrdersRepository;
   use App\Http\Requests\UpdatePhotoRequest;
use Illuminate\Support\Facades\Auth;

class ConsumerController extends Controller
{
    protected $productrepo;
    protected $productservice;
    protected $articlerepo;
    protected $cartorderrepo;
    protected $authrepo;
    public function __construct(ProductRepository $productrepo ,productservice $productservice ,

    ArticleRepository $articlerepo , CartOrdersRepository $cartorderrepo, authrepo $authrepo )
    {
        $this->productrepo = $productrepo;
        $this->productservice = $productservice;
        $this->articlerepo = $articlerepo;
         $this->cartorderrepo = $cartorderrepo;
           $this->authrepo = $authrepo;


    }
    public function ShowProductsOfCategoryc($pharmaid,$categoryid){
     return $this->productrepo->ShowProductsOfCategory($pharmaid,$categoryid);
    }
   Public function Allcategoriesc(){
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

     public function confirmcartorder(ConfirmCartOrderRequest $request)
     {
        return $this->cartorderrepo->confirmorder($request);
    }

    public function show_qr_ofcartorderwithdetail($cartorder_id){
        return $this->cartorderrepo->show_qr_ofcartorderwithdetail($cartorder_id);
    }

    public function getproductofcart($pharma_id){
        return $this->productrepo->getproductofcart($pharma_id);
    }

        public function getordersforconsumer($pharma_id){
        return $this->cartorderrepo->getordersforconsumer($pharma_id);
    }

        public function cartorderarchive($pharma_id){
        return $this->cartorderrepo->cartorderarchive($pharma_id);
    }




    public function profile()
    {
        $user = Auth::user();
        $profile = $this->authrepo->getUserProfileById($user->id);

        return response()->json([
            'status' => true,
            'data' => $profile
        ]);
    }

 

public function updatePhoto(UpdatePhotoRequest $request)
{
    $user = auth()->user();
    $path = $this->authrepo->updateUserPhoto($user->id, $request->file('photo'));

    return response()->json([
        'status' => true,
        'message' => 'Photo updated successfully',
        'photo_url' => $path,
    ]);
}

}
