<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtraShippingCharge extends Model
{
    protected $table = 'extra_shipping_charges';

    protected $fillable = [
        'customerno',
        'etd_date',
        'ref_no',
        'courier',
        'recipient_name',
        'price',
        'description',
        'sequence_no',
        'created_by',
    ];

    protected $casts = [
        'etd_date'    => 'date',
        'price'       => 'decimal:2',
        'sequence_no' => 'integer',
    ];

    public function creator()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }
}
