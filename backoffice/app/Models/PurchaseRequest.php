<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model
{
    protected $perPage = 20;

    protected $fillable = [
        'request_no', 'customerno', 'product_url', 'site', 'product_title',
        'product_image', 'quantity', 'estimated_price_yen', 'actual_price_yen',
        'shipping_jp_yen', 'rate', 'purchase_ref', 'status', 'admin_id',
        'boss_id', 'customer_note', 'admin_note', 'customerorder_id',
    ];

    protected $casts = [
        'estimated_price_yen' => 'decimal:2',
        'actual_price_yen' => 'decimal:2',
        'shipping_jp_yen' => 'decimal:2',
        'rate' => 'decimal:4',
        'status' => 'integer',
    ];

    // Status constants
    const STATUS_PENDING     = 0;
    const STATUS_APPROVED    = 1;
    const STATUS_PURCHASING  = 2;
    const STATUS_PURCHASED   = 3;
    const STATUS_IN_WAREHOUSE = 4;
    const STATUS_SHIPPED     = 5;
    const STATUS_CANCELLED   = 6;

    public static $statusLabels = [
        0 => 'รอดำเนินการ',
        1 => 'อนุมัติแล้ว',
        2 => 'กำลังสั่งซื้อ',
        3 => 'สั่งซื้อแล้ว',
        4 => 'ถึงโกดังแล้ว',
        5 => 'ส่งแล้ว',
        6 => 'ยกเลิก',
    ];

    public static $statusColors = [
        0 => 'warning',
        1 => 'info',
        2 => 'primary',
        3 => 'success',
        4 => 'secondary',
        5 => 'dark',
        6 => 'danger',
    ];

    public function getStatusLabelAttribute()
    {
        return self::$statusLabels[$this->status] ?? 'ไม่ทราบ';
    }

    public function getStatusColorAttribute()
    {
        return self::$statusColors[$this->status] ?? 'secondary';
    }

    public function boss()
    {
        return $this->belongsTo(Boss::class, 'boss_id');
    }

    public function admin()
    {
        return $this->belongsTo(\App\User::class, 'admin_id');
    }

    public function customerorder()
    {
        return $this->belongsTo(Customerorder::class, 'customerorder_id');
    }

    /**
     * Detect site from URL
     */
    public static function detectSite($url)
    {
        if (strpos($url, 'mercari.com') !== false) return 'Mercari';
        if (strpos($url, 'yahoo.co.jp') !== false) return 'Yahoo Auctions';
        if (strpos($url, 'rakuten.co.jp') !== false) return 'Rakuten';
        if (strpos($url, 'amazon.co.jp') !== false) return 'Amazon JP';
        if (strpos($url, 'paypayfleamarket') !== false) return 'PayPay';
        return 'Other';
    }

    /**
     * Generate next request number
     */
    public static function generateRequestNo()
    {
        $prefix = 'PR-' . date('Ymd') . '-';
        $last = self::where('request_no', 'like', $prefix . '%')
            ->orderBy('request_no', 'desc')
            ->first();

        if ($last) {
            $num = (int) substr($last->request_no, -4) + 1;
        } else {
            $num = 1;
        }

        return $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);
    }
}
