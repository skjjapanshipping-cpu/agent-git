<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SupplierStatus
 *
 * @property $id
 * @property $name
 * @property $description
 * @property $created_at
 * @property $updated_at
 *
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class SupplierStatus extends Model
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
    protected $fillable = ['name','description'];

    protected static $nameCache = null;

    public static function getNameById($id){
        if (static::$nameCache === null) {
            static::$nameCache = static::pluck('name', 'id')->toArray();
        }
        return static::$nameCache[$id] ?? null;
    }

}

