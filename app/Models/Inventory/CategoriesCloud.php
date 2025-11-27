<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class CategoriesCloud extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'categories_cloud';

    protected $fillable = [
        'category_id',
        'category_name',
    ];
}
