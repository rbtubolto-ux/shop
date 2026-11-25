<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CampusController extends Controller
{
    private function authorizeSuperAdmin(): void
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Only Super Admin can manage campuses.');
        }
    }

    public function index()
    {
        $this->authorizeSuperAdmin();
        $campuses = Campus::withCount(['users', 'documents'])->latest()->paginate(15);
        return view('campuses.index', compact('campuses'));
    }

    public function create()
    {
        $this->authorizeSuperAdmin();
        return view('campuses.create');
    }

    public function store(Request $request)
    {
        $this->authorizeSuperAdmin();

        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'code'    => 'required|string|max:20|unique:campuses,code',
            'address' => 'nullable|string|max:500',
            'contact' => 'nullable|string|max:100',
        ]);

        Campus::create(array_merge($validated, ['is_active' => true]));

        return redirect()->route('campuses.index')->with('success', 'Campus created successfully.');
    }

    public function edit(Campus $campus)
    {
        $this->authorizeSuperAdmin();
        return view('campuses.edit', compact('campus'));
    }

    public function update(Request $request, Campus $campus)
    {
        $this->authorizeSuperAdmin();

        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'code'      => 'required|string|max:20|unique:campuses,code,' . $campus->id,
            'address'   => 'nullable|string|max:500',
            'contact'   => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $campus->update($validated);

        return redirect()->route('campuses.index')->with('success', 'Campus updated successfully.');
    }
}
