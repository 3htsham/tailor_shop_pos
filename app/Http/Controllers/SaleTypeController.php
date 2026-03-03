<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SaleType;
use App\Models\Sale;
use App\Models\CustomField;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class SaleTypeController extends Controller
{
    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('sale-types-index')) {
            $sale_types = SaleType::all();
            return view('backend.sale_type.index', compact('sale_types'));
        }
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function create()
    {
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('sale-types-add')) {
            return view('backend.sale_type.create');
        }
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function store(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        if(!$role->hasPermissionTo('sale-types-add'))
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');

        $data = $request->all();

        $sale_type = SaleType::create([
            'name' => $data['name'],
            'measurement_unit' => $data['measurement_unit'],
            'is_active' => isset($data['is_active']) ? 1 : 0,
        ]);

        // Create custom fields for this sale type
        if(isset($data['field_names']) && is_array($data['field_names'])) {
            foreach($data['field_names'] as $field_name) {
                $field_name = trim($field_name);
                if(empty($field_name)) continue;

                $column_name = str_replace(" ", "_", strtolower($field_name));

                // Add column to sales table
                $sqlStatement = "ALTER TABLE sales ADD `".$column_name."` varchar(255) NULL";
                try {
                    DB::statement($sqlStatement);
                } catch(\Exception $e) {
                    // Column may already exist, skip
                }

                // Create custom field record
                CustomField::create([
                    'belongs_to' => 'sale',
                    'name' => $field_name,
                    'type' => 'text',
                    'grid_value' => 4,
                    'is_table' => false,
                    'is_invoice' => false,
                    'is_required' => true,
                    'is_admin' => false,
                    'is_disable' => false,
                    'sale_type_id' => $sale_type->id,
                ]);
            }
        }

        return redirect('sale-types')->with('message', 'Sale Type created successfully');
    }

    public function edit($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('sale-types-edit')) {
            $sale_type = SaleType::find($id);
            $custom_fields = CustomField::where('sale_type_id', $id)->get();
            return view('backend.sale_type.edit', compact('sale_type', 'custom_fields'));
        }
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function update(Request $request, $id)
    {
        $role = Role::find(Auth::user()->role_id);
        if(!$role->hasPermissionTo('sale-types-edit'))
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');

        $data = $request->all();
        $sale_type = SaleType::find($id);

        $sale_type->update([
            'name' => $data['name'],
            'measurement_unit' => $data['measurement_unit'],
            'is_active' => isset($data['is_active']) ? 1 : 0,
        ]);

        // Get existing custom field IDs for this sale type
        $existing_field_ids = CustomField::where('sale_type_id', $id)->pluck('id')->toArray();
        $kept_field_ids = isset($data['existing_field_ids']) ? $data['existing_field_ids'] : [];

        // Remove custom fields that were deleted
        $fields_to_remove = array_diff($existing_field_ids, $kept_field_ids);
        foreach($fields_to_remove as $field_id) {
            $field = CustomField::find($field_id);
            if($field) {
                $column_name = str_replace(" ", "_", strtolower($field->name));
                try {
                    DB::statement("ALTER TABLE sales DROP COLUMN `".$column_name."`");
                } catch(\Exception $e) {
                    // Column may not exist, skip
                }
                $field->delete();
            }
        }

        // Add new custom fields
        if(isset($data['field_names']) && is_array($data['field_names'])) {
            foreach($data['field_names'] as $field_name) {
                $field_name = trim($field_name);
                if(empty($field_name)) continue;

                $column_name = str_replace(" ", "_", strtolower($field_name));

                // Add column to sales table
                $sqlStatement = "ALTER TABLE sales ADD `".$column_name."` varchar(255) NULL";
                try {
                    DB::statement($sqlStatement);
                } catch(\Exception $e) {
                    // Column may already exist, skip
                }

                CustomField::create([
                    'belongs_to' => 'sale',
                    'name' => $field_name,
                    'type' => 'text',
                    'grid_value' => 4,
                    'is_table' => false,
                    'is_invoice' => false,
                    'is_required' => true,
                    'is_admin' => false,
                    'is_disable' => false,
                    'sale_type_id' => $sale_type->id,
                ]);
            }
        }

        return redirect('sale-types')->with('message', 'Sale Type updated successfully');
    }

    public function destroy($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if(!$role->hasPermissionTo('sale-types-delete'))
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');

        // Check if this sale type is being used in any sale
        $sales_count = Sale::where('garment_sale_type_id', $id)->count();
        if($sales_count > 0) {
            return redirect('sale-types')->with('not_permitted', 'This Sale Type is currently used in ' . $sales_count . ' sale(s) and cannot be deleted. Please remove it from all sales first.');
        }

        $sale_type = SaleType::find($id);
        $custom_fields = CustomField::where('sale_type_id', $id)->get();

        // Drop columns from sales table
        foreach($custom_fields as $field) {
            $column_name = str_replace(" ", "_", strtolower($field->name));
            try {
                DB::statement("ALTER TABLE sales DROP COLUMN `".$column_name."`");
            } catch(\Exception $e) {
                // Column may not exist, skip
            }
            $field->delete();
        }

        $sale_type->delete();
        return redirect('sale-types')->with('message', 'Sale Type deleted successfully');
    }

    // API endpoint: return custom fields for a sale type (used by AJAX in sale form)
    public function getCustomFields($id)
    {
        $custom_fields = CustomField::where('sale_type_id', $id)->get();
        $sale_type = SaleType::find($id);
        return response()->json([
            'custom_fields' => $custom_fields,
            'measurement_unit' => $sale_type ? $sale_type->measurement_unit : '',
        ]);
    }
}
