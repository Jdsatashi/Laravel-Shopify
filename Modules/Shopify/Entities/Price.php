<?php

namespace Modules\Shopify\Entities;

use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Price extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'price',
        'compare_price',
        'discount_type',
        'discount_value',
    ];
}
