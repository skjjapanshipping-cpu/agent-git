<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddressBook extends Model
{
    protected $table = 'address_books';

    protected $fillable = [
        'user_id',
        'label',
        'fullname',
        'mobile',
        'address',
        'subdistrict',
        'district',
        'province',
        'postcode',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }
}
