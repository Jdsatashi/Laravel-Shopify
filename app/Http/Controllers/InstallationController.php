<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class InstallationController extends Controller
{
    use functionTrait, RequestTrait;
    public function startInstallation(Request $req)
    {
        try{
            $valid = $this->validateRequestFromShopify($req->all());

            if($valid){
                $shop = $req->has('shop');
                if($shop){
                    $storeDetail = $this->getStoreDomain($req->shop);
                    if($storeDetail !== null && $storeDetail !== false){
                        $validAccessToken = $this->checkIfTokenIsValid($storeDetail);
                        if($validAccessToken){
                            return view('welcome');
                        } else {
                            print_r("Invalid token.");
                        }
                    } else {
                        $client_id = config('custom.shopify_api_key');
                        $scopes = config('custom.api_scopes');
                        $url = config('app.url');
                        $uri = $url . "shopify/auth/redirect";
                        Log::info('New installation for shop' . $req->shop);
                        $endpoint = "https://{$req->shop}/admin/oauth/authorize?client_id={$client_id}&scope={$scopes}&redirect_uri={$uri}";
                        return Redirect::to($endpoint);
                    }
                } else throw new Exception('Invalid Shop!');
            } else throw new Exception('Invalid Request start install!');
        }
        catch (Exception $e){
            Log::info($e->getMessage() . ' ' . $e->getLine());
            dd($e->getMessage() . ' ' . $e->getLine());
        }
    }


    public function handleRedirect(Request $req){
        try{
            $valid = $this->validateRequestFromShopify($req->all());
            if($valid){
                Log::info(json_encode($req->all()));
                if($req->has('shop') && $req->has('code')){
                    $shop = $req->shop;
                    $code = $req->code;
                    $access_token = $this->requestTokenFromShopifyForStore($shop,$code);
                    if($access_token){
                        $shopDetail = $this->getShopDetailFromShopify($shop, $access_token);
                        $saveDetail = $this->saveStoreDetail($shopDetail, $access_token);
                        if($saveDetail){
                            Log::info("Successful save detail." . $saveDetail);
                            $uri = config('app.url');
                            $complete = 'shopify/auth/complete';
                            $complete_uri = $uri . $complete;
                            return redirect($complete_uri);
                        } else {
                            Log::info("Problems when saving.\n" . $saveDetail);
                            Log::info("Shop details: " . $shopDetail);
                        }
                    } else{
                        throw new Exception("Problems to get access token.");
                    }
                } else throw new Exception("Code or Shop is not present in the url");
            } else throw new Exception("Invalid Request redirect!");
        } catch (Exception $e){
            Log::info($e->getMessage() . ' ' . $e->getLine());
            dd($e->getMessage() . ' ' . $e->getLine());
        }
    }

    private function saveStoreDetail($shopDetail, $access_token)
    {
        try{
            $payload = [
                'access_token' => $access_token,
                'myshopify_domain' => $shopDetail['myshopify_domain'],
                'id' => $shopDetail['id'],
                'name' => $shopDetail['name'],
                'phone' => $shopDetail['phone'],
                'address1' => $shopDetail['address1'],
                'address2' => $shopDetail['address2'],
                'zip' => $shopDetail['zip'],
            ];
            Store::updateOrCreate( ['myshopify_domain' => $shopDetail['myshopify_domain']], $payload);
            return true;
        } catch (Exception $e){
            Log::info($e->getMessage() . ' ' . $e->getLine());
            return false;
        }
    }

    public function completeInstallation(){
        return view('welcome');
    }

    public function getShopDetailFromShopify($shop, $access_token){
        try {
            $endpoint = getShopifyUrlForStore('shop.json', ['myshopify_domain' => $shop]);
            $headers = getShopifyHeadersForStore(['access_token' => $access_token]);
            $response = $this->makeAPICallToShopify('GET', $endpoint, $headers);
            Log::info($response);
            if($response['status'] == 200){
                $body = $response['body'];
                if(!is_array($body)) $body = json_decode($body, true);
                return $body['shop'] ?? null;
            } else {
                Log::info("Response Shopify details was got\n" . json_encode($response));
                return null . "Null response";
            }
        } catch(Exception $e){
            Log::info("Problems to get details of shopify\n".$e->getMessage() . ' ' . $e->getLine());
            dd($e->getMessage() . ' ' . $e->getLine());
        }
    }

    public function requestTokenFromShopifyForStore($shop,$code){
        try{
            $endpoint = 'https://' . $shop . '/admin/oauth/access_token?client_id=' . config('custom.shopify_api_key') . '&client_secret=' . config('custom.shopify_api_secret') . '&code=' . $code;
            $headers = [
                'Content-Type' => 'application/json'
            ];
            $response = $this->makeAPICallToShopify('POST', $endpoint, $headers, null);
            Log::info("Response getting Token\nToken: " . json_encode($response));
            if($response['status'] == 200){
                $body = $response['body'];
                if(!is_array($body)) $body = json_decode($body, true);
                if(isset($body['access_token']) && $body['access_token']){
                    return $body['access_token'];
                }
            }
        } catch (Exception $e){
            Log::info($e->getMessage() . ' ' . $e->getLine());
            dd($e->getMessage() . ' ' . $e->getLine());
        }
    }

    private function validateRequestFromShopify($request)
    {
        try {
            $arr = [];
            $hmac = $request['hmac'];
            unset($request['hmac']);
            foreach ($request as $key => $value) {
                $key = str_replace("%", "%25", $key);
                $key = str_replace("&", "%26", $key);
                $key = str_replace("=", "%3D", $key);
                $value = str_replace("%", "%25", $value);
                $value = str_replace("&", "%26", $value);
                $arr[] = $key . "=" . $value;
            }
            $str = implode('&', $arr);
            $ver_hmac =  hash_hmac('sha256', $str, config('custom.shopify_api_secret'), false);
            return $ver_hmac === $hmac;
        } catch (Exception $e) {
            Log::info('Problem with verify hmac from request');
            Log::info($e->getMessage() . ' ' . $e->getLine());
            return false;
        }
    }

    private function checkIfTokenIsValid($storeDetail)
    {
        try{
            if($storeDetail !== null && isset($storeDetail->access_token) && strlen($storeDetail->access_token) > 0){
                $token = $storeDetail->access_token;
                $endpoint = getShopifyUrlForStore('shop.json', $storeDetail);
                $headers = getShopifyHeadersForStore($storeDetail);
                $response = $this->makeAPICallToShopify('GET', $endpoint, $headers, null);
                return $response['status'] === 200;
            }
            return false;
        } catch (Exception $e){
            return false;
        }
    }
}
