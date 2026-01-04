<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeBalanceAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'amount',
        'note',
        'user_id'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
