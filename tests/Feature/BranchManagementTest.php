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
            'name' => 'Apex Driving Academy',
            'slug' => 'apex-driving',
            'email' => 'admin@apexdriving.com',
            'phone' => '9876543210',
            'status' => 'active',
            'subscription_plan' => 'Multi-Branch',
            'subscription_status' => 'active',
        ]);

        $this->mainBranch = Branch::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'HQ-DELHI',
            'name' => 'Apex Driving Academy HQ - Connaught Place',
            'phone' => '+91 11 2345 6789',
            'email' => 'cp@apexdriving.com',
            'address' => 'Connaught Place Block B',
            'city' => 'New Delhi',
            'state' => 'Delhi',
            'postal_code' => '110001',
            'is_main_branch' => true,
            'is_active' => true,
        ]);

        $this->owner = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->mainBranch->id,
            'role_slug' => 'business_owner',
            'email' => 'owner@apexdriving.com',
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
            'name' => 'South Extension Branch',
            'code' => 'BR-SOUTH-EXT',
            'phone' => '+91 98111 22334',
            'email' => 'southext@apexdriving.com',
            'address' => 'Part 2, Ring Road',
            'city' => 'New Delhi',
            'state' => 'Delhi',
            'postal_code' => '110049',
            'is_active' => 1,
            'is_main_branch' => 0,
        ]);

        $response->assertRedirect(route('branches.index'));
        $this->assertDatabaseHas('branches', [
            'code' => 'BR-SOUTH-EXT',
            'name' => 'South Extension Branch',
            'city' => 'New Delhi',
            'is_active' => 1,
            'is_main_branch' => 0,
        ]);
    }

    public function test_can_edit_existing_branch(): void
    {
        $branch = Branch::create([
            'tenant_id' => $this->owner->tenant_id,
            'name' => 'Dwarka Sector 10 Branch',
            'code' => 'BR-DWARKA',
            'phone' => '+91 98222 33445',
            'city' => 'New Delhi',
            'state' => 'Delhi',
            'is_main_branch' => false,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->owner)->post(route('branches.update', $branch->id), [
            'name' => 'Dwarka Sector 10 Training Hub',
            'code' => 'BR-DWARKA-HUB',
            'phone' => '+91 98222 99999',
            'email' => 'dwarka@apexdriving.com',
            'address' => 'Plot 4, Sector 10',
            'city' => 'New Delhi',
            'state' => 'Delhi',
            'postal_code' => '110075',
            'is_active' => 1,
            'is_main_branch' => 0,
        ]);

        $response->assertRedirect(route('branches.index'));
        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
            'code' => 'BR-DWARKA-HUB',
            'name' => 'Dwarka Sector 10 Training Hub',
            'phone' => '+91 98222 99999',
        ]);
    }

    public function test_can_toggle_branch_active_status(): void
    {
        $branch = Branch::create([
            'tenant_id' => $this->owner->tenant_id,
            'name' => 'Noida Sector 18 Branch',
            'code' => 'BR-NOIDA',
            'city' => 'Noida',
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
            'name' => 'Gurugram Cyber Hub',
            'code' => 'BR-GURUGRAM',
            'city' => 'Gurugram',
            'state' => 'Haryana',
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
