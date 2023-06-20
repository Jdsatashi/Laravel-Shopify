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
            ];
        } catch (\Exception $e){
            return [
                'status' => $e->getCode(),
                'message' => $e->getMessage(),
                'body' => null
            ];
        }
    }
    public function makePOSTCallToShopify($payload, $endpoint, $header = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header === NULL ? [] : $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $aHeaderInfo = curl_getinfo($ch);
        $curlHeaderSize = $aHeaderInfo['header_size'];
        $sBody = trim(mb_substr($result, $curlHeaderSize));

        return ['status' => $httpCode, 'body' => $sBody];
    }
}