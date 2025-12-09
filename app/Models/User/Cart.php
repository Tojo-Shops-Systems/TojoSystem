<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';

    protected $collection = 'carts';

    protected $fillable = [
        'cartID',
        'items',
        'customer',
        'date',
    ];

    protected $casts = [
        'customer' => 'array',
        'date' => 'datetime',
    ];
}
