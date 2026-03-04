<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Dailyrate
 *
 * @property $id
 * @property $name
 * @property $rateprice
 * @property $cod_rate
 * @property $datetimerate
 * @property $created_at
 * @property $updated_at
 *
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Dailyrate extends Model
{

    static $rules = [
		'name' => 'required',
		'rateprice' => 'required',
		'datetimerate' => 'required',
    ];

    protected $perPage = 20;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['name','rateprice','cod_rate','datetimerate'];

    public static function curRatePerBath(){

        return  self::latest()->value('rateprice');
    }
    
    public static function getCodRate(){
        // ดึง COD rate ล่าสุด (default 0.25 ถ้าไม่มีค่า)
        return self::latest()->value('cod_rate') ?? 0.25;
    }
    
    public static function getRatePerBathByDate($date){
        $rate = self::whereDate('datetimerate', '<=',$date)->orderBy('datetimerate', 'desc')->first();
        return $rate ? $rate->rateprice : self::curRatePerBath();
    }

}
