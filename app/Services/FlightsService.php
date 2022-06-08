<?php

namespace App\Services;

use App\Repositories\FlightsRepository;

class FlightsService
{
    private $repository;
    private $flights = false;
    private $outbounds = array();
    private $inbounds = array();
    private $groupedFlights = array();
    
    public function __construct(FlightsRepository $repository){
        $this->repository = $repository;
    }

    public function getGroupedFlights($request){
        $this->flights = $this->repository->getFlights();
        if(isset($this->flights['error'])){
            return $this->flights;
        }

        $this->groupOutboundsInbounds();
        $this->initGroups();
        $this->createFlightGroups();
        
        //order groups by totalPrice
        $this->groupedFlights['groups'] = collect($this->groupedFlights['groups'])->sortBy('totalPrice')->toArray();
        $this->setTotals();

        return $this->groupedFlights;
    }

    private function initGroups(){
        if($this->flights !== false){
            $this->groupedFlights = array(
                'flights'       =>  $this->flights,
                'groups'        =>  array(),
                'totalGroups'   =>  0,
                'totalFlights'  =>  count($this->flights),
                'cheapestPrice' =>  false,
                'cheapestGroup' =>  false
            );
        }
    }

    private function groupOutboundsInbounds(){
        //group outbounds and inbounds by fare and price
        foreach($this->flights as $key => $flight){
            if($flight->outbound == 1){
                if(!array_key_exists($flight->price.$flight->fare, $this->outbounds)){
                    $this->outbounds[$flight->price.$flight->fare] = array();                    
                }
                $this->outbounds[$flight->price.$flight->fare][] = $flight;
            }else{
                if(!array_key_exists($flight->price.$flight->fare, $this->inbounds)){
                    $this->inbounds[$flight->price.$flight->fare] = array();                    
                }
                $this->inbounds[$flight->price.$flight->fare][] = $flight;
            }
        }
    }

    private function createFlightGroups(){
        $countGroups = 0;
        foreach($this->outbounds as $key => $outbound){
            foreach($this->inbounds as $inbound){
                if($outbound[0]->fare == $inbound[0]->fare){
                    $this->groupedFlights['groups'][$countGroups] = array(
                        'uniqueId'      =>  $countGroups,
                        'totalPrice'    =>  $outbound[0]->price + $inbound[0]->price,
                        'outbound'      =>  $outbound,
                        'inbound'       =>  $inbound
                    );
                    $countGroups++;
                }
            }
        }
    }

    private function setTotals(){
        if(count($this->groupedFlights['groups']) > 0){
            $this->groupedFlights['totalGroups'] = count($this->groupedFlights['groups']);
            $this->groupedFlights['cheapestPrice'] = $this->groupedFlights['groups'][0]['totalPrice'];
            $this->groupedFlights['cheapestGroup'] = $this->groupedFlights['groups'][0]['uniqueId'];
        }   
    }
}