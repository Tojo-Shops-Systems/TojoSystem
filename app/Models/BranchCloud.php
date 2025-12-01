<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class BranchCloud extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';

    protected $collection = 'branch_clouds';

    protected $fillable = [
        'branch_id',      // El ID numérico único (ej: 10) que usará la Pi
        'branchName',     // El nombre visual (ej: "Sucursal Centro")
        'address',        // La dirección
        'activation_key', // La llave secreta (ej: "ABC-123")
        'is_active',      // (Opcional) Para saber si ya fue reclamada
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'branch_id' => 'integer',
        'is_active' => 'boolean',
    ];
}
