<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'group_user');
    }

    // public function tables()
    // {
    //     return $this->hasMany('App\Models\Table');
    // }

    public function tables()
    {
        return $this->hasMany(Table::class, 'owner_id')->ofType('group');
    }
}
