<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Joke extends Model
{
    //
    //fields that data can be added to
    protected $fillable = ['body', 'user_id'];

    //A joke belongs to a specific user
    public function user(){
      return $this->belongsTo('App\User');
    }
}
