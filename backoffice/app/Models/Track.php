<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Track
 *
 * @property $id
 * @property $customer_name
 * @property $track_no
 * @property $cod
 * @property $weight
 * @property $source_date
 * @property $ship_date
 * @property $destination_date
 * @property $note
 * @property $created_at
 * @property $updated_at
 *
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Track extends Model
{

    static $rules = [
//		'customer_name' => 'required',
//		'track_no' => 'required',
//		'weight' => 'required'

    ];

    protected $perPage = 20;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['customer_name','track_no','cod','weight','source_date','ship_date','destination_date','note','status'];
    protected $casts = [
        'source_date' => 'datetime:d/m/Y'
        ,'ship_date' => 'datetime:d/m/Y'
        ,'destination_date' => 'datetime:d/m/Y'
    ];


}
