<?php

namespace App\Http\Controllers;

use App\Http\Requests\PharmaRequest;
use App\Http\Services\PharmaService;

use App\Http\Requests\AcceptPharmacistRequest;


class PharmaController extends Controller
{
    protected $pharmaService;

    public function __construct(PharmaService $pharmaService)
    {
        $this->pharmaService = $pharmaService;
        
    }
 



    public function pharma_request(PharmaRequest $request)
    {
        $result = $this->pharmaService->createPharma($request->validated());

        return response()->json([
            'message' => 'Pharma and Pharmacist created successfully.',
            'data' => $result,
        ], 201);
    }


    public function accept(AcceptPharmacistRequest $request)
    {
        $pharmacist = $this->pharmaService->accept($request->id);
        return response()->json(['message' => 'Pharmacist accepted successfully', 'data' => $pharmacist], 200);
    }
    public function deletePharmacist($id)
    {
        try {
            $this->pharmaService->deletePharmacist($id);
            return response()->json(['message' => 'Pharmacist and pharma deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

   public function getPendingPharmacists()
    {
        $pharmacists = $this->pharmaService->getAllPending();
        return response()->json($pharmacists);
    }

}
