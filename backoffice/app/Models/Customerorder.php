<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Customerorder
 *
 * @property $id
 * @property $order_date
 * @property $customerno
 * @property $category
 * @property $image_link
 * @property $quantity
 * @property $product_cost_yen
 * @property $rateprice
 * @property $product_cost_baht
 * @property $status
 * @property $tracking_number
 * @property $cutoff_date
 * @property $shipping_status
 * @property $note
 * @property $link
 * @property $itemno
 * @property $itemno2
 * @property $boss_id
 * @property $supplier_status_id
 * @property $note_admin
 * @property $img_deleted_at
 * @property $created_at
 * @property $updated_at
 *
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Customerorder extends Model
{

    static $rules = [
		'order_date' => 'required',
		'customerno' => 'required',
		'category' => 'required',
		'link' => 'required',
		'image_link' => ['nullable','mimes:jpeg,bmp,png,PNG,JPG,jpg,JPEG','max:9000'],
		'itemno2' => 'nullable|string|max:255',
		'supplier_status_id' => 'required',
		'boss_id' => 'required',
//		'quantity' => 'required',
//		'product_cost_yen' => 'required',
//		'rate' => 'required',
//		'product_cost_baht' => 'required',
//		'status' => 'required',
//		'cutoff_date' => 'required',
//		'shipping_status' => 'required',
    ];

    protected $perPage = 20;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['order_date','customerno','category','image_link','link','quantity','product_cost_yen','product_price_yen','shipping_jp_yen','rateprice','product_cost_baht','status','tracking_number','cutoff_date','shipping_status','note','itemno','itemno2','boss_id','supplier_status_id','note_admin','img_deleted_at'];

    protected $casts = [
        'order_date' => 'datetime',
        'cutoff_date' => 'datetime',
        'img_deleted_at' => 'datetime'
    ];

    public static function getLastItemNoByCusNo($customerno){
        $order = self::where('customerno',$customerno)->latest()->first();
        return $order ? $order->itemno : null;
    }

    public static function newItemno($customerno){
        // ใช้ CAST เป็น UNSIGNED เพื่อเปรียบเทียบตัวเลขจริง (ไม่ใช่ string comparison)
        $maxItemNo = self::where('customerno', $customerno)
            ->selectRaw('MAX(CAST(itemno AS UNSIGNED)) as max_itemno')
            ->value('max_itemno');

        // เพิ่มค่า 1 ให้กับ maxItemNo และใช้ str_pad เพื่อให้มีความยาว 4 หลัก
        return str_pad(($maxItemNo ?? 0) + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get the boss that owns the customerorder.
     */
    public function boss()
    {
        return $this->belongsTo(Boss::class, 'boss_id');
    }

    /**
     * Get the supplier status that owns the customerorder.
     */
    public function supplierStatus()
    {
        return $this->belongsTo(SupplierStatus::class, 'supplier_status_id');
    }

}
