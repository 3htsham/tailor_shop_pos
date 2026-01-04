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
            'price' => 'required|numeric',
        ]);

        // Check for previous assignment logic
        $last_assignment = TaskAssignment::where('sale_id', $validated['sale_id'])
                                        ->orderBy('created_at', 'desc')
                                        ->first();

        if ($last_assignment) {
            // Case 1: Switching to a DIFFERENT task
            if ($last_assignment->task_id != $validated['task_id']) {
                // Determine the logical previous task (latest one that isn't the one we are trying to start, essentially the same check)
                // Actually the requirement is: if there is ANY task in Pending/In-Progress, we validiation restricted changing it via frontend.
                // Backend check: 'Previous task must be completed'.
                if ($last_assignment->status != 'Completed') {
                     return redirect()->back()->with('not_permitted', 'Previous task must be completed before starting a new one.');
                }
            } 
            // Case 2: Updating the SAME task
            else {
                // Price Integrity: Price cannot change once assigned
                $validated['price'] = $last_assignment->price;

                // Sequential Status Check: Cannot revert progress
                if ($last_assignment->status == 'In-Progress' && $validated['status'] == 'Pending') {
                    return redirect()->back()->with('not_permitted', 'Cannot revert status from In-Progress to Pending.');
                }
                if ($last_assignment->status == 'Completed') {
                     // Generally shouldn't happen if UI handles it, but good to block reverting from Completed too if strict sequential.
                     // Requirement said "In-Progress Task should not be Reverted back to Pending".
                     // Implied: Completed shouldn't go back to Pending/In-Progress either?
                     // Let's assume strict forward progress.
                     if ($validated['status'] != 'Completed') {
                        return redirect()->back()->with('not_permitted', 'Cannot revert status from Completed.');
                     }
                }
            }
        }

        TaskAssignment::create($validated);

        if($validated['status'] == 'Completed') {
            Employee::where('id', $validated['employee_id'])->increment('balance', $validated['price']);
        }

        return redirect()->back()->with('message', 'Task assigned successfully');
    }
}
