<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = "groups";

    protected $fillable = ['code', 'name', 'reg_no', 'mobile','address','marker_id'];

}
