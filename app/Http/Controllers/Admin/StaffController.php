<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $staff = User::role(['admin', 'manager', 'service_advisor', 'technician'])
            ->with('roles', 'branch')
            ->when($request->branch_id, fn ($q) => $q->where('branch_id', $request->branch_id))
            ->latest()->paginate(15)->withQueryString();

        $branches = Branch::all();

        return view('admin.staff.index', compact('staff', 'branches'));
    }

    public function create()
    {
        $branches = Branch::all();

        return view('admin.staff.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'branch_id' => ['nullable', 'required_unless:role,admin', 'exists:branches,id'],
            'role' => ['required', 'in:admin,manager,service_advisor,technician,inventory_manager'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'branch_id' => $data['branch_id'] ?? null,
            'password' => bcrypt($data['password']),
        ]);

        $user->assignRole($data['role']);

        return redirect()->route('admin.staff.index')->with('status', 'Staff account created.');
    }

    public function edit(User $staff)
    {
        abort_if($staff->hasRole('customer'), 404);
        $branches = Branch::all();

        return view('admin.staff.edit', compact('staff', 'branches'));
    }

    public function update(Request $request, User $staff)
    {
        abort_if($staff->hasRole('customer'), 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$staff->id],
            'phone' => ['nullable', 'string', 'max:30'],
            'branch_id' => ['nullable', 'required_unless:role,admin', 'exists:branches,id'],
            'role' => ['required', 'in:admin,manager,service_advisor,technician,inventory_manager'],
            'is_active' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        // The last admin account can't be demoted or deactivated — that would
        // leave nobody able to manage staff, branches or this account itself.
        if ($staff->hasRole('admin') && ($data['role'] !== 'admin' || ! $request->boolean('is_active'))
            && User::role('admin')->where('is_active', true)->count() <= 1) {
            return back()->withErrors(['role' => 'This is the only active admin account — it cannot be demoted or deactivated.']);
        }

        $staff->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'branch_id' => $data['branch_id'] ?? null,
            'is_active' => $request->boolean('is_active'),
            ...($data['password'] ? ['password' => bcrypt($data['password'])] : []),
        ]);

        $staff->syncRoles([$data['role']]);

        return redirect()->route('admin.staff.index')->with('status', 'Staff account updated.');
    }

    public function destroy(Request $request, User $staff)
    {
        abort_if($staff->hasRole('customer'), 404);

        if ($staff->id === $request->user()->id) {
            return back()->with('error', 'You cannot deactivate your own account while logged in as it.');
        }

        if ($staff->hasRole('admin') && User::role('admin')->where('is_active', true)->count() <= 1) {
            return back()->with('error', 'This is the only active admin account — it cannot be deactivated.');
        }

        $staff->update(['is_active' => false]);

        return back()->with('status', 'Staff account deactivated.');
    }
}
