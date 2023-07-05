<?php

    namespace App\Http\Repository;

    interface IPriceRepo{
        public function getAll();
        public function create($data);
        public function getById($id);
        public function update($data, $id);
    }