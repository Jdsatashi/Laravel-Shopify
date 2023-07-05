<?php

if (!function_exists('getShopifyURLForStore')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function getShopifyURLForStore( $endpoint, $store)
    {
        return 'https://' . $store['myshopify_domain'] . '/admin/api/' . config('custom.shopify_api_version') . '/' . $endpoint;
    }
    function getShopifyHeadersForStore($storeDetail){
        return [
            'Content-Type' => 'application/json',
            'X-Shopify-Access-Token' => $storeDetail['access_token']
        ];
    }
    
}
