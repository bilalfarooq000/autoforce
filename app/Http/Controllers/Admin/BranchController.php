<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Invoice;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::withCount(['users', 'vehicles'])->get()->map(function (Branch $branch) {
            $branch->active_jobs_count = \App\Models\Inspection::where('branch_id', $branch->id)
                ->where('status', '!=', 'completed')->count();
            $branch->outstanding_amount = Invoice::whereHas('vehicle', fn ($v) => $v->where('branch_id', $branch->id))
                ->whereIn('status', ['unpaid', 'partial'])->get()->sum(fn ($i) => $i->balanceDue());

            return $branch;
        });

        return view('admin.branches.index', compact('branches'));
    }

    public function create()
    {
        return view('admin.branches.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:branches,name'],
            'city' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $branch = Branch::create($data);

        return redirect()->route('admin.branches.index')->with('status', "Branch \"{$branch->name}\" registered.");
    }

    public function edit(Branch $branch)
    {
        return view('admin.branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:branches,name,'.$branch->id],
            'city' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $branch->update($data);

        return redirect()->route('admin.branches.index')->with('status', 'Branch updated.');
    }
}
