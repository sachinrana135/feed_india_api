<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DonorItem extends Model
{
    protected $table = "donor_items";

    protected $fillable = ['user_id','donate_items','status'];

}
