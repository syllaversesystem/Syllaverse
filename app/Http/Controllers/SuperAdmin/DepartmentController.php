<?php
// File: app/Http/Controllers/SuperAdmin/DepartmentController.php
// Description: Controller for managing departments (Super Admin)

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\User;

class DepartmentController extends Controller
{
    // Show all departments
 public function index()
{
   $departments = Department::with(['admin', 'programs'])->latest()->get();


    return view('superadmin.departments.index', compact('departments'));
}


    // Store a new department
public function store(Request $request)
{
    $exists = Department::where('name', $request->name)
                        ->orWhere('code', $request->code)
                        ->exists();

    if ($exists) {
        return back()->with('info', 'Department already exists.');
    }

    // Proceed to store if not existing
    Department::create([
        'name' => $request->name,
        'code' => $request->code,
    ]);

    return back()->with('success', 'Department created successfully.');
}

    // Update a department
    public function update(Request $request, $id)
    {
        $department = Department::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:25|unique:departments,code,' . $id,
        ]);

        $department->update([
            'name' => $request->name,
            'code' => $request->code,
        ]);

        return redirect()->back()->with('success', 'Department updated successfully!');
    }

    // Delete a department
    public function destroy($id)
    {
        $department = Department::findOrFail($id);
        $department->delete();

        return redirect()->back()->with('success', 'Department deleted successfully!');
    }

    // Assign an admin to a department
    public function assignAdmin(Request $request, $userId)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id'
        ]);

        $user = User::where('role', 'admin')->where('status', 'active')->findOrFail($userId);
        $user->department_id = $request->department_id;
        $user->save();

        return redirect()->back()->with('success', 'Admin assigned to department successfully!');
    }


    
}
