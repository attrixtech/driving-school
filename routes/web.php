<?php

use App\Http\Controllers\AdmissionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LessonSchedulerController;
use App\Http\Controllers\LicensingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PublicPortalController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

// Public portal & Online enquiry
Route::get('/', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    $user = auth()->user();
    if (!$user->can('dashboard.view') && $user->can('student.portal')) {
        return redirect()->route('student.portal');
    }

    return redirect()->route('dashboard');
});

Route::get('/portal', [PublicPortalController::class, 'portal'])->name('public.portal');
Route::post('/portal/enquiry', [PublicPortalController::class, 'submitEnquiry'])->name('public.enquiry.submit');
Route::get('/certificates/verify/{code}', [CertificateController::class, 'verify'])->name('certificates.verify');

// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/quick-login', [AuthController::class, 'quickLogin'])->name('auth.quick-login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated ERP Core Routes (Protected by Spatie Roles & Permissions)
Route::middleware('auth')->group(function () {

    // Switch active branch
    Route::post('/branch/{branch}/switch', [AuthController::class, 'switchBranch'])
        ->middleware('permission:dashboard.view')
        ->name('branch.switch');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard');

    // CRM & Leads
    Route::middleware('permission:leads.view')->group(function () {
        Route::get('/crm/leads', [LeadController::class, 'index'])->name('crm.leads');
        Route::get('/crm/leads/{lead}/logs', [LeadController::class, 'getLogs'])->name('crm.leads.logs');
    });
    Route::middleware('permission:leads.manage')->group(function () {
        Route::post('/crm/leads', [LeadController::class, 'store'])->name('crm.leads.store');
        Route::post('/crm/leads/{lead}/followup', [LeadController::class, 'addFollowup'])->name('crm.leads.followup');
    });

    // Admissions
    Route::middleware('permission:admissions.create')->group(function () {
        Route::get('/admissions/create', [AdmissionController::class, 'create'])->name('admissions.create');
        Route::post('/admissions', [AdmissionController::class, 'store'])->name('admissions.store');
    });
    Route::middleware('permission:admissions.view')->group(function () {
        Route::get('/admissions/{admission}', [AdmissionController::class, 'show'])->name('admissions.show');
        Route::get('/admissions/{admission}/print', [AdmissionController::class, 'print'])->name('admissions.print');
    });

    // Students Directory & Self-Service
    Route::get('/student/portal', [StudentController::class, 'myTraining'])
        ->middleware('permission:student.portal')
        ->name('student.portal');
    Route::get('/students/export/csv', [StudentController::class, 'exportCsv'])
        ->middleware('permission:students.export')
        ->name('students.export.csv');
    Route::middleware('permission:students.view')->group(function () {
        Route::get('/students', [StudentController::class, 'index'])->name('students.index');
        Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show');
    });
    Route::middleware('permission:students.manage')->group(function () {
        Route::post('/students/{student}/documents', [StudentController::class, 'uploadDocument'])->name('students.documents.upload');
        Route::post('/students/documents/{document}/verify', [StudentController::class, 'verifyDocument'])->name('students.documents.verify');
    });
    Route::post('/students/{student}/license', [StudentController::class, 'updateLicense'])
        ->middleware('permission:licensing.manage')
        ->name('students.license.update');

    // Smart Scheduling
    Route::middleware('permission:schedules.view')->group(function () {
        Route::get('/schedules', [LessonSchedulerController::class, 'index'])->name('schedules.index');
        Route::get('/schedules/calendar', [LessonSchedulerController::class, 'calendar'])->name('schedules.calendar');
    });
    Route::middleware('permission:schedules.manage')->group(function () {
        Route::get('/schedules/create', [LessonSchedulerController::class, 'create'])->name('schedules.create');
        Route::post('/schedules', [LessonSchedulerController::class, 'store'])->name('schedules.store');
    });
    Route::middleware('permission:schedules.complete')->group(function () {
        Route::get('/schedules/{schedule}/complete', [LessonSchedulerController::class, 'completeForm'])->name('schedules.complete-form');
        Route::post('/schedules/{schedule}/complete', [LessonSchedulerController::class, 'complete'])->name('schedules.complete');
    });

    // Training & Progress
    Route::middleware('permission:training.view')->group(function () {
        Route::get('/training/progress', [TrainingController::class, 'progress'])->name('training.progress');
        Route::get('/training/mocktests', [TrainingController::class, 'mocktests'])->name('training.mocktests');
        Route::get('/training/theory', [TrainingController::class, 'theory'])->name('training.theory');
    });
    Route::middleware('permission:training.manage')->group(function () {
        Route::post('/training/mocktests', [TrainingController::class, 'storeMockTest'])->name('training.mocktests.store');
        Route::post('/training/theory/classes', [TrainingController::class, 'storeTheoryClass'])->name('training.theory.classes.store');
        Route::post('/training/theory/classes/{class}/attend', [TrainingController::class, 'attendTheoryClass'])->name('training.theory.classes.attend');
    });

    // Instructors
    Route::get('/instructors', [InstructorController::class, 'index'])
        ->middleware('permission:instructors.view')
        ->name('instructors.index');
    Route::post('/instructors', [InstructorController::class, 'store'])
        ->middleware('permission:instructors.manage')
        ->name('instructors.store');

    // Fleet & Vehicles
    Route::middleware('permission:fleet.view')->group(function () {
        Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
        Route::get('/fleet/fuel-maintenance', [VehicleController::class, 'fuelAndMaintenance'])->name('fleet.fuel-maintenance');
    });
    Route::middleware('permission:fleet.manage')->group(function () {
        Route::post('/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
        Route::post('/fleet/fuel', [VehicleController::class, 'storeFuelLog'])->name('fleet.fuel.store');
        Route::post('/fleet/maintenance', [VehicleController::class, 'storeMaintenance'])->name('fleet.maintenance.store');
    });

    // Courses & Packages
    Route::get('/courses', [CourseController::class, 'index'])
        ->middleware('permission:courses.view')
        ->name('courses.index');
    Route::middleware('permission:courses.manage')->group(function () {
        Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
        Route::post('/courses/{course}/packages', [CourseController::class, 'storePackage'])->name('courses.packages.store');
    });

    // Licensing Records
    Route::get('/licensing/records', [LicensingController::class, 'records'])
        ->middleware('permission:licensing.view')
        ->name('licensing.records');

    // Certificates
    Route::middleware('permission:certificates.view')->group(function () {
        Route::get('/certificates', [CertificateController::class, 'index'])->name('certificates.index');
        Route::get('/certificates/{certificate}', [CertificateController::class, 'show'])->name('certificates.show');
    });
    Route::post('/certificates/issue', [CertificateController::class, 'issue'])
        ->middleware('permission:certificates.issue')
        ->name('certificates.issue');

    // Finance & Billing
    Route::middleware('permission:billing.view')->group(function () {
        Route::get('/billing/invoices', [BillingController::class, 'invoices'])->name('billing.invoices');
        Route::get('/billing/invoices/{invoice}', [BillingController::class, 'showInvoice'])->name('billing.invoices.show');
        Route::get('/billing/payments', [BillingController::class, 'payments'])->name('billing.payments');
    });
    Route::post('/billing/payments', [BillingController::class, 'storePayment'])
        ->middleware('permission:billing.collect')
        ->name('billing.payments.store');
    Route::middleware('permission:billing.expenses')->group(function () {
        Route::get('/billing/expenses', [BillingController::class, 'expenses'])->name('billing.expenses');
        Route::post('/billing/expenses', [BillingController::class, 'storeExpense'])->name('billing.expenses.store');
    });

    // Reports & BI
    Route::get('/reports', [ReportController::class, 'index'])
        ->middleware('permission:reports.view')
        ->name('reports.index');
    Route::get('/reports/export/financial-csv', [ReportController::class, 'exportFinancialCsv'])
        ->middleware('permission:reports.export')
        ->name('reports.export.financial-csv');

    // Notifications Gateway Hub
    Route::middleware('permission:notifications.view')->group(function () {
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    });
    Route::post('/notifications/send', [NotificationController::class, 'send'])
        ->middleware('permission:notifications.send')
        ->name('notifications.send');

    // User & Staff Management
    Route::middleware('permission:users.manage')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::post('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/toggle', [UserController::class, 'toggleStatus'])->name('users.toggle');
        Route::post('/users/{user}/password', [UserController::class, 'resetPassword'])->name('users.password');
    });

    // Multi-Branch Management
    Route::middleware('permission:branches.manage')->group(function () {
        Route::get('/branches', [BranchController::class, 'index'])->name('branches.index');
        Route::post('/branches', [BranchController::class, 'store'])->name('branches.store');
        Route::post('/branches/{branch}', [BranchController::class, 'update'])->name('branches.update');
        Route::post('/branches/{branch}/toggle', [BranchController::class, 'toggleStatus'])->name('branches.toggle');
        Route::post('/branches/{branch}/set-main', [BranchController::class, 'setMain'])->name('branches.set-main');
    });

    // SaaS Platform Super-Admin (Multi-Tenant Management)
    Route::middleware('role:super_admin')->group(function () {
        Route::get('/superadmin/tenants', [SuperAdminController::class, 'tenants'])->name('superadmin.tenants');
        Route::post('/superadmin/tenants', [SuperAdminController::class, 'storeTenant'])->name('superadmin.tenants.store');
        Route::post('/superadmin/tenants/{tenant}/subscription', [SuperAdminController::class, 'updateSubscription'])->name('superadmin.tenants.subscription');
    });

    // Settings
    Route::middleware('permission:settings.manage')->group(function () {
        Route::get('/settings/company', [SettingController::class, 'company'])->name('settings.company');
        Route::post('/settings/company', [SettingController::class, 'updateCompany'])->name('settings.company.update');
        Route::post('/settings/branches', [SettingController::class, 'storeBranch'])->name('settings.branches.store');
    });
});
