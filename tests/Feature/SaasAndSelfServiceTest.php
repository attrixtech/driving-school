<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Course;
use App\Models\CoursePackage;
use App\Models\Instructor;
use App\Models\LessonSchedule;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Invoice;
use App\Models\Notification;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaasAndSelfServiceTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Branch $branch;
    protected User $superAdmin;
    protected User $owner;
    protected User $studentUser;
    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->superAdmin = User::create([
            'tenant_id' => null,
            'branch_id' => null,
            'name' => 'Platform Super Admin',
            'email' => 'superadmin@thrumble.driving-school',
            'password' => bcrypt('password123'),
            'role_slug' => 'super_admin',
            'is_active' => true,
        ]);
        $this->superAdmin->syncRoles(['super_admin']);

        $this->tenant = Tenant::create([
            'name' => 'thrumble.driving-school',
            'slug' => 'thrumble-driving-school',
            'email' => 'director@thrumble.driving-school',
            'phone' => '+91 98370 43210',
            'contact_person' => 'Director Admin',
            'status' => 'active',
            'subscription_plan' => 'Professional',
            'subscription_status' => 'active',
            'max_branches' => 5,
            'max_students' => 500,
            'max_vehicles' => 20,
        ]);

        $this->branch = Branch::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Main Training Ground',
            'code' => 'MAIN',
            'is_main_branch' => true,
            'status' => 'active',
        ]);

        $this->owner = User::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Owner Admin',
            'email' => 'owner@test.com',
            'password' => bcrypt('password123'),
            'role_slug' => 'business_owner',
            'is_active' => true,
        ]);
        $this->owner->syncRoles(['business_owner']);

        $this->studentUser = User::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'name' => 'John Learner',
            'email' => 'john.student@test.com',
            'password' => bcrypt('password123'),
            'role_slug' => 'student',
            'is_active' => true,
        ]);
        $this->studentUser->syncRoles(['student']);

        $this->student = Student::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'user_id' => $this->studentUser->id,
            'student_code' => 'STD-001',
            'first_name' => 'John',
            'last_name' => 'Learner',
            'phone' => '9876543210',
            'email' => 'john.student@test.com',
            'gender' => 'male',
            'date_of_birth' => '2000-01-01',
            'status' => 'in_training',
        ]);
    }

    public function test_superadmin_can_view_and_manage_tenants(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get('/superadmin/tenants');

        $response->assertStatus(200);
        $response->assertSee('SaaS Multi-Tenant Management');
        $response->assertSee('thrumble.driving-school');
    }

    public function test_superadmin_can_provision_new_tenant(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->post('/superadmin/tenants', [
                'name' => 'Taj Speed Driving School Agra',
                'contact_person' => 'Ramesh Gupta',
                'email' => 'director@tajspeed.thrumble.driving-school',
                'phone' => '+91 98379 77665',
                'subscription_plan' => 'Enterprise',
                'subscription_status' => 'active',
                'max_branches' => 10,
                'max_students' => 1000,
                'max_vehicles' => 30,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tenants', [
            'name' => 'Taj Speed Driving School Agra',
            'subscription_plan' => 'Enterprise',
        ]);
    }

    public function test_superadmin_can_update_subscription(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->post("/superadmin/tenants/{$this->tenant->id}/subscription", [
                'subscription_plan' => 'Multi-Branch Fleet',
                'subscription_status' => 'active',
                'max_branches' => 20,
                'max_students' => 2000,
                'max_vehicles' => 50,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tenants', [
            'id' => $this->tenant->id,
            'subscription_plan' => 'Multi-Branch Fleet',
            'max_students' => 2000,
        ]);
    }

    public function test_student_can_access_self_service_portal(): void
    {
        $response = $this->actingAs($this->studentUser)
            ->withSession([
                'active_tenant_id' => $this->tenant->id,
                'active_branch_id' => $this->branch->id,
            ])
            ->get('/student/portal');

        $response->assertStatus(200);
        $response->assertSee('John Learner');
        $response->assertSee('STD-001');
        $response->assertSee('Practical Driving Hours');
        $response->assertSee('RTO Exam Ready');
    }

    public function test_staff_can_dispatch_simulated_notifications(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession([
                'active_tenant_id' => $this->tenant->id,
                'active_branch_id' => $this->branch->id,
            ])
            ->post('/notifications/send', [
                'student_id' => $this->student->id,
                'channel' => 'whatsapp',
                'template' => 'lesson_reminder',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('notifications', [
            'tenant_id' => $this->tenant->id,
            'type' => 'whatsapp',
        ]);
    }

    public function test_can_export_students_csv(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession([
                'active_tenant_id' => $this->tenant->id,
                'active_branch_id' => $this->branch->id,
            ])
            ->get('/students/export/csv');

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
    }

    public function test_can_export_financial_ledger_csv(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession([
                'active_tenant_id' => $this->tenant->id,
                'active_branch_id' => $this->branch->id,
            ])
            ->get('/reports/export/financial-csv');

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
    }
}
