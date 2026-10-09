<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;

class EmployeeController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fullname'       => 'required|string|max:255',
            'role'           => 'required|string|max:100',
            'contact_number' => 'required|string|max:50',
            'status'         => 'required|in:active,inactive',
            'notes'          => 'nullable|string|max:1000',
        ]);

        Employee::create($validated);

        return redirect()->route('users.index')->with('success', 'Employee record added successfully.');
    }
}