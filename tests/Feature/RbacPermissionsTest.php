<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_super_admin_can_access_all_modules_including_saas_tenants(): void
    {
        $superAdmin = User::withoutGlobalScopes()->where('email', 'superadmin@thrumble.driving-school')->first();

        $this->assertTrue($superAdmin->hasRole('super_admin'));

        $this->actingAs($superAdmin)->get('/superadmin/tenants')->assertStatus(200);
        $this->actingAs($superAdmin)->get('/dashboard')->assertStatus(200);
    }

    public function test_business_owner_can_manage_tenant_but_cannot_access_superadmin(): void
    {
        $owner = User::where('email', 'owner@thrumble.driving-school')->first();

        $this->assertTrue($owner->hasRole('business_owner'));

        $this->actingAs($owner)->get('/dashboard')->assertStatus(200);
        $this->actingAs($owner)->get('/users')->assertStatus(200);
        $this->actingAs($owner)->get('/branches')->assertStatus(200);
        $this->actingAs($owner)->get('/settings/company')->assertStatus(200);
        $this->actingAs($owner)->get('/reports')->assertStatus(200);

        // Forbidden: Platform Super Admin panel
        $this->actingAs($owner)->get('/superadmin/tenants')->assertStatus(403);
    }

    public function test_branch_manager_can_run_operations_but_cannot_manage_users_branches_settings_or_superadmin(): void
    {
        $manager = User::where('email', 'manager@thrumble.driving-school')->first();

        $this->assertTrue($manager->hasRole('branch_manager'));

        $this->actingAs($manager)->get('/dashboard')->assertStatus(200);
        $this->actingAs($manager)->get('/crm/leads')->assertStatus(200);
        $this->actingAs($manager)->get('/schedules')->assertStatus(200);
        $this->actingAs($manager)->get('/vehicles')->assertStatus(200);
        $this->actingAs($manager)->get('/billing/invoices')->assertStatus(200);

        // Forbidden admin modules
        $this->actingAs($manager)->get('/users')->assertStatus(403);
        $this->actingAs($manager)->get('/branches')->assertStatus(403);
        $this->actingAs($manager)->get('/settings/company')->assertStatus(403);
        $this->actingAs($manager)->get('/superadmin/tenants')->assertStatus(403);
    }

    public function test_admission_executive_access_boundaries(): void
    {
        $admissions = User::where('email', 'admissions@thrumble.driving-school')->first();

        $this->assertTrue($admissions->hasRole('admission_executive'));

        $this->actingAs($admissions)->get('/dashboard')->assertStatus(200);
        $this->actingAs($admissions)->get('/crm/leads')->assertStatus(200);
        $this->actingAs($admissions)->get('/admissions/create')->assertStatus(200);
        $this->actingAs($admissions)->get('/students')->assertStatus(200);
        $this->actingAs($admissions)->get('/billing/invoices')->assertStatus(200);

        // Forbidden modules
        $this->actingAs($admissions)->get('/billing/expenses')->assertStatus(403);
        $this->actingAs($admissions)->get('/reports')->assertStatus(403);
        $this->actingAs($admissions)->get('/users')->assertStatus(403);
        $this->actingAs($admissions)->get('/branches')->assertStatus(403);
        $this->actingAs($admissions)->get('/superadmin/tenants')->assertStatus(403);
    }

    public function test_instructor_access_boundaries(): void
    {
        $instructor = User::where('email', 'rajesh.instructor@thrumble.driving-school')->first();

        $this->assertTrue($instructor->hasRole('instructor'));

        $this->actingAs($instructor)->get('/dashboard')->assertStatus(200);
        $this->actingAs($instructor)->get('/schedules')->assertStatus(200);
        $this->actingAs($instructor)->get('/schedules/calendar')->assertStatus(200);
        $this->actingAs($instructor)->get('/training/progress')->assertStatus(200);
        $this->actingAs($instructor)->get('/training/theory')->assertStatus(200);
        $this->actingAs($instructor)->get('/vehicles')->assertStatus(200);

        // Forbidden modules
        $this->actingAs($instructor)->get('/crm/leads')->assertStatus(403);
        $this->actingAs($instructor)->get('/admissions/create')->assertStatus(403);
        $this->actingAs($instructor)->get('/billing/invoices')->assertStatus(403);
        $this->actingAs($instructor)->get('/billing/expenses')->assertStatus(403);
        $this->actingAs($instructor)->get('/reports')->assertStatus(403);
        $this->actingAs($instructor)->get('/users')->assertStatus(403);
        $this->actingAs($instructor)->get('/branches')->assertStatus(403);
        $this->actingAs($instructor)->get('/settings/company')->assertStatus(403);
        $this->actingAs($instructor)->get('/superadmin/tenants')->assertStatus(403);
    }

    public function test_student_is_restricted_to_own_portal_and_theory_hub(): void
    {
        $studentUser = User::where('email', 'student@thrumble.driving-school')->first();
        $otherStudent = Student::where('user_id', '!=', $studentUser->id)->orWhereNull('user_id')->first();

        $this->assertTrue($studentUser->hasRole('student'));

        // Quick-login redirects student directly to student portal
        $this->post('/quick-login', ['email' => 'student@thrumble.driving-school'])
            ->assertRedirect(route('student.portal'));

        // Allowed routes
        $portalResponse = $this->actingAs($studentUser)->get('/student/portal');
        $portalResponse->assertStatus(200);
        $portalResponse->assertSee('Arjun Verma');
        $portalResponse->assertDontSee('Staff Switcher');

        // Attempting to view another student via query param still only shows Arjun's profile
        if ($otherStudent) {
            $tamperedResponse = $this->actingAs($studentUser)->get('/student/portal?student_id=' . $otherStudent->id);
            $tamperedResponse->assertStatus(200);
            $tamperedResponse->assertSee('Arjun Verma');
            $tamperedResponse->assertDontSee($otherStudent->full_name);
        }

        $this->actingAs($studentUser)->get('/training/theory')->assertStatus(200);

        // Forbidden ERP routes
        $this->actingAs($studentUser)->get('/dashboard')->assertStatus(403);
        $this->actingAs($studentUser)->get('/students')->assertStatus(403);
        $this->actingAs($studentUser)->get('/crm/leads')->assertStatus(403);
        $this->actingAs($studentUser)->get('/billing/invoices')->assertStatus(403);
        $this->actingAs($studentUser)->get('/users')->assertStatus(403);
        $this->actingAs($studentUser)->get('/branches')->assertStatus(403);
        $this->actingAs($studentUser)->get('/settings/company')->assertStatus(403);
        $this->actingAs($studentUser)->get('/superadmin/tenants')->assertStatus(403);
    }
}
