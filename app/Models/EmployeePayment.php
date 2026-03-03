<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'amount', 'paying_method', 'note', 'user_id', 'reference_no'
    ];

    public function employee()
    {
        return $this->belongsTo('App\Models\Employee');
    }
}
