<?php

namespace App\Http\Controllers;

use App\Http\Repositories\AdminRepository;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    protected $adminrepo;

    public function __construct(AdminRepository $adminrepo )
    {
        $this->adminrepo = $adminrepo;

    }

    public function getallusers(){
        return $this->adminrepo->getallusers();
    }

    public function getalldelivaries(){
        return $this->adminrepo->getdelivaries();
    }
    public function getallpharmas(){

    return $this->adminrepo->getallpharmas();
}

    public function bills($pharma_id){

    return $this->adminrepo->bills($pharma_id);
}

public function getcomplaint(){
    return $this->adminrepo->getcomplaint();
}
public function getpharmaslocation(){
  return $this->adminrepo->getpharmaslocation();
}
public function deleteuser($user_id){
  return $this->adminrepo->deleteuser($user_id);
}

public function deletepharma($pharma_id){
  return $this->adminrepo->deletepharma($pharma_id);
}
public function getMonthlyOrderCounts(){
  return $this->adminrepo->getMonthlyOrderCounts();
}

public function detect_price(Request $request){
    return $this->adminrepo->detect_price($request);
}
public function edit_price(Request $request){
    return $this->adminrepo->edit_price($request);
}

public function getdeliveryprice(){
    return $this->adminrepo->getdeliveryprice();
}









}
