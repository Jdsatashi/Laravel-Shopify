<?php
    namespace App\Http\Repository;

    use App\Models\Price;

    class PriceRepo implements IPriceRepo{
        public function getAll(){
            return Price::all();
        }
        public function create($data){
            return Price::create($data);
        }
        public function getById($id){
            return Price::where('product_id', $id)->first();
        }
        public function update($data, $id){
            return Price::where('product_id', $id)->update($data);
        }
        public function delete($id){
            return Price::where('product_id', $id)->delete();
        }
    }