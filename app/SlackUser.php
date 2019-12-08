<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SlackUser extends Model
{
    protected $table = 'slack_users';

    protected $fillable = [
        'id',
        'name',
        'email',
        'title',
        'slack_id',
        'real_name',
        'private_channel_id',
    ];
}
