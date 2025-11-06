<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplierName',
        'contactEmail',
        'phoneNumber',
        'address',
    ];

    protected $table = 'suppliers';
}
