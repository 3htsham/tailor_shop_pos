<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id', 'product_sale_id', 'unit_item_no', 'task_id', 'price', 'employee_id', 'status'
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function productSale()
    {
        return $this->belongsTo(Product_Sale::class, 'product_sale_id');
    }
}
