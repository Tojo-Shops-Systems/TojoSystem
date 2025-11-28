<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class ProductCloud extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'products_cloud';

    protected $fillable = [
        'product_code',
        'product_name',
        'product_url_image',
        'product_description',
        'product_stock',
        'product_price',
        'category_id',
        'supplier_id',
        'branch_id',
    ];
}
