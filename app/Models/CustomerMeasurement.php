<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerMeasurement extends Model
{
    protected $fillable = [
        'customer_id',
        'sale_type_id',
        'measurements',
        'notes',
    ];

    protected $casts = [
        'measurements' => 'array',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function saleType()
    {
        return $this->belongsTo(SaleType::class);
    }
}
