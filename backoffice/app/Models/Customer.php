<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Customer
 *
 * @property $id
 * @property $addr
 * @property $province
 * @property $distrinct
 * @property $subdistrinct
 * @property $postcode
 * @property $name
 * @property $email
 * @property $mobile
 * @property $avatar
 * @property $created_at
 * @property $updated_at
 * @property $customerno
 *
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Customer extends Model
{

    static $rules = [
		'name' => 'required',
    ];


    protected $perPage = 20;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['addr','province','distrinct','subdistrinct','postcode','name','email','mobile','avatar','customerno'];



}
