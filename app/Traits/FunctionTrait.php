<?php

namespace App\Traits;

use App\Models\Store;

trait FunctionTrait {
    public function getStoreDomain($shop){
        return Store::where('myshopify_domain', $shop)->first();
    }
}