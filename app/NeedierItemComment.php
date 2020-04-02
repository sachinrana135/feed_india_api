<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NeedierItemComment extends Model
{
    protected $table = "needier_items_comments";

    protected $fillable = ['needier_items_id','comments'];

}
