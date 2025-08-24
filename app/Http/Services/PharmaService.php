<?php

namespace App\Http\Services;

use App\Http\Repositories\PharmaRepository;
use App\Models\Pharmacist;
use Illuminate\Support\Facades\Auth;
//use App\\Http\Repositories\PharmacistRepository;


class PharmaService
{
  

    protected $pharmaRepository;

    public function __construct(PharmaRepository $pharmaRepository)
    {
        $this->pharmaRepository = $pharmaRepository;
    }
/*
    public function createPharma(array $data)
    {
        // Create Pharma
        $pharma = $this->pharmaRepository->createPharma([
            'length' => $data['length'],
            'width' => $data['width'],
            'name' => $data['name'],
            'license' => $data['license'],
            'phone' => $data['phone'],
        ]);
        $userId = Auth::id();
        // Create Pharmacist linked to Pharma
        $pharmacist = $this->pharmaRepository->createPharmacist([
            'certificate' => $data['certificate'],
            'description' => $data['description'] ?? null,
          
            'user_id' => $userId,
            'pharma_id' => $pharma->id,
        ]);

        return [
            'pharma' => $pharma,
            'pharmacist' => $pharmacist,
        ];
    }*/

public function createPharma(array $data)
{

    if (isset($data['license']) && $data['license']) {
        $license = $data['license'];
        $licenseExtension = $license->getClientOriginalExtension();
        $licenseName = time() . '_license.' . $licenseExtension;
        $licensePath = 'licenses'; // Path to save the image
        $license->move(public_path($licensePath), $licenseName);
        $licenseRelativePath = $licensePath . '/' . $licenseName;
        $licenseFullUrl = url($licenseRelativePath); // Full URL for accessing the image
        $data['license'] = $licenseFullUrl; // Store URL
    }

    if (isset($data['certificate']) && $data['certificate']) {
        $certificate = $data['certificate'];
        $certificateExtension = $certificate->getClientOriginalExtension();
        $certificateName = time() . '_certificate.' . $certificateExtension;
        $certificatePath = 'certificates'; // Path to save the image
        $certificate->move(public_path($certificatePath), $certificateName);
        $certificateRelativePath = $certificatePath . '/' . $certificateName;
        $certificateFullUrl = url($certificateRelativePath); // Full URL for accessing the image
        $data['certificate'] = $certificateFullUrl; // Store URL
    }


    $pharma = $this->pharmaRepository->createPharma([
        'length' => $data['length'],
        'width' => $data['width'],
        'name' => $data['name'],
        'license' => $data['license'], // Store the URL of the uploaded license image
        'phone' => $data['phone'],
    ]);

    $userId = Auth::id();

   
    $pharmacist = $this->pharmaRepository->createPharmacist([
        'certificate' => $data['certificate'], // Store the URL of the uploaded certificate image
        'description' => $data['description'] ?? null,
        'user_id' => $userId,
        'pharma_id' => $pharma->id,
    ]);

    return [
        'pharma' => $pharma,
        'pharmacist' => $pharmacist,
    ];
}
/*
    public function accept($id)
    {
        return $this->pharmaRepository->accept($id);
    }
    */
      public function accept(int $id, FcmService $fcmService)
    {
        return $this->pharmaRepository->accept($id, $fcmService);
    }

    public function deletePharmacist($id, FcmService $fcmService)
    {
        try {
            // Call the repository to delete the pharmacist and pharma
            return $this->pharmaRepository->deletePharmacist($id, $fcmService);
        } catch (\Exception $e) {
            throw new \Exception("Failed to delete pharmacist and pharma: " . $e->getMessage());
        }
    }
    

      public function getAllPending()
    {
        return $this->pharmaRepository->getPendingPharmacists();
    }

    
      public function getPharmacists()
    {
        return $this->pharmaRepository->getPharmacists();
    }

    

    
 public function getAvailablePublicOrders()
    {
        return $this->pharmaRepository->getAvailablePublicOrders();
    }

      
 public function getAvailablePrivateOrders()
    {
        return $this->pharmaRepository->getAvailablePrivateOrders();
    }


    public function acceptOrder(array $data)
{
    return $this->pharmaRepository->acceptOrder($data);
}



public function refuseOrder(array $data, FcmService $fcmService)
{
    return $this->pharmaRepository->refuseOrder($data, $fcmService);
}


public function acceptRecommendation($userId)
{
    return $this->pharmaRepository->handleAccept($userId);
}

public function refuseRecommendation($userId)
{
    return $this->pharmaRepository->handleRefuse($userId);
}



    public function searchByName($name)
    {
        return $this->pharmaRepository->searchByName($name);
    }

    
    public function storeComplaint(array $data)
    {
        return $this->pharmaRepository->store($data);
    }
   /*  public function getAcceptPointState()
    {
        $userId = auth()->id(); 

        $pharmacist = Pharmacist::where('user_id', $userId)->first();

        return $pharmacist ? (bool) $pharmacist->accept_point : null;
    }*/
        
      public function getAcceptPointState()
    {
        $pharmacist = Pharmacist::with(['user:id,firstname,lastname,email,phone,photo', 'pharma:id,name'])
            ->where('user_id', auth()->id())
            ->first();

        if (! $pharmacist) {
            return null;
        }

        return [
            'id'           => $pharmacist->id,
            'certificate'  => $pharmacist->certificate,
            'description'  => $pharmacist->description,
            'license'      => $pharmacist->license,
            'accept'       => (bool) $pharmacist->accept,
            'accept_point' => (bool) $pharmacist->accept_point,
            'point_value'  => $pharmacist->point_value,
            'pharma'       => [
                'id'   => optional($pharmacist->pharma)->id,
                'name' => optional($pharmacist->pharma)->name,
            ],
            'user'         => [
                'id'        => optional($pharmacist->user)->id,
                'firstname' => optional($pharmacist->user)->firstname,
                'lastname'  => optional($pharmacist->user)->lastname,
                'email'     => optional($pharmacist->user)->email,
                'phone'     => optional($pharmacist->user)->phone,
                'photo'     => optional($pharmacist->user)->photo,
            ],
        ];
    }

}
