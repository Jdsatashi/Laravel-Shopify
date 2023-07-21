<?php

namespace Modules\Shopify\Traits;

use Modules\Shopify\Entities\Store;

trait FunctionTrait {
    public function getStoreDomain($shop){
        return Store::where('myshopify_domain', $shop)->first();
    }
}