<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleType extends Model
{
    protected $fillable = ['name', 'measurement_unit', 'is_active'];

    public function customFields()
    {
        return $this->hasMany(CustomField::class, 'sale_type_id');
    }
}
