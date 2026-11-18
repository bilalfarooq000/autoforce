<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\ServiceType;
use Illuminate\Http\Request;

class ServiceTypeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $branchId = $user->hasRole('admin') ? null : $user->branch_id;

        $serviceTypes = ServiceType::with('branch')
            ->when($branchId, fn ($q) => $q->where(fn ($qq) => $qq->where('branch_id', $branchId)->orWhereNull('branch_id')))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('staff.service-types.index', compact('serviceTypes'));
    }

    public function create(Request $request)
    {
        $branches = $request->user()->hasRole('admin') ? Branch::orderBy('name')->get() : collect();

        return view('staff.service-types.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'branch_id' => ['nullable', 'exists:branches,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'type' => ['required', 'in:part,labor'],
            'default_price' => ['required', 'numeric', 'min:0'],
        ]);

        ServiceType::create([
            'branch_id' => $user->hasRole('admin') ? ($data['branch_id'] ?? null) : $user->branch_id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'default_price' => $data['default_price'],
        ]);

        return redirect()->route('staff.service-types.index')->with('status', 'Service type added.');
    }

    public function edit(ServiceType $serviceType)
    {
        $branches = request()->user()->hasRole('admin') ? Branch::orderBy('name')->get() : collect();

        return view('staff.service-types.edit', compact('serviceType', 'branches'));
    }

    public function update(Request $request, ServiceType $serviceType)
    {
        $user = $request->user();

        $data = $request->validate([
            'branch_id' => ['nullable', 'exists:branches,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'type' => ['required', 'in:part,labor'],
            'default_price' => ['required', 'numeric', 'min:0'],
        ]);

        $serviceType->update([
            'branch_id' => $user->hasRole('admin') ? ($data['branch_id'] ?? null) : $serviceType->branch_id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'default_price' => $data['default_price'],
        ]);

        return redirect()->route('staff.service-types.index')->with('status', 'Service type updated.');
    }

    public function toggle(ServiceType $serviceType)
    {
        $serviceType->update(['is_active' => ! $serviceType->is_active]);

        return back()->with('status', $serviceType->is_active ? 'Service type reactivated.' : 'Service type deactivated.');
    }

    /**
     * Branch-scoped catalog for the line-item "pick a service" dropdowns on
     * invoice/quotation forms — JSON, not a page.
     */
    public function picker(Request $request)
    {
        $user = $request->user();
        $branchId = $user->hasRole('admin') ? null : $user->branch_id;

        $serviceTypes = ServiceType::where('is_active', true)
            ->when($branchId, fn ($q) => $q->where(fn ($qq) => $qq->where('branch_id', $branchId)->orWhereNull('branch_id')))
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'default_price']);

        return response()->json($serviceTypes);
    }
}
