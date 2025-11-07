<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';

    protected $collection = 'tickets';

    protected $fillable = [
        'ticketID',
        'items',
        'totalAmount',
        'cashier',
        'customer',
        'status',
        'date',
    ];

    protected $casts = [
        'items' => 'array',
        'cashier' => 'array',
        'customer' => 'array',
        'totalAmount' => 'float',
        'status' => 'string',
        'date' => 'datetime',
    ];
}
