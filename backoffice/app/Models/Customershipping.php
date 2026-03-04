<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class Customershipping
 *
 * @property $id
 * @property $ship_date
 * @property $customerno
 * @property $track_no
 * @property $cod
 * @property $cod_rate
 * @property $weight
 * @property $unit_price
 * @property $import_cost
 * @property $box_image
 * @property $product_image
 * @property $box_no
 * @property $warehouse
 * @property $status
 * @property $delivery_address
 * @property $note
 * @property $created_at
 * @property $updated_at
 *
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Customershipping extends Model
{

    static $rules = [
		'ship_date' => 'required',
		'customerno' => 'required',
		'track_no' => 'required',
    ];

    protected $perPage = 20;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    const METHOD_SEA = 1;
    const METHOD_AIR = 2;

    protected $fillable = ['ship_date','customerno','track_no','cod','cod_rate','weight','unit_price','import_cost','box_image','product_image','box_no','warehouse','status','shipping_method', 'delivery_mobile',
        'delivery_address', 'delivery_subdistrict', 'delivery_district', 'delivery_province',
        'delivery_postcode','delivery_type_id','delivery_fullname','note', 'width', 'length', 'height','etd','note_admin','excel_status','iswholeprice','pay_status',
        'itemno'];

    protected $casts = [
        'ship_date' => 'datetime:d/m/Y',
        'etd' => 'datetime:d/m/Y',
        'scanned_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'chat_notified_at' => 'datetime',
    ];

    protected static $statusCache = null;

    public static function getShippingStatusNameById($id){
        if (static::$statusCache === null) {
            static::$statusCache = DB::table('shipping_statuses')->pluck('name', 'id')->toArray();
        }
        $name = static::$statusCache[$id] ?? '-';
        return (object)['name' => $name];
    }

    public static function getShippingMethodLabel($method)
    {
        $labels = [
            self::METHOD_SEA => 'ทางเรือ',
            self::METHOD_AIR => 'ทางเครื่องบิน',
        ];
        return $labels[$method] ?? 'ทางเรือ';
    }

    public static function getShippingMethodIcon($method)
    {
        return $method == self::METHOD_AIR ? 'fa-plane' : 'fa-ship';
    }

    public static function getEtdLabel($method)
    {
        return $method == self::METHOD_AIR ? 'รอบเที่ยวบิน' : 'รอบปิดตู้';
    }

    public static function getDefaultUnitPrice($method)
    {
        return $method == self::METHOD_AIR ? 339 : 150;
    }


}
