<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HourlyDemographicView extends Model
{
    use HasFactory;
    protected $table = 'hourly_demographics_view';

    public $timestamps = false;

    protected $fillable = ['demographic_id', 'gender', 'age_group', 'sentiment', 'date', 'value'];
}
