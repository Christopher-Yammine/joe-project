<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Demographic extends Model
{
    use HasFactory;

    public function gender()
    {
        return $this->belongsTo(Gender::class);
    }
    public function ageGroup()
    {
        return $this->belongsTo(AgeGroup::class);
    }
}
