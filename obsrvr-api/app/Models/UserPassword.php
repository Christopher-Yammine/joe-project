<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPassword extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'hashed_password',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function passwords()
    {
        return $this->hasMany(UserPassword::class, 'user_id');
    }

}
