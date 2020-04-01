<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AppUser extends Model
{
    protected $table = "users";

    protected $fillable = ['firebase_id', 'name', 'mobile','lat','lng'];

}
