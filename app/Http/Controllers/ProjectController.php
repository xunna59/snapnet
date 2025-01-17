<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Notifications\NewProject;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Project::query();


        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }


        if ($request->has('status')) {
            $query->where('status', $request->status);
        }


        $projects = $query->paginate(10);

        return response()->json($projects);
    }

    public function addEmployeeToProject(Request $request, $projectId)
    {

        $request->validate([
            'employee_id' => 'required|exists:users,id',
        ]);


        $project = Project::findOrFail($projectId);


        $employee = Employee::findOrFail($request->id);


        if (!$project->employees()->where('employee_id', $employee->id)->exists()) {
            $project->employees()->attach($employee);


            $employee->notify(new NewProject($project->name));
        } else {
            return response()->json([
                'message' => 'User is already assigned to this project.',
            ], 400);
        }

        return response()->json([
            'message' => 'Employee added to project and email sent.',
            'project' => $project->name,
            'user' => $employee->name,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:projects,name',

            'description' => 'nullable|string',

            'status' => 'required|in:pending,in_progress,completed',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);



        $project = Project::create($validated);
        return response()->json($project, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        return $project->load('employees');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {

        $validated = $request->validate([
            'name' => 'sometimes|required|unique:projects,name,' . $project->id,
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $project->update($validated);
        return response()->json($project);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $project->delete();
        return response()->json(['message' => 'Project deleted successfully.']);
    }

    public function getSummary()
    {

        $totalProjects = Project::count();


        $totalEmployees = Employee::count();


        $projectsByStatus = Project::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get();


        return response()->json([
            'total_projects' => $totalProjects,
            'total_employees' => $totalEmployees,
            'projects_by_status' => $projectsByStatus
        ]);
    }
}
