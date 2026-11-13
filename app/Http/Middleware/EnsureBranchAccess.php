<?php

namespace App\Http\Middleware;

use App\Models\Inspection;
use App\Models\InspectionDiagramMark;
use App\Models\InspectionItem;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\User;
use App\Models\Vehicle;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Defence in depth for multi-branch isolation: list views already filter by
 * branch_id, but a route-model-bound {vehicle}/{inspection}/{invoice}/etc. in a
 * URL has no inherent branch check. This middleware makes every staff route
 * reject access to another branch's record, regardless of which action it is —
 * admins bypass it since they have legitimate head-office-wide access.
 */
class EnsureBranchAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->hasRole('admin')) {
            return $next($request);
        }

        if ($customer = $request->route('customer')) {
            $customer = $customer instanceof User ? $customer : User::with('vehicles')->find($customer);
            $registeredAtBranch = $customer?->branch_id === $user->branch_id;
            $hasVehicleInBranch = $customer?->vehicles->contains('branch_id', $user->branch_id);

            abort_unless($registeredAtBranch || $hasVehicleInBranch, 403, 'This customer has no record at your branch.');

            return $next($request);
        }

        $branchId = $this->resolveBranchId($request);

        if ($branchId !== null && $branchId !== $user->branch_id) {
            abort(403, 'You do not have access to this branch\'s records.');
        }

        return $next($request);
    }

    private function resolveBranchId(Request $request): ?int
    {
        if ($vehicle = $request->route('vehicle')) {
            $vehicle = $vehicle instanceof Vehicle ? $vehicle : Vehicle::find($vehicle);

            return $vehicle?->branch_id;
        }

        if ($inspection = $request->route('inspection')) {
            $inspection = $inspection instanceof Inspection ? $inspection : Inspection::find($inspection);

            return $inspection?->branch_id;
        }

        if ($invoice = $request->route('invoice')) {
            $invoice = $invoice instanceof Invoice ? $invoice : Invoice::with('vehicle')->find($invoice);

            return $invoice?->vehicle?->branch_id;
        }

        if ($item = $request->route('item')) {
            $item = $item instanceof InspectionItem ? $item : InspectionItem::with('inspection')->find($item);

            return $item?->inspection?->branch_id;
        }

        if ($mark = $request->route('mark')) {
            $mark = $mark instanceof InspectionDiagramMark ? $mark : InspectionDiagramMark::with('inspection')->find($mark);

            return $mark?->inspection?->branch_id;
        }

        if ($quotation = $request->route('quotation')) {
            $quotation = $quotation instanceof Quotation ? $quotation : Quotation::with('inspection')->find($quotation);

            return $quotation?->inspection?->branch_id;
        }

        if ($stockItem = $request->route('stockItem')) {
            $stockItem = $stockItem instanceof InventoryItem ? $stockItem : InventoryItem::find($stockItem);

            return $stockItem?->branch_id;
        }

        return null;
    }
}
