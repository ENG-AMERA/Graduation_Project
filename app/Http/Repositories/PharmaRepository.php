<?php
namespace App\Http\Repositories;

use App\Models\Complaint;
use App\Models\DeliveryRequest;
use App\Models\Pharma;
use App\Models\Pharmacist;
use App\Models\Role;
use App\Models\PharmaUser;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
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

  
    
      public function getPendingPharmacists()
    {
        return Pharmacist::with(['user', 'pharma'])
            ->whereNull('accept')
            ->get();
    }

      
      public function getPharmacists()
    {
        return Pharmacist::with(['user', 'pharma'])
            ->where('accept',1)
            ->get();
    }


public function getAvailablePrivateOrders()
{
    $user = Auth::user();

    $pharmacist = Pharmacist::where('user_id', $user->id)->first();

    if (!$pharmacist) {
        throw new \Exception('Pharmacist not found for this user.');
    }

    $pharmaId = $pharmacist->pharma_id;

    $pharmaUsers = PharmaUser::with(['order' => function ($query) {
        $query->select(
            'id',
            'user_id',
            'name_medicine',
            'photo',
            'length',
            'width',
            'type',
            'time',
            'created_at',
            'updated_at'
        );
    }])
    ->where('type', 1)
    ->whereNull('accept_pharma')
    ->where('pharma_id', $pharmaId)
    ->get();

    // Add photo_path inside order relation
    $pharmaUsers->each(function ($pharmaUser) {
        if ($pharmaUser->order) {
            $photo = $pharmaUser->order->photo;

            if ($photo) {
                if (filter_var($photo, FILTER_VALIDATE_URL)) {
                    $parsedUrl = parse_url($photo);
                    $relativePath = ltrim($parsedUrl['path'], '/');
                } else {
                    $relativePath = $photo;
                }

                $pharmaUser->order->photo_path = $relativePath;
            } else {
                $pharmaUser->order->photo_path = null;
            }
        }
    });

    return $pharmaUsers;
}


public function getAvailablePublicOrders()
{
    $user = Auth::user();

    $pharmacist = Pharmacist::where('user_id', $user->id)->first();

    if (!$pharmacist) {
        throw new \Exception('Pharmacist not found for this user.');
    }

    $pharmaId = $pharmacist->pharma_id;

    $pharmaUsers = PharmaUser::with(['order' => function ($query) {
        $query->select(
            'id',
            'user_id',
            'name_medicine',
            'photo',
            'length',
            'width',
            'type',
            'time',
            'created_at',
            'updated_at'
        );
    }])
    ->where('type', 0)
    ->whereNull('accept_pharma')
    ->where('pharma_id', null)
    ->get();

    // Add photo_path inside order relation
    $pharmaUsers->each(function ($pharmaUser) {
        if ($pharmaUser->order) {
            $photo = $pharmaUser->order->photo;

            if ($photo) {
                if (filter_var($photo, FILTER_VALIDATE_URL)) {
                    $parsedUrl = parse_url($photo);
                    $relativePath = ltrim($parsedUrl['path'], '/');
                } else {
                    $relativePath = $photo;
                }

                $pharmaUser->order->photo_path = $relativePath;
            } else {
                $pharmaUser->order->photo_path = null;
            }
        }
    });

    return $pharmaUsers;
}
public function acceptOrder(array $data)
{


    // Find the PharmaUser record
     $pharmaUser = PharmaUser::where('order_id', $data['order_id'])->first();


    if (!$pharmaUser) {
        return response()->json(['message' => 'PharmaUser not found'], 404);
    }
    
    $user = Auth::user();

    // Get the pharmacist based on user ID
    $pharmacist = Pharmacist::where('user_id', $user->id)->first();

    if (!$pharmacist) {
        throw new \Exception('Pharmacist not found for this user.');
    }

    $pharmaId = $pharmacist->pharma_id;


    // Update accept_pharma = 1
    $pharmaUser->update([
        'accept_pharma' => 1,
        'pharma_id'=>$pharmaId


]);
// Create a DeliveryRequest with null qr, price, delivery_id
    DeliveryRequest::create([
        'qr' => null,
        'price' => $data['price'],
        'delivery_id' => null,
        'pharma_user_id' => $pharmaUser->id,
    ]);
 

    return response()->json(['message' => 'Order accepted and delivery request created'], 200);
}


public function refuseOrder(array $data)
{
    $pharmaUser = PharmaUser::where('order_id', $data['order_id'])->first();

    if (!$pharmaUser) {
        return response()->json(['message' => 'PharmaUser not found'], 404);
    }

       $user = Auth::user();

    // Get the pharmacist based on user ID
    $pharmacist = Pharmacist::where('user_id', $user->id)->first();

    if (!$pharmacist) {
        throw new \Exception('Pharmacist not found for this user.');
    }

    $pharmaId = $pharmacist->pharma_id;



    // Update accept_pharma and reason
    $pharmaUser->update([
        'accept_pharma' => 0,
        'reason' => $data['reason'],
          'pharma_id'=>$pharmaId

    ]);

    // Create delivery request with null fields
    DeliveryRequest::create([
        'qr' => null,
        'price' => null,
        'delivery_id' => null,
        'pharma_user_id' => $pharmaUser->id,
    ]);

    return response()->json(['message' => 'Order refused and delivery request created'], 200);
}


public function handleAccept($userId)
{
    $user = User::find($userId);

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }


    // Optional: set accept_point in Pharmacist model if needed
    $pharmacist = Pharmacist::where('id', $userId)->first();
    if ($pharmacist) {
        $pharmacist->update(['accept_point' => 1]);
    }

    return response()->json(['message' => 'Accepted done']);
}


public function handleRefuse($userId)
{
   $user = User::find($userId);

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

  

    // Optional: set accept_point in Pharmacist model if needed
    $pharmacist = Pharmacist::where('id', $userId)->first();
    if ($pharmacist) {
        $pharmacist->update(['accept_point' => 0]);
    }

    return response()->json(['message' => 'refuse done']);
}

public function searchByName($name)
{
    return Pharma::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($name) . '%'])->get();
}

 public function store(array $data)
    {
        return Complaint::create($data);
    }
}
