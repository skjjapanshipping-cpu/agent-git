<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MyAuthProvider extends Model
{

    protected $primaryKey='myauthproviderid';
    protected $fillable = ['userid','provider', 'providerid'];
}
