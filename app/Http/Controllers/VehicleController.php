<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\Expense;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\VehicleDocument;
use App\Models\VehicleFuelLog;
use App\Models\VehicleMaintenance;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;
        $branchId = session('active_branch_id') ?? auth()->user()?->branch_id;

        $vehicles = Vehicle::where('tenant_id', $tenantId)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->with(['branch', 'category', 'documents'])
            ->latest()
            ->paginate(15);

        $branches = Branch::where('tenant_id', $tenantId)->get();
        $categories = VehicleCategory::where('tenant_id', $tenantId)->get();

        return view('fleet.vehicles', compact('vehicles', 'branches', 'categories'));
    }

    public function store(Request $request)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;

        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'vehicle_category_id' => 'required|exists:vehicle_categories,id',
            'registration_number' => 'required|string|max:30',
            'make' => 'required|string|max:50',
            'model' => 'required|string|max:50',
            'year' => 'nullable|integer|min:2000|max:2030',
            'color' => 'nullable|string|max:30',
            'transmission' => 'required|in:manual,automatic',
            'fuel_type' => 'required|in:petrol,diesel,cng,electric,hybrid',
            'dual_control_fitted' => 'required|in:yes,no',
            'odometer_reading' => 'required|integer|min:0',
            'insurance_expiry_date' => 'nullable|date',
            'fitness_expiry_date' => 'nullable|date',
            'pollution_expiry_date' => 'nullable|date',
            'permit_expiry_date' => 'nullable|date',
        ]);

        $vehicle = Vehicle::create([
            'tenant_id' => $tenantId,
            'status' => 'active',
            ...$validated,
        ]);

        ActivityLog::record('fleet', 'create', "Registered vehicle {$vehicle->registration_number} ({$vehicle->make} {$vehicle->model})", ['vehicle_id' => $vehicle->id]);

        return redirect()->back()->with('success', "Vehicle {$vehicle->registration_number} added to fleet.");
    }

    public function fuelAndMaintenance(Request $request)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;
        $branchId = session('active_branch_id') ?? auth()->user()?->branch_id;

        $fuelLogs = VehicleFuelLog::where('tenant_id', $tenantId)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->with('vehicle')
            ->latest('fuel_date')
            ->take(20)
            ->get();

        $maintenances = VehicleMaintenance::where('tenant_id', $tenantId)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->with('vehicle')
            ->latest('service_date')
            ->take(20)
            ->get();

        $vehicles = Vehicle::where('tenant_id', $tenantId)->where('status', 'active')->get();

        return view('fleet.fuel-maintenance', compact('fuelLogs', 'maintenances', 'vehicles'));
    }

    public function storeFuelLog(Request $request)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;

        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'fuel_date' => 'required|date',
            'liters' => 'required|numeric|min:0.1',
            'cost_per_liter' => 'required|numeric|min:1',
            'odometer_reading' => 'required|integer|min:0',
            'station_name' => 'nullable|string|max:150',
            'receipt_number' => 'nullable|string|max:100',
        ]);

        $totalCost = round($validated['liters'] * $validated['cost_per_liter'], 2);
        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);

        $fuel = VehicleFuelLog::create([
            'tenant_id' => $tenantId,
            'branch_id' => $vehicle->branch_id,
            'vehicle_id' => $vehicle->id,
            'fuel_date' => $validated['fuel_date'],
            'liters' => $validated['liters'],
            'cost_per_liter' => $validated['cost_per_liter'],
            'total_cost' => $totalCost,
            'odometer_reading' => $validated['odometer_reading'],
            'station_name' => $validated['station_name'] ?? null,
            'receipt_number' => $validated['receipt_number'] ?? null,
        ]);

        // Also record as business expense
        Expense::create([
            'tenant_id' => $tenantId,
            'branch_id' => $vehicle->branch_id,
            'vehicle_id' => $vehicle->id,
            'created_by_user_id' => auth()->id(),
            'expense_category' => 'fuel',
            'title' => "Fuel refill for {$vehicle->registration_number} ({$validated['liters']} L)",
            'expense_date' => $validated['fuel_date'],
            'amount' => $totalCost,
            'payment_mode' => 'cash',
            'reference_number' => $validated['receipt_number'] ?? null,
            'paid_to' => $validated['station_name'] ?? 'Fuel Station',
        ]);

        if ($validated['odometer_reading'] > $vehicle->odometer_reading) {
            $vehicle->update(['odometer_reading' => $validated['odometer_reading']]);
        }

        return redirect()->back()->with('success', "Fuel log of ₹{$totalCost} recorded for {$vehicle->registration_number}.");
    }

    public function storeMaintenance(Request $request)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;

        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'service_type' => 'required|string|max:100',
            'service_date' => 'required|date',
            'cost' => 'required|numeric|min:0',
            'odometer_reading' => 'nullable|integer|min:0',
            'workshop_name' => 'nullable|string|max:150',
            'invoice_number' => 'nullable|string|max:100',
            'next_service_due_date' => 'nullable|date',
            'description' => 'nullable|string',
        ]);

        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);

        $maint = VehicleMaintenance::create([
            'tenant_id' => $tenantId,
            'branch_id' => $vehicle->branch_id,
            ...$validated,
        ]);

        // Record as expense
        Expense::create([
            'tenant_id' => $tenantId,
            'branch_id' => $vehicle->branch_id,
            'vehicle_id' => $vehicle->id,
            'created_by_user_id' => auth()->id(),
            'expense_category' => 'maintenance',
            'title' => "Maintenance ({$validated['service_type']}) for {$vehicle->registration_number}",
            'expense_date' => $validated['service_date'],
            'amount' => $validated['cost'],
            'payment_mode' => 'bank_transfer',
            'reference_number' => $validated['invoice_number'] ?? null,
            'paid_to' => $validated['workshop_name'] ?? 'Workshop',
        ]);

        return redirect()->back()->with('success', "Maintenance record saved for {$vehicle->registration_number}.");
    }
}
