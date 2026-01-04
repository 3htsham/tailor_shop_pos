<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable =[
        "name", "image", "department_id", "email", "phone_number",
        "user_id", "staff_id", "address", "city", "country", "is_active", "balance"
    ];

    public function payroll()
    {
    	return $this->hasMany('App\Models\Payroll');
    }

    public function taskAssignments()
    {
        return $this->hasMany('App\Models\TaskAssignment');
    }

    public function balanceAdjustments()
    {
        return $this->hasMany('App\Models\EmployeeBalanceAdjustment');
    }

}
