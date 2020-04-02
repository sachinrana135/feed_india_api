<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NeedierItem extends Model
{
    protected $table = "needier_items";

    protected $fillable = ['user_id','items_need'];

}
