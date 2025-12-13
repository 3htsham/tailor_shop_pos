<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Task;
use App\Models\Employee;
use App\Models\TaskAssignment;
use Auth;

class TaskAssignmentController extends Controller
{
    public function index($sale_id)
    {
        $sale = Sale::findOrFail($sale_id);
        $tasks = Task::where('is_active', true)->get();
        $employees = Employee::where('is_active', true)->get();
        $previous_assignments = TaskAssignment::where('sale_id', $sale_id)
                                ->with(['task', 'employee'])
                                ->orderBy('created_at', 'desc')
                                ->get();
        
        $last_assignment = $previous_assignments->first();

        return view('backend.sale.task_management', compact('sale', 'tasks', 'employees', 'previous_assignments', 'last_assignment'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'task_id' => 'required|exists:tasks,id',
            'employee_id' => 'required|exists:employees,id',
            'status' => 'required|in:Pending,In-Progress,Completed',
            'status_date' => 'nullable|date',
        ]);

        if (empty($validated['status_date'])) {
            $validated['status_date'] = now();
        }

        TaskAssignment::create($validated);

        return redirect()->back()->with('message', 'Task assigned successfully');
    }
}
