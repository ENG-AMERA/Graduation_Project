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








}
