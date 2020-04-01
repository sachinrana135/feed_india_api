<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GroupUser extends Model
{
    protected $table = "group_users";

    protected $fillable = ['group_id','user_id'];

}
