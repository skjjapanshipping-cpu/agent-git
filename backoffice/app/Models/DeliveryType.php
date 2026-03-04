<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DeliveryType
 *
 * @property $id
 * @property $name
 *
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class DeliveryType extends Model
{

    static $rules = [
    ];

    protected $perPage = 20;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];

    protected static $nameCache = null;

    public static function getNameById($id){
        if (static::$nameCache === null) {
            static::$nameCache = static::pluck('name', 'id')->toArray();
        }
        return static::$nameCache[$id] ?? '-';
    }

}
