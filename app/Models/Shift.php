<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function jobs()
    {
        return $this->hasMany(ShiftJob::class);
    }

    public function staff()
    {
        return $this->hasMany(ShiftStaff::class);
    }
}
