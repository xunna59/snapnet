<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        return $project->employees;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:employees,email',
            'position' => 'required|string',
        ]);

        $employee = $project->employees()->create($validated);
        return response()->json($employee, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Employee $employee)
    {
        return $employee;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, Employee $employee)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string',
            'email' => 'sometimes|email|unique:employees,email',
            'position' => 'required|string',
        ]);

        $employee->update($validated);
        return response()->json($employee);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Employee $employee)
    {
        $employee->delete();
        return response()->json(['message' => 'Employee deleted successfully.']);
    }
}
