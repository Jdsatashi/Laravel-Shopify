<?php
    namespace App\Http\Repository;

    Interface IStoreRepo{
        public function getStoreName($store);
        public function getFirst();
    }