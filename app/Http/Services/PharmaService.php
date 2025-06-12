<?php

namespace App\Http\Services;

use App\Http\Repositories\PharmaRepository;
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

    public function accept($id)
    {
        return $this->pharmaRepository->accept($id);
    }
    public function deletePharmacist($id)
    {
        try {
            // Call the repository to delete the pharmacist and pharma
            return $this->pharmaRepository->deletePharmacist($id);
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


public function refuseOrder(array $data)
{
    return $this->pharmaRepository->refuseOrder($data);
}




}
