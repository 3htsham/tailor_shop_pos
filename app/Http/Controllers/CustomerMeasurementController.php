<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\SaleType;
use App\Models\CustomField;
use App\Models\CustomerMeasurement;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;

class CustomerMeasurementController extends Controller
{
    /**
     * List all measurements for a given customer.
     */
    public function index($customer_id)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('customers-edit')) {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }

        $customer = Customer::findOrFail($customer_id);
        $measurements = CustomerMeasurement::with('saleType')
            ->where('customer_id', $customer_id)
            ->get();

        return view('backend.customer_measurement.index', compact('customer', 'measurements'));
    }

    /**
     * Show the create measurement form for a customer.
     */
    public function create($customer_id)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('customers-edit')) {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }

        $customer = Customer::findOrFail($customer_id);

        // Exclude sale types that already have a measurement record for this customer
        $existing_type_ids = CustomerMeasurement::where('customer_id', $customer_id)
            ->pluck('sale_type_id')
            ->toArray();

        $sale_types = SaleType::where('is_active', true)
            ->whereNotIn('id', $existing_type_ids)
            ->get();

        if ($sale_types->isEmpty()) {
            return redirect()
                ->route('customer.measurements.index', $customer_id)
                ->with('message', 'All available Sale Types already have measurements for this customer.');
        }

        return view('backend.customer_measurement.create', compact('customer', 'sale_types'));
    }

    /**
     * Store a new measurement record.
     */
    public function store(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('customers-edit')) {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }

        $request->validate([
            'customer_id'  => 'required|exists:customers,id',
            'sale_type_id' => 'required|exists:sale_types,id',
        ]);

        $customer_id  = $request->customer_id;
        $sale_type_id = $request->sale_type_id;

        // Build the measurements array from posted field values
        $custom_fields = CustomField::where('sale_type_id', $sale_type_id)->get();
        $measurements  = [];
        foreach ($custom_fields as $field) {
            $key = str_replace(' ', '_', strtolower($field->name));
            $measurements[$key] = $request->input($key, '');
        }

        // Upsert: create or update if somehow exists
        CustomerMeasurement::updateOrCreate(
            ['customer_id' => $customer_id, 'sale_type_id' => $sale_type_id],
            [
                'measurements' => $measurements,
                'notes'        => $request->notes,
            ]
        );

        return redirect()
            ->route('customer.measurements.index', $customer_id)
            ->with('message', 'Measurements saved successfully.');
    }

    /**
     * Show the edit form for an existing measurement record.
     */
    public function edit($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('customers-edit')) {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }

        $measurement  = CustomerMeasurement::with('customer', 'saleType')->findOrFail($id);
        $customer     = $measurement->customer;
        $sale_type    = $measurement->saleType;
        $custom_fields = CustomField::where('sale_type_id', $sale_type->id)->get();

        return view('backend.customer_measurement.edit', compact('measurement', 'customer', 'sale_type', 'custom_fields'));
    }

    /**
     * Update an existing measurement record.
     */
    public function update(Request $request, $id)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('customers-edit')) {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }

        $measurement   = CustomerMeasurement::findOrFail($id);
        $custom_fields = CustomField::where('sale_type_id', $measurement->sale_type_id)->get();

        $measurements = [];
        foreach ($custom_fields as $field) {
            $key = str_replace(' ', '_', strtolower($field->name));
            $measurements[$key] = $request->input($key, '');
        }

        $measurement->update([
            'measurements' => $measurements,
            'notes'        => $request->notes,
        ]);

        return redirect()
            ->route('customer.measurements.index', $measurement->customer_id)
            ->with('message', 'Measurements updated successfully.');
    }

    /**
     * Delete a measurement record.
     */
    public function destroy($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('customers-edit')) {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }

        $measurement = CustomerMeasurement::findOrFail($id);
        $customer_id = $measurement->customer_id;
        $measurement->delete();

        return redirect()
            ->route('customer.measurements.index', $customer_id)
            ->with('message', 'Measurements deleted successfully.');
    }

    /**
     * API: Return measurements JSON for a customer + sale type combination.
     * Used by the Create Sale AJAX check.
     */
    public function getMeasurements($customer_id, $sale_type_id)
    {
        $measurement = CustomerMeasurement::with('saleType')
            ->where('customer_id', $customer_id)
            ->where('sale_type_id', $sale_type_id)
            ->first();

        if (!$measurement) {
            return response()->json([
                'exists'       => false,
                'measurements' => [],
                'notes'        => null,
            ]);
        }

        // Attach field labels for display in the sale form
        $custom_fields = CustomField::where('sale_type_id', $sale_type_id)->get();
        $display       = [];
        foreach ($custom_fields as $field) {
            $key = str_replace(' ', '_', strtolower($field->name));
            $display[] = [
                'label' => $field->name,
                'key'   => $key,
                'value' => $measurement->measurements[$key] ?? '',
            ];
        }

        return response()->json([
            'exists'            => true,
            'measurements'      => $display,
            'notes'             => $measurement->notes,
            'measurement_unit'  => $measurement->saleType->measurement_unit ?? '',
            'measurement_id'    => $measurement->id,
            'customer_id'       => $customer_id,
            'sale_type_id'      => $sale_type_id,
        ]);
    }
}
