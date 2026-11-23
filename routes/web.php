<?php

use App\Http\Controllers\AdmissionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LessonSchedulerController;
use App\Http\Controllers\LicensingController;
use App\Http\Controllers\PublicPortalController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

// Public portal & Online enquiry
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::get('/portal', [PublicPortalController::class, 'portal'])->name('public.portal');
Route::post('/portal/enquiry', [PublicPortalController::class, 'submitEnquiry'])->name('public.enquiry.submit');
Route::get('/certificates/verify/{code}', [CertificateController::class, 'verify'])->name('certificates.verify');

// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/quick-login', [AuthController::class, 'quickLogin'])->name('auth.quick-login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated ERP Core Routes
Route::middleware('auth')->group(function () {
    
    // Switch active branch
    Route::post('/branch/{branch}/switch', [AuthController::class, 'switchBranch'])->name('branch.switch');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // CRM & Leads
    Route::get('/crm/leads', [LeadController::class, 'index'])->name('crm.leads');
    Route::post('/crm/leads', [LeadController::class, 'store'])->name('crm.leads.store');
    Route::post('/crm/leads/{lead}/followup', [LeadController::class, 'addFollowup'])->name('crm.leads.followup');

    // Admissions
    Route::get('/admissions/create', [AdmissionController::class, 'create'])->name('admissions.create');
    Route::post('/admissions', [AdmissionController::class, 'store'])->name('admissions.store');
    Route::get('/admissions/{admission}', [AdmissionController::class, 'show'])->name('admissions.show');
    Route::get('/admissions/{admission}/print', [AdmissionController::class, 'print'])->name('admissions.print');

    // Students Directory
    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show');
    Route::post('/students/{student}/documents', [StudentController::class, 'uploadDocument'])->name('students.documents.upload');
    Route::post('/students/documents/{document}/verify', [StudentController::class, 'verifyDocument'])->name('students.documents.verify');
    Route::post('/students/{student}/license', [StudentController::class, 'updateLicense'])->name('students.license.update');

    // Smart Scheduling
    Route::get('/schedules', [LessonSchedulerController::class, 'index'])->name('schedules.index');
    Route::get('/schedules/calendar', [LessonSchedulerController::class, 'calendar'])->name('schedules.calendar');
    Route::get('/schedules/create', [LessonSchedulerController::class, 'create'])->name('schedules.create');
    Route::post('/schedules', [LessonSchedulerController::class, 'store'])->name('schedules.store');
    Route::get('/schedules/{schedule}/complete', [LessonSchedulerController::class, 'completeForm'])->name('schedules.complete-form');
    Route::post('/schedules/{schedule}/complete', [LessonSchedulerController::class, 'complete'])->name('schedules.complete');

    // Training & Progress
    Route::get('/training/progress', [TrainingController::class, 'progress'])->name('training.progress');
    Route::get('/training/mocktests', [TrainingController::class, 'mocktests'])->name('training.mocktests');
    Route::post('/training/mocktests', [TrainingController::class, 'storeMockTest'])->name('training.mocktests.store');

    // Instructors
    Route::get('/instructors', [InstructorController::class, 'index'])->name('instructors.index');
    Route::post('/instructors', [InstructorController::class, 'store'])->name('instructors.store');

    // Fleet & Vehicles
    Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
    Route::post('/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
    Route::get('/fleet/fuel-maintenance', [VehicleController::class, 'fuelAndMaintenance'])->name('fleet.fuel-maintenance');
    Route::post('/fleet/fuel', [VehicleController::class, 'storeFuelLog'])->name('fleet.fuel.store');
    Route::post('/fleet/maintenance', [VehicleController::class, 'storeMaintenance'])->name('fleet.maintenance.store');

    // Courses & Packages
    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
    Route::post('/courses/{course}/packages', [CourseController::class, 'storePackage'])->name('courses.packages.store');

    // Licensing Records
    Route::get('/licensing/records', [LicensingController::class, 'records'])->name('licensing.records');

    // Certificates
    Route::get('/certificates', [CertificateController::class, 'index'])->name('certificates.index');
    Route::post('/certificates/issue', [CertificateController::class, 'issue'])->name('certificates.issue');
    Route::get('/certificates/{certificate}', [CertificateController::class, 'show'])->name('certificates.show');

    // Finance & Billing
    Route::get('/billing/invoices', [BillingController::class, 'invoices'])->name('billing.invoices');
    Route::get('/billing/invoices/{invoice}', [BillingController::class, 'showInvoice'])->name('billing.invoices.show');
    Route::get('/billing/payments', [BillingController::class, 'payments'])->name('billing.payments');
    Route::post('/billing/payments', [BillingController::class, 'storePayment'])->name('billing.payments.store');
    Route::get('/billing/expenses', [BillingController::class, 'expenses'])->name('billing.expenses');
    Route::post('/billing/expenses', [BillingController::class, 'storeExpense'])->name('billing.expenses.store');

    // Reports & BI
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // Settings
    Route::get('/settings/company', [SettingController::class, 'company'])->name('settings.company');
    Route::post('/settings/company', [SettingController::class, 'updateCompany'])->name('settings.company.update');
    Route::post('/settings/branches', [SettingController::class, 'storeBranch'])->name('settings.branches.store');
});
