<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Copyright extends Model
{

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function order()
    {
        return $this->morphOne('App\Order', 'orderable');
    }

    public function authors()
    {
        return $this->belongsToMany('App\Author');
    }

    public function files()
    {
        return $this->morphToMany('App\File', 'fileable');
    }

}
