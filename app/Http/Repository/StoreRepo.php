<?php
    namespace App\Http\Repository;

use App\Models\Store;

class StoreRepo implements IStoreRepo{
    public function getStoreName($store){
        return Store::where('name', $store)->first();
    }
    public function getFirst()
    {
        return Store::first();
    }
}