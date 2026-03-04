<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tambon extends Model
{
    protected static $provincesCache = null;
    protected static $amphoesCache = null;
    protected static $tambonsCache = null;

    public static function getCachedProvinces()
    {
        if (static::$provincesCache === null) {
            static::$provincesCache = static::select('province')->distinct()->get();
        }
        return static::$provincesCache;
    }

    public static function getCachedAmphoes()
    {
        if (static::$amphoesCache === null) {
            static::$amphoesCache = static::select('amphoe')->distinct()->get();
        }
        return static::$amphoesCache;
    }

    public static function getCachedTambons()
    {
        if (static::$tambonsCache === null) {
            static::$tambonsCache = static::select('tambon')->distinct()->get();
        }
        return static::$tambonsCache;
    }
}
