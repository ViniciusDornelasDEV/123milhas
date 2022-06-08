<?php

namespace App\Repositories;

use GuzzleHttp\Client;

class FlightsRepository
{
    public function getFlights(){
        $client = new Client();
        $responseFlights = $client->get('http://prova.123milhas.net/api/flights');
        
        if($responseFlights->getStatusCode() !== 200){
            return array(
                'error'           =>  'Erro ao acessar API de voos!',
                'status_code'   => $responseFlights->getStatusCode()
            );
        }
        
        return json_decode($responseFlights->getBody());
    }
}
