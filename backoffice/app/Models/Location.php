<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Location
 *
 * @property $id
 * @property $name
 * @property $description
 * @property $hashcode
 * @property $created_at
 * @property $updated_at
 * @property $app_id
 *
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Location extends Model
{
    use HasUuid;
    static $rules = [
		'name' => 'required',
		//'hashcode' => 'required',
    ];

    protected $perPage = 20;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['name','description','hashcode','app_id'];



}
