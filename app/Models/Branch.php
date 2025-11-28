<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'branchName',
        'address',
    ];

    protected $table = 'branches';

    public $incrementing = false;
}
