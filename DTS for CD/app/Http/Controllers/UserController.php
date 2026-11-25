<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Campus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    private function authorizeAccess(): void
    {
        if (!Auth::user()->isDocumentController()) abort(403);
    }

    public function index()
    {
        $user  = Auth::user();
        $query = User::with('campus')->where('campus_id', $user->campus_id);

        $users = $query->orderBy('role')->orderBy('name')->paginate(20);
        $canManage = $user->isDocumentController();

        return view('users.index', compact('users', 'canManage'));
    }

    public function create()
    {
        $this->authorizeAccess();
        $campuses = Campus::where('is_active', true)->get();
        return view('users.create', compact('campuses'));
    }

    public function store(Request $request)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|min:8|confirmed',
            'role'       => 'required|in:document_controller,campus_director,routing_unit,document_submitter',
            'department' => 'nullable|string|max:255',
            'position'   => 'nullable|string|max:255',
        ]);

        User::create([
            ...$validated,
            'campus_id' => Auth::user()->campus_id,
            'password'  => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $this->authorizeAccess();
        if ($user->campus_id !== Auth::user()->campus_id) abort(403);
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeAccess();
        if ($user->campus_id !== Auth::user()->campus_id) abort(403);

        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email,'.$user->id,
            'role'       => 'required|in:document_controller,campus_director,routing_unit,document_submitter',
            'department' => 'nullable|string|max:255',
            'position'   => 'nullable|string|max:255',
            'is_active'  => 'boolean',
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:8|confirmed']);
            $validated['password'] = Hash::make($request->password);
        }

        $user->update($validated);
        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $this->authorizeAccess();
        if ($user->id === Auth::id()) return back()->with('error', 'Cannot deactivate your own account.');
        $user->update(['is_active' => false]);
        return redirect()->route('users.index')->with('success', 'User deactivated.');
    }
}
