<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SlackUser extends Model
{
    protected $fillable = [
        'id',
        'name',
        'email',
        'title',
        'slack_id',
        'real_name',
    ];
}
