<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Person extends Model
{
    use HasFactory;

    protected $table = 'persons';

    protected $fillable = [
        'firstName',
        'lastName',
        'CURP',
        'phoneNumber',
    ];

    public function setCURPAttribute($value)
    {
        $this->attributes['CURP'] = Hash::make($value);
    }

    // app/Models/User/Person.php
    public function user()
    {
        return $this->hasOne(\App\Models\User\User::class, 'person_id');
    }
}
