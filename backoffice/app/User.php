<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable;
    use HasRoles;

    /**
     * สร้าง customerno ใหม่ในรูปแบบ "anw-NNNN" แบบ atomic
     * - ใช้ transaction + lock เพื่อป้องกัน race condition
     * - คำนวณจากเลขสูงสุดปัจจุบัน (ไม่ใช่ ID ล่าสุด)
     *
     * @param int $startFrom เลขเริ่มต้นถ้าไม่มีลูกค้าในระบบเลย
     */
    public static function generateNextCustomerno(int $startFrom = 500): string
    {
        return DB::transaction(function () use ($startFrom) {
            $row = DB::selectOne("
                SELECT MAX(CAST(SUBSTRING(customerno, 5) AS UNSIGNED)) AS maxnum
                FROM users
                WHERE customerno IS NOT NULL
                  AND customerno LIKE 'anw-%'
                FOR UPDATE
            ");
            $current = (int) ($row->maxnum ?? 0);
            $next = max($current + 1, $startFrom);
            return 'anw-' . $next;
        });
    }

    /**
     * Customerno แสดงผลเป็นตัวพิมพ์ใหญ่ (ANW-1234)
     */
    public function getCustomercodeAttribute(): ?string
    {
        return $this->customerno ? strtoupper($this->customerno) : null;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password','mobile','avatar','ward_no','addr', 'province','distrinct','subdistrinct','postcode','customerno','cus_unit_price','cus_unit_price_air','delivery_type_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function gcCaptures(){
        return $this->hasMany('App\Capturegc');
    }

    public static function userCount(){
        return User::count();
    }

    /**
     * ส่งอีเมลรีเซ็ตรหัสผ่านด้วย template SKJ Branding แทน Laravel default
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\CustomerResetPasswordNotification($token));
    }
}
