<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public const PERMISSIONS = [
        'dashboard.view',
        'leads.view',
        'leads.manage',
        'admissions.view',
        'admissions.create',
        'students.view',
        'students.manage',
        'students.export',
        'student.portal',
        'schedules.view',
        'schedules.manage',
        'schedules.complete',
        'training.view',
        'training.manage',
        'instructors.view',
        'instructors.manage',
        'fleet.view',
        'fleet.manage',
        'courses.view',
        'courses.manage',
        'licensing.view',
        'licensing.manage',
        'certificates.view',
        'certificates.issue',
        'billing.view',
        'billing.collect',
        'billing.expenses',
        'reports.view',
        'reports.export',
        'notifications.view',
        'notifications.send',
        'users.manage',
        'branches.manage',
        'settings.manage',
        'superadmin.manage',
    ];

    public const ROLE_PERMISSIONS = [
        'super_admin' => self::PERMISSIONS,
        'business_owner' => [
            'dashboard.view',
            'leads.view', 'leads.manage',
            'admissions.view', 'admissions.create',
            'students.view', 'students.manage', 'students.export', 'student.portal',
            'schedules.view', 'schedules.manage', 'schedules.complete',
            'training.view', 'training.manage',
            'instructors.view', 'instructors.manage',
            'fleet.view', 'fleet.manage',
            'courses.view', 'courses.manage',
            'licensing.view', 'licensing.manage',
            'certificates.view', 'certificates.issue',
            'billing.view', 'billing.collect', 'billing.expenses',
            'reports.view', 'reports.export',
            'notifications.view', 'notifications.send',
            'users.manage',
            'branches.manage',
            'settings.manage',
        ],
        'school_admin' => [
            'dashboard.view',
            'leads.view', 'leads.manage',
            'admissions.view', 'admissions.create',
            'students.view', 'students.manage', 'students.export', 'student.portal',
            'schedules.view', 'schedules.manage', 'schedules.complete',
            'training.view', 'training.manage',
            'instructors.view', 'instructors.manage',
            'fleet.view', 'fleet.manage',
            'courses.view', 'courses.manage',
            'licensing.view', 'licensing.manage',
            'certificates.view', 'certificates.issue',
            'billing.view', 'billing.collect', 'billing.expenses',
            'reports.view', 'reports.export',
            'notifications.view', 'notifications.send',
            'users.manage',
            'branches.manage',
            'settings.manage',
        ],
        'branch_manager' => [
            'dashboard.view',
            'leads.view', 'leads.manage',
            'admissions.view', 'admissions.create',
            'students.view', 'students.manage', 'students.export', 'student.portal',
            'schedules.view', 'schedules.manage', 'schedules.complete',
            'training.view', 'training.manage',
            'instructors.view', 'instructors.manage',
            'fleet.view', 'fleet.manage',
            'courses.view', 'courses.manage',
            'licensing.view', 'licensing.manage',
            'certificates.view', 'certificates.issue',
            'billing.view', 'billing.collect', 'billing.expenses',
            'reports.view', 'reports.export',
            'notifications.view', 'notifications.send',
        ],
        'admission_executive' => [
            'dashboard.view',
            'leads.view', 'leads.manage',
            'admissions.view', 'admissions.create',
            'students.view', 'students.manage', 'student.portal',
            'courses.view',
            'licensing.view', 'licensing.manage',
            'certificates.view',
            'billing.view', 'billing.collect',
            'notifications.view', 'notifications.send',
        ],
        'receptionist' => [
            'dashboard.view',
            'leads.view', 'leads.manage',
            'admissions.view', 'admissions.create',
            'students.view',
            'schedules.view', 'schedules.manage',
            'courses.view',
            'billing.view', 'billing.collect',
            'notifications.view',
        ],
        'instructor' => [
            'dashboard.view',
            'students.view',
            'schedules.view', 'schedules.complete',
            'training.view', 'training.manage',
            'fleet.view',
            'certificates.view',
        ],
        'accountant' => [
            'dashboard.view',
            'students.view',
            'billing.view', 'billing.collect', 'billing.expenses',
            'reports.view', 'reports.export',
        ],
        'student' => [
            'student.portal',
            'training.view',
        ],
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach (self::ROLE_PERMISSIONS as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($permissions);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
