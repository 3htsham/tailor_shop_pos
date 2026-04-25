<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Task;
use App\Models\Employee;
use App\Models\TaskAssignment;
use App\Models\Product_Sale;
use App\Models\Product;
use Auth;

class TaskAssignmentController extends Controller
{
    /**
     * Show task management page for a sale.
     * Loads product list so the view can populate the product dropdown.
     */
    public function index($sale_id)
    {
        $sale = Sale::findOrFail($sale_id);
        $tasks = Task::where('is_active', true)->get();
        $employees = Employee::where('is_active', true)->get();

        // Load product_sales with their product names for the dropdown
        $productSales = Product_Sale::with('product')
                            ->where('sale_id', $sale_id)
                            ->get();

        return view('backend.sale.task_management', compact('sale', 'tasks', 'employees', 'productSales'));
    }

    /**
     * AJAX — return the products of a sale as JSON.
     * Used to populate the product dropdown dynamically (if needed client-side).
     */
    public function getSaleProducts($sale_id)
    {
        $sale = Sale::findOrFail($sale_id);

        $products = Product_Sale::with('product')
                        ->where('sale_id', $sale_id)
                        ->get()
                        ->map(function ($ps) {
                            return [
                                'product_sale_id' => $ps->id,
                                'product_name'    => $ps->product ? $ps->product->name : 'Unknown',
                                'qty'             => (int) $ps->qty,
                            ];
                        });

        return response()->json($products);
    }

    /**
     * AJAX — return task assignments for a specific product_sale_id + unit_item_no.
     * Also returns the last_assignment so the frontend can lock/unlock the form properly.
     */
    public function getUnitAssignments(Request $request, $sale_id)
    {
        $request->validate([
            'product_sale_id' => 'required|integer|exists:product_sales,id',
            'unit_item_no'    => 'required|integer|min:1',
        ]);

        $product_sale_id = $request->product_sale_id;
        $unit_item_no    = $request->unit_item_no;

        $assignments = TaskAssignment::where('sale_id', $sale_id)
                            ->where('product_sale_id', $product_sale_id)
                            ->where('unit_item_no', $unit_item_no)
                            ->with(['task', 'employee'])
                            ->orderBy('created_at', 'desc')
                            ->get();

        $last_assignment = $assignments->first();

        // Format for JSON
        $assignmentsData = $assignments->map(function ($a) {
            return [
                'task_name'   => $a->task ? $a->task->task_name : 'N/A',
                'price'       => number_format($a->price, 2),
                'employee'    => $a->employee ? $a->employee->name : 'N/A',
                'status'      => $a->status,
                'created_at'  => $a->created_at->format('Y-m-d H:i'),
            ];
        });

        $lastData = null;
        if ($last_assignment) {
            $lastData = [
                'task_id'  => $last_assignment->task_id,
                'price'    => $last_assignment->price,
                'status'   => $last_assignment->status,
                'employee_id' => $last_assignment->employee_id,
            ];
        }

        return response()->json([
            'assignments'      => $assignmentsData,
            'last_assignment'  => $lastData,
        ]);
    }

    /**
     * Store a new task assignment linked to a product unit item.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sale_id'         => 'required|exists:sales,id',
            'product_sale_id' => 'required|integer|exists:product_sales,id',
            'unit_item_no'    => 'required|integer|min:1',
            'task_id'         => 'required|exists:tasks,id',
            'employee_id'     => 'required|exists:employees,id',
            'status'          => 'required|in:Pending,In-Progress,Completed',
            'price'           => 'required|numeric',
        ]);

        // Validate unit_item_no does not exceed the product qty
        $productSale = Product_Sale::findOrFail($validated['product_sale_id']);
        if ($validated['unit_item_no'] > (int) $productSale->qty) {
            return redirect()->back()->with('not_permitted', 'Invalid unit item number.');
        }

        // Get the last assignment for THIS specific product unit
        $last_assignment = TaskAssignment::where('sale_id', $validated['sale_id'])
                                        ->where('product_sale_id', $validated['product_sale_id'])
                                        ->where('unit_item_no', $validated['unit_item_no'])
                                        ->orderBy('created_at', 'desc')
                                        ->first();

        if ($last_assignment) {
            // Case 1: Switching to a DIFFERENT task
            if ($last_assignment->task_id != $validated['task_id']) {
                if ($last_assignment->status != 'Completed') {
                    return redirect()->back()->with('not_permitted', 'Previous task must be completed before starting a new one.');
                }
            }
            // Case 2: Updating the SAME task
            else {
                // If In-Progress, price is locked
                if ($last_assignment->status == 'In-Progress') {
                    $validated['price'] = $last_assignment->price;
                }

                // Cannot revert progress
                if ($last_assignment->status == 'In-Progress' && $validated['status'] == 'Pending') {
                    return redirect()->back()->with('not_permitted', 'Cannot revert status from In-Progress to Pending.');
                }
                if ($last_assignment->status == 'Completed') {
                    if ($validated['status'] != 'Completed') {
                        return redirect()->back()->with('not_permitted', 'Cannot revert status from Completed.');
                    }
                }
            }
        }

        TaskAssignment::create($validated);

        if ($validated['status'] == 'Completed') {
            $employee = Employee::find($validated['employee_id']);
            if (!$employee->is_payroll) {
                $employee->increment('balance', $validated['price']);
            }
        }

        return redirect()->back()->with('message', 'Task assigned successfully');
    }
}
