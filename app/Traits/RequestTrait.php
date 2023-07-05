<?php

namespace App\Traits;

use GuzzleHttp\Client;

trait RequestTrait{
    public function makeAPICallToShopify($method, $endpoint, $headers, $requestBody = null){
        # Headers
        try{
            $client = new Client();
            $response = null;
            if($method == 'GET'){
                $response = $client->request($method, $endpoint, ['headers' => $headers]);
            } else if($method == 'POST' or $method == 'PUT'){
                $response = $client->request($method, $endpoint, ['headers' => $headers, 'json' => $requestBody]);
            } else if ($method == 'DELETE'){
                $response = $client->request($method, $endpoint, ['headers' => $headers]);
            }
            return [
                'status' => $response->getStatusCode(),
                'message' => "Action {$method} successful",
                'body' => json_decode($response->getBody(), true),
                'headers' => $response->getHeaders()
            ];
        } catch (\Exception $e){
            return [
                'status' => $e->getCode(),
                'message' => $e->getMessage(),
                'body' => null
            ];
        }
    }
    public function makeGrapqlCallToShopify($method, $endpoint, $headers, $requestBody = null)
    {
        try {
            $client = new Client();
            $response = $client->request($method, $endpoint, ['headers' => $headers, 'json' => $requestBody]);

            return [
                'status' => $response->getStatusCode(),
                'message' => "Action Grapql {$method} successful",
                'body' => json_decode($response->getBody(), true),
            ];
        } catch (\Exception $e) {
            return [
                'status' => $e->getCode(),
                'message' => $e->getMessage(),
                'body' => null
            ];
        }
    }
}