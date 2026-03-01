<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\TaskCategory;
use App\Models\Subtask;
use Illuminate\Http\Request;
use App\Notifications\TaskAssignedNotification;

class TaskController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Page View (NO DATA — jQuery will load it)
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $tasks = Task::with(['creator', 'assignee', 'category'])->latest()->get();
        $users = User::where('id', '!=', auth()->id())->get();
        $categories = TaskCategory::all(); // all task categories

        return view('task.index', compact('tasks', 'users', 'categories'));
    }



    /*
    |--------------------------------------------------------------------------
    | AJAX: Get All Tasks
    |--------------------------------------------------------------------------
    */

    public function data()
    {
        $userId = auth()->id();

        $tasks = Task::with([
            'creator',
            'assignee',
            'category',
            'subtasks',
            'activities.causer.profile',
        ])
            ->where(function ($query) use ($userId) {
                $query->where('created_by', $userId)
                    ->orWhere('assigned_to', $userId);
            })
            ->latest()
            ->get();

        return response()->json($tasks);
    }


    /*
    |--------------------------------------------------------------------------
    | Store Task (AJAX)
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high',
            'due_at' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'category_id' => 'nullable|exists:tasks_category,id',
            'tags' => 'nullable|array',
        ]);



        // Ensure tags is always an array
        $validated['tags'] = collect($validated['tags'] ?? [])
            ->filter()
            ->values()
            ->toArray();

        $validated['created_by'] = auth()->id();
        $validated['status'] = 'todo';

        $task = Task::create($validated);

    if ($task->assignee && $task->assigned_to !== auth()->id()) {
        $task->assignee->notify(new TaskAssignedNotification($task));
    }

        return response()->json($task, 201);
    }


    /*
    |--------------------------------------------------------------------------
    | Update Task
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:todo,in_progress,done',
            'priority' => 'sometimes|in:low,medium,high',
            'due_at' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'category_id' => 'nullable|exists:tasks_category,id',
            'tags' => 'nullable|array',
            'subtasks' => 'nullable|array',
            'subtasks.*.title' => 'required|string|max:255',
            'subtasks.*.is_completed' => 'nullable|boolean',
        ]);

        // Ensure tags is always an array
        $validated['tags'] = collect($validated['tags'] ?? [])
            ->filter()
            ->values()
            ->toArray();

        // Update main task
        $task->update($validated);

        // Handle subtasks
        if (!empty($validated['subtasks'])) {
            foreach ($validated['subtasks'] as $stData) {
                if (isset($stData['id'])) {
                    // Existing subtask, update it
                    $subtask = $task->subtasks()->find($stData['id']);
                    if ($subtask) {
                        $subtask->update([
                            'title' => $stData['title'],
                            'is_completed' => $stData['is_completed'] ?? false,
                        ]);
                    }
                } else {
                    // New subtask, create it
                    $task->subtasks()->create([
                        'title' => $stData['title'],
                        'is_completed' => $stData['is_completed'] ?? false,
                    ]);
                }
            }
        }

        // Return task with updated subtasks
        return response()->json($task->load('subtasks'));
    }


    /*
    |--------------------------------------------------------------------------
    | Toggle Complete (for checkbox)
    |--------------------------------------------------------------------------
    */

    public function toggleComplete(Task $task)
    {
        $task->update([
            'status' => $task->status === 'done' ? 'todo' : 'done'
        ]);

        return response()->json([
            'message' => 'Status updated',
            'status' => $task->status
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Toggle Important (Star Button)
    |--------------------------------------------------------------------------
    */

    public function toggleImportant(Task $task)
    {
        $newPriority = $task->priority === 'high' ? 'medium' : 'high';

        $task->update([
            'priority' => $newPriority
        ]);

        return response()->json([
            'message' => 'Priority updated',
            'priority' => $newPriority
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Task
    |--------------------------------------------------------------------------
    */

    public function destroy(Task $task)
    {
        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully'
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Add Subtask
    |--------------------------------------------------------------------------
    */

    public function addSubtask(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $subtask = $task->subtasks()->create($validated);

        return response()->json($subtask, 201);
    }


    /*
    |--------------------------------------------------------------------------
    | Toggle Subtask Completion
    |--------------------------------------------------------------------------
    */

    public function toggleSubtask(Subtask $subtask)
    {
        $subtask->update([
            'is_completed' => !$subtask->is_completed
        ]);

        return response()->json($subtask);
    }


    /*
    |--------------------------------------------------------------------------
    | Show Task Details
    |--------------------------------------------------------------------------
    */

    public function show(Task $task)
    {
        $task->load([
            'creator',
            'assignee',
            'category',
            'subtasks',
            'activities.causer'
        ]);

        return response()->json($task);
    }
}
