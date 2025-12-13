<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Task;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class TaskController extends Controller
{
    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('tasks-index')) {
            $task_all = Task::where('is_active', true)->withCount('taskAssignments')->get();
            $canAddTask = $role->hasPermissionTo('tasks-add');
            $canEditTask = $role->hasPermissionTo('tasks-edit');
            $canDeleteTask = $role->hasPermissionTo('tasks-delete');
            return view('backend.task.index', compact('task_all', 'canAddTask', 'canEditTask', 'canDeleteTask'));
        }
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $data = $request->all();
        Task::create($data);
        cache()->forget('task');
        return redirect()->back()->with('message', 'Task created successfully');
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        Task::find($data['task_id'])->update($data);
        cache()->forget('task');
        return redirect()->back()->with('message', 'Task updated successfully');
    }

    public function destroy($id)
    {
        if(!env('USER_VERIFIED'))
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');

        Task::find($id)->update(['is_active' => false]);
        cache()->forget('Task');
        return redirect()->back()->with('message', 'Task deleted successfully');
    }
}
