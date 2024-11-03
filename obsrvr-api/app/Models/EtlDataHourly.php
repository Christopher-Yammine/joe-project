<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EtlDataHourly extends Model
{
    protected $table = 'etl_data_hourly';
    use HasFactory;
      public function metric()
    {
        return $this->belongsTo(Metric::class);
    }
}