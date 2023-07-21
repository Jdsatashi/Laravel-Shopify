<?php
namespace Modules\Shopify\Traits;

Trait CalcDiscountTrait{
    protected function DiscountCal($option, $value, $price): string
    {
        $discount_price = 1;
        if($option == 'percent'){
            $discount_price = $price - ($price * $value / 100);
        }
        elseif ($option == 'fixed'){
            $discount_price = $price - $value;
        }
        $discount_price = number_format($discount_price, 2, '.', '');
        if ($discount_price <= 0) {
            $discount_price = '1';
        }
        return $discount_price;
    }
}