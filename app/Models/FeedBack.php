<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class FeedBack extends Eloquent
{
    protected $table = 'feedback';
    protected $connection = 'mongodb';

    protected $fillable = [
        'data'
    ];
}
