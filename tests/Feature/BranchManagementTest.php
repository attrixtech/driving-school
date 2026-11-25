<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Branch $mainBranch;
    protected User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        $roles = [
            'business_owner' => 'Business Owner',
            'branch_manager' => 'Branch Manager',
            'instructor' => 'Driving Instructor',
        ];
        foreach ($roles as $slug => $name) {
            Role::firstOrCreate(['slug' => $slug], ['name' => $name]);
        }

        $this->tenant = Tenant::create([
            'name' => 'thrumble.driving-school',
            'slug' => 'thrumble-driving-school',
            'email' => 'admin@thrumble.driving-school',
            'phone' => '+91 98370 43210',
            'status' => 'active',
            'subscription_plan' => 'Multi-Branch',
            'subscription_status' => 'active',
        ]);

        $this->mainBranch = Branch::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'HQ-AGRA',
            'name' => 'thrumble.driving-school HQ - Sanjay Place',
            'phone' => '+91 562 401 2345',
            'email' => 'sanjayplace@thrumble.driving-school',
            'address' => 'Plot 42, Block B, Sanjay Place',
            'city' => 'Agra',
            'state' => 'Uttar Pradesh',
            'postal_code' => '282002',
            'is_main_branch' => true,
            'is_active' => true,
        ]);

        $this->owner = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->mainBranch->id,
            'role_slug' => 'business_owner',
            'email' => 'owner@thrumble.driving-school',
        ]);
    }

    public function test_business_owner_can_view_branch_network_page(): void
    {
        $response = $this->actingAs($this->owner)->get(route('branches.index'));

        $response->assertStatus(200);
        $response->assertSee('Branch Network');
        $response->assertSee('+ Add New Branch');
        $response->assertSee('Total Branches');
    }

    public function test_can_create_new_branch(): void
    {
        $response = $this->actingAs($this->owner)->post(route('branches.store'), [
            'name' => 'Tajganj Branch',
            'code' => 'BR-TAJGANJ',
            'phone' => '+91 98371 22334',
            'email' => 'tajganj@thrumble.driving-school',
            'address' => 'Fatehabad Road, Tajganj',
            'city' => 'Agra',
            'state' => 'Uttar Pradesh',
            'postal_code' => '282001',
            'is_active' => 1,
            'is_main_branch' => 0,
        ]);

        $response->assertRedirect(route('branches.index'));
        $this->assertDatabaseHas('branches', [
            'code' => 'BR-TAJGANJ',
            'name' => 'Tajganj Branch',
            'city' => 'Agra',
            'is_active' => 1,
            'is_main_branch' => 0,
        ]);
    }

    public function test_can_edit_existing_branch(): void
    {
        $branch = Branch::create([
            'tenant_id' => $this->owner->tenant_id,
            'name' => 'Sikandra Sector 10 Branch',
            'code' => 'BR-SIKANDRA',
            'phone' => '+91 98372 33445',
            'city' => 'Agra',
            'state' => 'Uttar Pradesh',
            'is_main_branch' => false,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->owner)->post(route('branches.update', $branch->id), [
            'name' => 'Sikandra Training Hub',
            'code' => 'BR-SIKANDRA-HUB',
            'phone' => '+91 98372 99999',
            'email' => 'sikandra@thrumble.driving-school',
            'address' => 'Plot 4, Avas Vikas, Sikandra',
            'city' => 'Agra',
            'state' => 'Uttar Pradesh',
            'postal_code' => '282007',
            'is_active' => 1,
            'is_main_branch' => 0,
        ]);

        $response->assertRedirect(route('branches.index'));
        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
            'code' => 'BR-SIKANDRA-HUB',
            'name' => 'Sikandra Training Hub',
            'phone' => '+91 98372 99999',
        ]);
    }

    public function test_can_toggle_branch_active_status(): void
    {
        $branch = Branch::create([
            'tenant_id' => $this->owner->tenant_id,
            'name' => 'Dayalbagh Branch',
            'code' => 'BR-DAYALBAGH',
            'city' => 'Agra',
            'state' => 'Uttar Pradesh',
            'is_main_branch' => false,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->owner)->post(route('branches.toggle', $branch->id));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
            'is_active' => false,
        ]);

        // Toggle back to active
        $response = $this->actingAs($this->owner)->post(route('branches.toggle', $branch->id));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
            'is_active' => true,
        ]);
    }

    public function test_can_designate_branch_as_primary_headquarters(): void
    {
        $branch2 = Branch::create([
            'tenant_id' => $this->owner->tenant_id,
            'name' => 'Khandari Training Hub',
            'code' => 'BR-KHANDARI',
            'city' => 'Agra',
            'state' => 'Uttar Pradesh',
            'is_main_branch' => false,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->owner)->post(route('branches.set-main', $branch2->id));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('branches', [
            'id' => $branch2->id,
            'is_main_branch' => true,
        ]);

        $this->assertDatabaseHas('branches', [
            'id' => $this->mainBranch->id,
            'is_main_branch' => false,
        ]);
    }

    public function test_cannot_deactivate_primary_headquarters(): void
    {
        $response = $this->actingAs($this->owner)->post(route('branches.toggle', $this->mainBranch->id));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('branches', [
            'id' => $this->mainBranch->id,
            'is_active' => true,
        ]);
    }
}
