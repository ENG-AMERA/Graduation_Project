<?php
namespace App\Http\Repositories;

use App\Models\Pharma;
use App\Models\Role;
use App\Models\User;

class AdminRepository{

    public function getallusers(){

    $users = User::with('roles')->get();

    return response()->json([
        'users' => $users,
    ]);

    }

    public function getdelivaries(){
        $role=Role::where('name','Delivery')->with('user')->
        with('user.delivery')->get();
        return response()->json([
            'delivaries'=> $role,
        ]);

    }

    public function getallpharmas(){
        $pharma=Pharma::with('pharmacists')->with('pharmacists.user')->get();
             return response()->json([
            'pharmaswithdetails'=> $pharma,
        ]);
    }



}
