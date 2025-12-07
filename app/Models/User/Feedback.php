<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';

    protected $collection = 'feedback';

    protected $fillable = [
        'feedbackID',
        'sensorType',
        'sensorNumber',
        'data',
    ];

    protected $casts = [
        'sensorType' => 'string',
        'sensorNumber' => 'integer',
        'data' => 'string',
    ];
}
