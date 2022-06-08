<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FlightsService;

class FlightsController extends Controller
{
    private $service;

    public function __construct(FlightsService $service){
        $this->service = $service;
    }

    public function getGroupedFlights(Request $request){
        return response()->json($this->service->getGroupedFlights($request));
    }
}
