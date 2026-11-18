<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $branchId = $user->hasRole('admin') ? null : $user->branch_id;

        $suppliers = Supplier::with('branch')
            ->when($branchId, fn ($q) => $q->where(fn ($qq) => $qq->where('branch_id', $branchId)->orWhereNull('branch_id')))
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('staff.suppliers.index', compact('suppliers'));
    }

    public function create(Request $request)
    {
        $branches = $request->user()->hasRole('admin') ? Branch::orderBy('name')->get() : collect();

        return view('staff.suppliers.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'branch_id' => ['nullable', 'exists:branches,id'],
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
        ]);

        $supplier = Supplier::create([
            'branch_id' => $user->hasRole('admin') ? ($data['branch_id'] ?? null) : $user->branch_id,
            'name' => $data['name'],
            'contact_person' => $data['contact_person'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
        ]);

        return redirect()->route('staff.suppliers.index')->with('status', "Supplier \"{$supplier->name}\" added.");
    }

    public function edit(Supplier $supplier)
    {
        $branches = request()->user()->hasRole('admin') ? Branch::orderBy('name')->get() : collect();

        return view('staff.suppliers.edit', compact('supplier', 'branches'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $user = $request->user();

        $data = $request->validate([
            'branch_id' => ['nullable', 'exists:branches,id'],
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
        ]);

        $supplier->update([
            'branch_id' => $user->hasRole('admin') ? ($data['branch_id'] ?? null) : $supplier->branch_id,
            'name' => $data['name'],
            'contact_person' => $data['contact_person'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
        ]);

        return redirect()->route('staff.suppliers.index')->with('status', 'Supplier updated.');
    }

    public function toggle(Supplier $supplier)
    {
        $supplier->update(['is_active' => ! $supplier->is_active]);

        return back()->with('status', $supplier->is_active ? 'Supplier reactivated.' : 'Supplier deactivated.');
    }
}
