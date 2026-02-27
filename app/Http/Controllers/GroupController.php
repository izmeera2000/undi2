<?php

namespace App\Http\Controllers;

 
use App\Models\Group;
use App\Models\Member;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    // List all groups with members
    public function index()
    {
        $groups = Group::with('members')->get();
        return view('members.groups.index', compact('groups'));
    }

    // Show form to create a group
    public function create()
    {
        return view('members.groups.create');
    }

    // Store new group
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Group::create($request->only('name', 'description'));

        return redirect()->route('members.groups.index')->with('success', 'Group created!');
    }

    // Show edit form
    public function edit(Group $group)
    {
        return view('members.groups.edit', compact('group'));
    }

    // Update group
    public function update(Request $request, Group $group)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $group->update($request->only('name', 'description'));

        return redirect()->route('members.groups.index')->with('success', 'Group updated!');
    }

    // Delete group
    public function destroy(Group $group)
    {
        $group->delete();
        return redirect()->route('members.groups.index')->with('success', 'Group deleted!');
    }

    // Invite member by email
    public function invite(Request $request, Group $group)
    {
        $request->validate([
            'email' => 'required|email|exists:members,email',
        ]);

        $member = Member::where('email', $request->email)->first();
        $group->members()->syncWithoutDetaching([$member->id]);

        return back()->with('success', 'Member added!');
    }

    // Remove member
    public function removeMember(Group $group, Member $member)
    {
        $group->members()->detach($member->id);
        return back()->with('success', 'Member removed!');
    }
}