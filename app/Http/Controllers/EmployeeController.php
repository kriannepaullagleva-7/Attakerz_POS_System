<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::withCount(['sales', 'stockIns', 'productions'])
            ->orderBy('last_name')
            ->get();

        $totalEmployees = $employees->count();

        return view('employees.index', compact(
            'employees',
            'totalEmployees'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name'     => 'required|string|max:50',
            'middle_name'    => 'nullable|string|max:50',
            'last_name'      => 'required|string|max:50',
            'contact_number' => 'nullable|string|max:20',
            'address'        => 'nullable|string',
            'role'           => 'required|in:cashier,staff',
        ]);

        $employee = Employee::create($request->only([
            'first_name', 'middle_name', 'last_name',
            'contact_number', 'address', 'role',
        ]));

        return redirect()->route('employees.index')
            ->with('success', "Employee \"{$employee->full_name}\" added successfully!");
    }

    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'first_name'     => 'required|string|max:50',
            'middle_name'    => 'nullable|string|max:50',
            'last_name'      => 'required|string|max:50',
            'contact_number' => 'nullable|string|max:20',
            'address'        => 'nullable|string',
            'role'           => 'required|in:cashier,staff',
        ]);

        $employee->update($request->only([
            'first_name', 'middle_name', 'last_name',
            'contact_number', 'address', 'role',
        ]));

        return redirect()->route('employees.index')
            ->with('success', 'Employee updated successfully!');
    }

    public function destroy(Employee $employee)
    {
        $name = $employee->full_name;
        $employee->delete();
        return redirect()->route('employees.index')
            ->with('success', "\"{$name}\" has been removed.");
    }
}
