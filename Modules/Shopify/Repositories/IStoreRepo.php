<?php
    namespace Modules\Shopify\Repositories;

    Interface IStoreRepo{
        public function getStoreName($store);
        public function getFirst();
    }