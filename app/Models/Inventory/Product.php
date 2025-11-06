<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'stock',
        'price',
        'supplier_id',
    ];

    protected $table = 'products';

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
    ];
}
