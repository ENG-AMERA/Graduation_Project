<?php
namespace App\Http\Repositories;

use App\Models\Delivery;
use App\Models\Role;
class deliveryRepository
{
    public function createdelivery(array $data)
    {
        return Delivery::create($data);
    }
   
   
    public function accept($id)
    {
        // Find the pharmacist by ID
        $delivery = Delivery::findOrFail($id);

        // Update the accept field to 1
        $delivery->update(['accept' => 1]);

        // Create the pharmacist role
        $role = Role::create([
            'user_id' => $delivery->user_id,
            'name' => 'delivery'
        ]);
          $userid= $delivery->user_id;
       
          Role::where('user_id', $userid)->where('name','Consumer')->delete();
            // Delete the delivery record

        return $delivery;
    }
    public function deletdelivery($id)
    {
        try {
            // Find the pharmacist by ID
            $delivery = Delivery::findOrFail($id);
    
           $userid= $delivery->user_id;
       
                   $delivery->delete();
    
    
            Role::where('user_id', $userid)->where('name','Delivery')->delete();
            // Delete the pharmacist record
    
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Error deleting delivery" . $e->getMessage());
        }
    }
    

       public function getPendingdelivery()
    {
        return Delivery::with(['user'])
            ->whereNull('accept')
            ->get();
    }
}
