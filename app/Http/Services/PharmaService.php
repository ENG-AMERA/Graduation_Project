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
    
}
