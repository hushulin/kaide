<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meter extends Model {

    protected $guarded = ['id'];



    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function xiaofeis()
    {
        return $this->hasMany('App\Models\Xiaofei');
    }
}
