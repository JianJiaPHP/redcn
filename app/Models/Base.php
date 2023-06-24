<?php

namespace App\Models;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Base extends Model
{
    public function getCreatedAtAttribute($value): string
    {
        return Carbon::parse($value)->toDateTimeString();
    }
    public function getUpdatedAtAttribute($value): string
    {
        return Carbon::parse($value)->toDateTimeString();
    }
}
