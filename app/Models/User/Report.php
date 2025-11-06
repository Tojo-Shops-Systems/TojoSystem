<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';

    protected $collection = 'reports';

    protected $fillable = [
        'reportID',
        'description',
        'customer',
        'cashier',
        'branch',
        'date',
    ];

    protected $casts = [
        'description' => 'string',
        'cashier' => 'array',
        'customer' => 'array',
        'branch' => 'string',
        'date' => 'datetime',
    ];
}
