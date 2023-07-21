<?php
    namespace Modules\Shopify\Repositories;

use Modules\Shopify\Entities\Store;

class StoreRepo implements IStoreRepo{
    public function getStoreName($store){
        return Store::where('name', $store)->first();
    }
    public function getFirst()
    {
        return Store::first();
    }
}