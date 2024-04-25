<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftStaff extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shift_job()
    {
        return $this->belongsTo(ShiftJob::class);
    }
}
