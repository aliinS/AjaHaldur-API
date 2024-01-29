<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function content()
    {
        return $this->hasMany(TableContent::class);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}
