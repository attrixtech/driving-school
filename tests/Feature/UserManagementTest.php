<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Instructor;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Branch $branch1;
    protected Branch $branch2;
    protected User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->tenant = Tenant::create([
            'name' => 'thrumble.driving-school',
            'slug' => 'thrumble-driving-school',
            'email' => 'admin@thrumble.driving-school',
            'phone' => '+91 98370 43210',
            'status' => 'active',
            'subscription_plan' => 'Enterprise',
            'subscription_status' => 'active',
        ]);

        $this->branch1 = Branch::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Sanjay Place Branch',
            'code' => 'SANJAY',
            'is_main_branch' => true,
            'status' => 'active',
        ]);

        $this->branch2 = Branch::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Kamla Nagar Branch',
            'code' => 'KAMLA',
            'is_main_branch' => false,
            'status' => 'active',
        ]);

        $this->owner = User::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Owner Admin',
            'email' => 'owner@thrumble.driving-school',
            'password' => bcrypt('password123'),
            'role_slug' => 'business_owner',
            'is_active' => true,
        ]);
        $this->owner->syncRoles(['business_owner']);
    }

    public function test_business_owner_can_view_users_directory(): void
    {
        $response = $this->actingAs($this->owner)->get('/users');

        $response->assertStatus(200);
        $response->assertSeeText('Staff & User Management');
        $response->assertSee('Owner Admin');
        $response->assertSee('Total Users');
    }

    public function test_business_owner_can_create_new_staff_user(): void
    {
        $userData = [
            'name' => 'Pooja Sharma',
            'email' => 'pooja.sharma@thrumble.driving-school',
            'phone' => '9888877777',
            'role_slug' => 'branch_manager',
            'branch_id' => $this->branch1->id,
            'branches' => [$this->branch1->id, $this->branch2->id],
            'password' => 'secret123',
            'is_active' => '1',
        ];

        $response = $this->actingAs($this->owner)->post('/users', $userData);

        $response->assertRedirect('/users');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'tenant_id' => $this->tenant->id,
            'name' => 'Pooja Sharma',
            'email' => 'pooja.sharma@thrumble.driving-school',
            'role_slug' => 'branch_manager',
            'is_active' => true,
        ]);

        $createdUser = User::where('email', 'pooja.sharma@thrumble.driving-school')->first();
        $this->assertNotNull($createdUser);
        $this->assertTrue(Hash::check('secret123', $createdUser->password));
        $this->assertCount(2, $createdUser->branches);
    }

    public function test_creating_instructor_user_automatically_creates_instructor_record(): void
    {
        $userData = [
            'name' => 'Ravi Instructor',
            'email' => 'ravi.instructor@thrumble.driving-school',
            'phone' => '9811122233',
            'role_slug' => 'instructor',
            'branch_id' => $this->branch1->id,
            'branches' => [$this->branch1->id],
            'password' => 'instructor123',
            'license_number' => 'UP-80-2024-9988',
            'is_active' => '1',
        ];

        $response = $this->actingAs($this->owner)->post('/users', $userData);

        $response->assertRedirect('/users');
        $this->assertDatabaseHas('users', [
            'email' => 'ravi.instructor@thrumble.driving-school',
            'role_slug' => 'instructor',
        ]);

        $createdUser = User::where('email', 'ravi.instructor@thrumble.driving-school')->first();
        $this->assertNotNull($createdUser);

        // Verify Instructor table entry
        $this->assertDatabaseHas('instructors', [
            'tenant_id' => $this->tenant->id,
            'user_id' => $createdUser->id,
            'name' => 'Ravi Instructor',
            'email' => 'ravi.instructor@thrumble.driving-school',
        ]);
    }

    public function test_business_owner_can_update_user_and_toggle_status(): void
    {
        $staff = User::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Arun Staff',
            'email' => 'arun@thrumble.driving-school',
            'phone' => '9123456789',
            'role_slug' => 'admission_executive',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);

        // 1. Update User
        $response = $this->actingAs($this->owner)->post("/users/{$staff->id}", [
            'name' => 'Arun Kumar Updated',
            'email' => 'arun.updated@thrumble.driving-school',
            'phone' => '9999988888',
            'role_slug' => 'branch_manager',
            'branch_id' => $this->branch2->id,
            'branches' => [$this->branch2->id],
            'is_active' => '1',
        ]);

        $response->assertRedirect('/users');
        $this->assertDatabaseHas('users', [
            'id' => $staff->id,
            'name' => 'Arun Kumar Updated',
            'email' => 'arun.updated@thrumble.driving-school',
            'role_slug' => 'branch_manager',
            'branch_id' => $this->branch2->id,
        ]);

        // 2. Toggle Status
        $toggleResponse = $this->actingAs($this->owner)->post("/users/{$staff->id}/toggle");
        $toggleResponse->assertSessionHas('success');
        $this->assertFalse((bool)$staff->fresh()->is_active);

        // 3. Reset Password
        $passResponse = $this->actingAs($this->owner)->post("/users/{$staff->id}/password", [
            'password' => 'newSecretPass456',
            'password_confirmation' => 'newSecretPass456',
        ]);
        $passResponse->assertSessionHas('success');
        $this->assertTrue(Hash::check('newSecretPass456', $staff->fresh()->password));
    }
}
