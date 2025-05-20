<?php
namespace App\Http\Repositories;
use App\Models\Pharma;
use App\Models\Pharmacist;
use App\Models\Role;
class PharmaRepository
{
    public function createPharma(array $data)
    {
        return Pharma::create($data);
    }

    public function createPharmacist(array $data)
    {
        return Pharmacist::create($data);
    }
    public function accept($id)
    {
        // Find the pharmacist by ID
        $pharmacist = Pharmacist::findOrFail($id);

        // Update the accept field to 1
        $pharmacist->update(['accept' => 1]);

        // Create the pharmacist role
        $role = Role::create([
            'user_id' => $pharmacist->user_id,
            'name' => 'pharmacist'
        ]);
        $userid= $pharmacist->user_id;
       
          Role::where('user_id', $userid)->where('name','Consumer')->delete();
            // Delete the pharmacist record

        return $pharmacist;
    }
    public function deletePharmacist($id)
    {
        try {
            // Find the pharmacist by ID
            $pharmacist = Pharmacist::findOrFail($id);
    
            // Delete the associated pharma record
            Pharma::where('id', $pharmacist->pharma_id)->delete();
            $userid= $pharmacist->user_id;
            // Delete the pharmacist record
            $pharmacist->delete();
    
            Role::where('user_id', $userid)->where('name','Pharmacist')->delete();
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Error deleting pharmacist and pharma: " . $e->getMessage());
        }
    }
    
}
