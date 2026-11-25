<?php

namespace Database\Seeders;

use App\Models\Admission;
use App\Models\Branch;
use App\Models\Certificate;
use App\Models\Company;
use App\Models\Course;
use App\Models\CoursePackage;
use App\Models\Expense;
use App\Models\Instructor;
use App\Models\InstructorAvailability;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\LessonAttendance;
use App\Models\LessonSchedule;
use App\Models\MockTest;
use App\Models\Notification;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\StudentProgress;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\VehicleDocument;
use App\Models\VehicleFuelLog;
use App\Models\VehicleMaintenance;
use App\Services\AdmissionService;
use App\Services\BillingService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. ROLES & PERMISSIONS (Spatie)
        $this->call(RolePermissionSeeder::class);

        // 2. PLATFORM SUPER ADMIN
        $superAdmin = User::create([
            'tenant_id' => null,
            'branch_id' => null,
            'name' => 'thrumble.driving-school Master Admin',
            'email' => 'superadmin@thrumble.driving-school',
            'phone' => '+91 98370 00000',
            'password' => Hash::make('password123'),
            'role_slug' => 'super_admin',
            'is_active' => true,
        ]);
        $superAdmin->syncRoles(['super_admin']);

        // 3. TENANT (thrumble.driving-school)
        $tenant = Tenant::create([
            'name' => 'thrumble.driving-school',
            'slug' => 'thrumble-driving-school',
            'domain' => 'thrumble.driving-school',
            'contact_person' => 'Vikramaditya Rao',
            'email' => 'owner@thrumble.driving-school',
            'phone' => '+91 98370 43210',
            'status' => 'active',
            'subscription_plan' => 'premium',
            'subscription_status' => 'active',
            'subscription_ends_at' => now()->addYear(),
            'max_branches' => 5,
            'max_students' => 2000,
            'max_vehicles' => 50,
            'settings' => [
                'currency' => 'INR',
                'timezone' => 'Asia/Kolkata',
                'gst_rate' => 18,
                'allow_student_portal' => true,
            ],
        ]);

        session(['active_tenant_id' => $tenant->id]);

        // 4. COMPANY PROFILE
        $company = Company::create([
            'tenant_id' => $tenant->id,
            'legal_name' => 'thrumble.driving-school Pvt Ltd',
            'trade_name' => 'thrumble.driving-school',
            'registration_number' => 'U74999UP2018PTC112345',
            'gstin' => '09AABCT1234F1Z8',
            'phone' => '+91 562 401 2345',
            'email' => 'contact@thrumble.driving-school',
            'website' => 'https://thrumble.driving-school',
            'address' => 'Plot 42, Block B, Sanjay Place, Civil Lines',
            'city' => 'Agra',
            'state' => 'Uttar Pradesh',
            'postal_code' => '282002',
            'country' => 'India',
            'currency' => 'INR',
            'currency_symbol' => '₹',
        ]);

        // 5. BRANCHES
        $branchMain = Branch::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'name' => 'Sanjay Place Main Branch',
            'code' => 'SANJAY-01',
            'phone' => '+91 562 401 2345',
            'email' => 'sanjayplace@thrumble.driving-school',
            'address' => 'Plot 42, Block B, Sanjay Place, Civil Lines',
            'city' => 'Agra',
            'state' => 'Uttar Pradesh',
            'postal_code' => '282002',
            'is_main_branch' => true,
            'is_active' => true,
        ]);

        $branchKamlaNagar = Branch::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'name' => 'Kamla Nagar Branch',
            'code' => 'KAMLA-02',
            'phone' => '+91 562 402 6789',
            'email' => 'kamlanagar@thrumble.driving-school',
            'address' => '14/2, Main Market Road, Kamla Nagar',
            'city' => 'Agra',
            'state' => 'Uttar Pradesh',
            'postal_code' => '282005',
            'is_main_branch' => false,
            'is_active' => true,
        ]);

        // 6. USERS
        $owner = User::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMain->id,
            'name' => 'Vikramaditya Rao (Owner)',
            'email' => 'owner@thrumble.driving-school',
            'phone' => '+91 98370 43210',
            'password' => Hash::make('password123'),
            'role_slug' => 'business_owner',
            'is_active' => true,
        ]);
        $owner->syncRoles(['business_owner']);

        $manager = User::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branchKamlaNagar->id,
            'name' => 'Arvind Swamy (Manager)',
            'email' => 'manager@thrumble.driving-school',
            'phone' => '+91 98370 43211',
            'password' => Hash::make('password123'),
            'role_slug' => 'branch_manager',
            'is_active' => true,
        ]);
        $manager->syncRoles(['branch_manager']);

        $admissionExec = User::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMain->id,
            'name' => 'Kavita Menon (Admissions)',
            'email' => 'admissions@thrumble.driving-school',
            'phone' => '+91 98370 43212',
            'password' => Hash::make('password123'),
            'role_slug' => 'admission_executive',
            'is_active' => true,
        ]);
        $admissionExec->syncRoles(['admission_executive']);

        // 7. VEHICLE CATEGORIES
        $catCar = VehicleCategory::create([
            'tenant_id' => $tenant->id,
            'name' => 'Four Wheeler (LMV)',
            'code' => 'LMV',
            'license_class' => 'Light Motor Vehicle',
            'description' => 'Hatchback, Sedan and SUV 4-wheelers',
            'icon' => 'car',
            'is_active' => true,
        ]);

        $catBike = VehicleCategory::create([
            'tenant_id' => $tenant->id,
            'name' => 'Two Wheeler (MCWG)',
            'code' => 'MCWG',
            'license_class' => 'Motor Cycle With Gear',
            'description' => 'Motorcycles and Scooters with/without gear',
            'icon' => 'bike',
            'is_active' => true,
        ]);

        $catAuto = VehicleCategory::create([
            'tenant_id' => $tenant->id,
            'name' => 'Automatic Four Wheeler (LMV-A)',
            'code' => 'LMV-A',
            'license_class' => 'Light Motor Vehicle - Automatic',
            'description' => 'Automatic transmission cars with dual pedals',
            'icon' => 'car',
            'is_active' => true,
        ]);

        // 8. COURSES & PACKAGES
        $courseCar = Course::create([
            'tenant_id' => $tenant->id,
            'vehicle_category_id' => $catCar->id,
            'code' => 'CRS-LMV-01',
            'name' => 'LMV Four-Wheeler Comprehensive Driving',
            'description' => 'Complete beginner car driving training covering city roads, highways, reverse parking, and slope control.',
            'transmission_type' => 'manual',
            'duration_days' => 25,
            'total_practical_hours' => 15,
            'total_theory_hours' => 5,
            'default_fee' => 7500.00,
            'has_simulator' => true,
            'is_active' => true,
        ]);

        $pkgCarStandard = CoursePackage::create([
            'tenant_id' => $tenant->id,
            'course_id' => $courseCar->id,
            'code' => 'PKG-LMV-STD',
            'name' => 'Standard Beginner Package (21 Days)',
            'description' => '15 practical sessions (1 hr each) + 5 theory classes + Mock test.',
            'duration_days' => 21,
            'practical_sessions_count' => 15,
            'theory_sessions_count' => 5,
            'package_fee' => 7500.00,
            'includes_ll_assistance' => true,
            'includes_dl_test_assistance' => true,
            'includes_doorstep_pickup' => false,
            'is_active' => true,
        ]);

        $pkgCarVIP = CoursePackage::create([
            'tenant_id' => $tenant->id,
            'course_id' => $courseCar->id,
            'code' => 'PKG-LMV-VIP',
            'name' => 'VIP Doorstep Package (30 Days)',
            'description' => '20 practical sessions + doorstep pickup & drop + dedicated instructor + guaranteed RTO test assistance.',
            'duration_days' => 30,
            'practical_sessions_count' => 20,
            'theory_sessions_count' => 5,
            'package_fee' => 12500.00,
            'includes_ll_assistance' => true,
            'includes_dl_test_assistance' => true,
            'includes_doorstep_pickup' => true,
            'is_active' => true,
        ]);

        $courseBike = Course::create([
            'tenant_id' => $tenant->id,
            'vehicle_category_id' => $catBike->id,
            'code' => 'CRS-MCWG-01',
            'name' => 'Two-Wheeler Motorcycle & Scooter Training',
            'description' => 'Master balance, clutch control, gear shifting, figure-8 track test, and defensive riding.',
            'transmission_type' => 'manual',
            'duration_days' => 15,
            'total_practical_hours' => 10,
            'total_theory_hours' => 3,
            'default_fee' => 4500.00,
            'has_simulator' => false,
            'is_active' => true,
        ]);

        $pkgBike = CoursePackage::create([
            'tenant_id' => $tenant->id,
            'course_id' => $courseBike->id,
            'code' => 'PKG-MCWG-STD',
            'name' => 'Motorcycle Foundation Package',
            'description' => '10 practical riding sessions + RTO track test preparation.',
            'duration_days' => 15,
            'practical_sessions_count' => 10,
            'theory_sessions_count' => 3,
            'package_fee' => 4500.00,
            'includes_ll_assistance' => true,
            'includes_dl_test_assistance' => true,
            'includes_doorstep_pickup' => false,
            'is_active' => true,
        ]);

        // 9. INSTRUCTORS
        $instUser1 = User::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMain->id,
            'name' => 'Rajesh Sharma',
            'email' => 'rajesh.instructor@thrumble.driving-school',
            'phone' => '+91 98370 11223',
            'password' => Hash::make('password123'),
            'role_slug' => 'instructor',
            'is_active' => true,
        ]);
        $instUser1->syncRoles(['instructor']);

        $instructor1 = Instructor::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMain->id,
            'user_id' => $instUser1->id,
            'instructor_code' => 'INST-001',
            'name' => 'Rajesh Sharma',
            'phone' => '+91 98370 11223',
            'email' => 'rajesh.instructor@thrumble.driving-school',
            'license_number' => 'UP-80-2012-0019845',
            'license_expiry_date' => now()->addYears(3),
            'experience_years' => 9,
            'vehicle_types' => ['LMV', 'MCWG'],
            'status' => 'active',
            'hourly_rate' => 250.00,
            'monthly_salary' => 28000.00,
            'rating' => 4.90,
            'address' => 'Sanjay Place, Civil Lines, Agra',
        ]);

        $instUser2 = User::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMain->id,
            'name' => 'Suresh Kumar',
            'email' => 'suresh.instructor@thrumble.driving-school',
            'phone' => '+91 98370 44556',
            'password' => Hash::make('password123'),
            'role_slug' => 'instructor',
            'is_active' => true,
        ]);
        $instUser2->syncRoles(['instructor']);

        $instructor2 = Instructor::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMain->id,
            'user_id' => $instUser2->id,
            'instructor_code' => 'INST-002',
            'name' => 'Suresh Kumar',
            'phone' => '+91 98370 44556',
            'email' => 'suresh.instructor@thrumble.driving-school',
            'license_number' => 'UP-80-2016-0045892',
            'license_expiry_date' => now()->addYears(2),
            'experience_years' => 6,
            'vehicle_types' => ['LMV'],
            'status' => 'active',
            'hourly_rate' => 220.00,
            'monthly_salary' => 25000.00,
            'rating' => 4.80,
            'address' => 'Khandari Crossing, Agra',
        ]);

        $instructor3 = Instructor::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branchKamlaNagar->id,
            'user_id' => null,
            'instructor_code' => 'INST-003',
            'name' => 'Priya Patel',
            'phone' => '+91 98370 77889',
            'email' => 'priya.instructor@thrumble.driving-school',
            'license_number' => 'UP-80-2018-0089123',
            'license_expiry_date' => now()->addDays(20), // Alert: Expiring soon!
            'experience_years' => 5,
            'vehicle_types' => ['LMV-A', 'MCWG'],
            'status' => 'active',
            'hourly_rate' => 240.00,
            'monthly_salary' => 26000.00,
            'rating' => 4.95,
            'address' => 'Kamla Nagar Block C, Agra',
        ]);

        // Instructor availabilities (Mon-Sat 7:00 to 18:00)
        foreach ([$instructor1, $instructor2, $instructor3] as $inst) {
            for ($day = 1; $day <= 6; $day++) {
                InstructorAvailability::create([
                    'tenant_id' => $tenant->id,
                    'instructor_id' => $inst->id,
                    'day_of_week' => $day,
                    'start_time' => '07:00:00',
                    'end_time' => '18:00:00',
                    'is_available' => true,
                ]);
            }
        }

        // 10. VEHICLES
        $veh1 = Vehicle::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMain->id,
            'vehicle_category_id' => $catCar->id,
            'registration_number' => 'UP-80-MJ-1001',
            'make' => 'Maruti Suzuki',
            'model' => 'Swift VXi Dual Control',
            'year' => 2023,
            'color' => 'Pearl White',
            'transmission' => 'manual',
            'fuel_type' => 'petrol',
            'dual_control_fitted' => 'yes',
            'status' => 'active',
            'odometer_reading' => 24500,
            'insurance_expiry_date' => now()->addMonths(8),
            'fitness_expiry_date' => now()->addYears(2),
            'pollution_expiry_date' => now()->addDays(12), // Alert: Expiring in 12 days!
            'permit_expiry_date' => now()->addMonths(10),
            'road_tax_expiry_date' => now()->addYears(5),
        ]);

        $veh2 = Vehicle::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMain->id,
            'vehicle_category_id' => $catCar->id,
            'registration_number' => 'UP-80-MJ-1002',
            'make' => 'Hyundai',
            'model' => 'i20 Sportz Dual Control',
            'year' => 2022,
            'color' => 'Titan Grey',
            'transmission' => 'manual',
            'fuel_type' => 'petrol',
            'dual_control_fitted' => 'yes',
            'status' => 'active',
            'odometer_reading' => 31200,
            'insurance_expiry_date' => now()->addDays(8), // Alert: Expiring in 8 days!
            'fitness_expiry_date' => now()->addMonths(14),
            'pollution_expiry_date' => now()->addMonths(4),
            'permit_expiry_date' => now()->addMonths(11),
            'road_tax_expiry_date' => now()->addYears(4),
        ]);

        $veh3 = Vehicle::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branchKamlaNagar->id,
            'vehicle_category_id' => $catAuto->id,
            'registration_number' => 'UP-80-MK-2005',
            'make' => 'Maruti Suzuki',
            'model' => 'Baleno Automatic (CVT)',
            'year' => 2024,
            'color' => 'Nexa Blue',
            'transmission' => 'automatic',
            'fuel_type' => 'petrol',
            'dual_control_fitted' => 'yes',
            'status' => 'active',
            'odometer_reading' => 8900,
            'insurance_expiry_date' => now()->addMonths(11),
            'fitness_expiry_date' => now()->addYears(3),
            'pollution_expiry_date' => now()->addMonths(5),
            'permit_expiry_date' => now()->addMonths(11),
            'road_tax_expiry_date' => now()->addYears(6),
        ]);

        $veh4 = Vehicle::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMain->id,
            'vehicle_category_id' => $catBike->id,
            'registration_number' => 'UP-80-EK-4567',
            'make' => 'Hero',
            'model' => 'Splendor Plus 100cc',
            'year' => 2022,
            'color' => 'Black with Silver',
            'transmission' => 'manual',
            'fuel_type' => 'petrol',
            'dual_control_fitted' => 'no',
            'status' => 'active',
            'odometer_reading' => 14300,
            'insurance_expiry_date' => now()->addMonths(7),
            'fitness_expiry_date' => now()->addYears(5),
            'pollution_expiry_date' => now()->addMonths(3),
        ]);

        // Vehicle Documents
        VehicleDocument::create([
            'tenant_id' => $tenant->id,
            'vehicle_id' => $veh1->id,
            'document_type' => 'pollution',
            'document_number' => 'PUC-UP80-2026-9988',
            'issue_date' => now()->subMonths(5),
            'expiry_date' => now()->addDays(12),
            'issuing_authority' => 'RTO Agra UP-80',
            'notes' => 'Emission test renewal due soon',
        ]);

        VehicleDocument::create([
            'tenant_id' => $tenant->id,
            'vehicle_id' => $veh2->id,
            'document_type' => 'insurance',
            'document_number' => 'ICICI-LOMB-987123',
            'issue_date' => now()->subYear()->addDays(8),
            'expiry_date' => now()->addDays(8),
            'issuing_authority' => 'ICICI Lombard General Insurance',
            'notes' => 'Comprehensive policy renewal due in 8 days',
        ]);

        // Fuel Logs
        VehicleFuelLog::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMain->id,
            'vehicle_id' => $veh1->id,
            'fuel_date' => now()->subDays(2),
            'liters' => 30.00,
            'cost_per_liter' => 94.70,
            'total_cost' => 2841.00,
            'odometer_reading' => 24450,
            'station_name' => 'Indian Oil Sanjay Place, Agra',
            'receipt_number' => 'IOCL-58921',
        ]);

        // Maintenance Log
        VehicleMaintenance::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMain->id,
            'vehicle_id' => $veh1->id,
            'service_type' => 'routine_service',
            'service_date' => now()->subDays(20),
            'odometer_reading' => 23800,
            'cost' => 4200.00,
            'workshop_name' => 'Agra Motors Maruti Authorized, Sikandra',
            'invoice_number' => 'AM-SRV-2026-118',
            'next_service_due_date' => now()->addMonths(5),
            'next_service_due_km' => 28800,
            'description' => 'Engine oil replacement, oil filter, brake check, dual-pedal adjustment.',
        ]);

        // 11. CRM LEADS
        $lead1 = Lead::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMain->id,
            'assigned_to_user_id' => $admissionExec->id,
            'course_id' => $courseCar->id,
            'lead_number' => 'LEAD-2026-001',
            'full_name' => 'Rohan Deshmukh',
            'phone' => '+91 98371 45678',
            'email' => 'rohan.deshmukh@gmail.com',
            'city' => 'Agra',
            'source' => 'website',
            'status' => 'interested',
            'follow_up_date' => now()->addDay(),
            'notes' => 'Interested in 21 days beginner package. Needs morning slot 7-8 AM.',
        ]);

        LeadFollowup::create([
            'tenant_id' => $tenant->id,
            'lead_id' => $lead1->id,
            'user_id' => $admissionExec->id,
            'followup_at' => now()->subDays(2)->setHour(10)->setMinute(30),
            'communication_channel' => 'call',
            'outcome' => 'connected',
            'notes' => 'Initial inquiry call from website lead. Explained course fee structure, 15 practical hours + 5 theory classes, and dual-pedal vehicle safety.',
            'next_followup_date' => now()->subDay()->toDateString(),
        ]);

        LeadFollowup::create([
            'tenant_id' => $tenant->id,
            'lead_id' => $lead1->id,
            'user_id' => $admissionExec->id,
            'followup_at' => now()->subDay()->setHour(14)->setMinute(15),
            'communication_channel' => 'whatsapp',
            'outcome' => 'connected',
            'notes' => 'Transmitted official course brochure and 18% GST fee breakdown via WhatsApp. Client confirmed preferred slot is 7:00 AM to 8:00 AM.',
            'next_followup_date' => now()->toDateString(),
        ]);

        LeadFollowup::create([
            'tenant_id' => $tenant->id,
            'lead_id' => $lead1->id,
            'user_id' => $admissionExec->id,
            'followup_at' => now()->setHour(11)->setMinute(0),
            'communication_channel' => 'call',
            'outcome' => 'callback_requested',
            'notes' => 'Discussed slot allocation with trainer Rajesh. Client requested callback tomorrow morning to finalize enrollment documents.',
            'next_followup_date' => now()->addDay()->toDateString(),
        ]);

        $lead2 = Lead::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMain->id,
            'assigned_to_user_id' => $admissionExec->id,
            'course_id' => $courseCar->id,
            'lead_number' => 'LEAD-2026-002',
            'full_name' => 'Ananya Sharma',
            'phone' => '+91 98372 56789',
            'email' => 'ananya.sharma@yahoo.com',
            'city' => 'Agra',
            'source' => 'walkin',
            'status' => 'contacted',
            'follow_up_date' => now()->toDateString(),
            'notes' => 'Walked into Sanjay Place branch inquiring about automatic car package.',
        ]);

        LeadFollowup::create([
            'tenant_id' => $tenant->id,
            'lead_id' => $lead2->id,
            'user_id' => $admissionExec->id,
            'followup_at' => now()->subDay()->setHour(16)->setMinute(45),
            'communication_channel' => 'in_person',
            'outcome' => 'connected',
            'notes' => 'Walk-in visit. Showed dual-control Maruti Swift (UP-80-MJ-1001) and simulator setup. Very enthusiastic about female trainer availability.',
            'next_followup_date' => now()->toDateString(),
        ]);

        LeadFollowup::create([
            'tenant_id' => $tenant->id,
            'lead_id' => $lead2->id,
            'user_id' => $admissionExec->id,
            'followup_at' => now()->setHour(9)->setMinute(30),
            'communication_channel' => 'call',
            'outcome' => 'connected',
            'notes' => 'Telephonic check-in. Confirmed Aadhaar and age proof requirements. She will visit branch today at 4 PM to complete admission.',
            'next_followup_date' => now()->toDateString(),
        ]);

        $lead3 = Lead::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMain->id,
            'assigned_to_user_id' => $admissionExec->id,
            'course_id' => $courseBike->id,
            'lead_number' => 'LEAD-2026-003',
            'full_name' => 'Vikram Joshi',
            'phone' => '+91 98373 67890',
            'email' => 'vikram.joshi@gmail.com',
            'city' => 'Agra',
            'source' => 'justdial',
            'status' => 'new',
            'follow_up_date' => now()->addDays(2),
            'notes' => 'Enquired via Justdial for Car + Two Wheeler combo package.',
        ]);

        LeadFollowup::create([
            'tenant_id' => $tenant->id,
            'lead_id' => $lead3->id,
            'user_id' => $admissionExec->id,
            'followup_at' => now()->subHours(3),
            'communication_channel' => 'call',
            'outcome' => 'no_answer',
            'notes' => 'First call attempt was unanswered. Sent introductory SMS with academy package fees and reception number.',
            'next_followup_date' => now()->addDays(2)->toDateString(),
        ]);

        // 12. STUDENTS & ADMISSIONS
        $admissionService = app(AdmissionService::class);
        $billingService = app(BillingService::class);

        // Student 1: Arjun Verma (Active in training)
        $studentUser1 = User::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMain->id,
            'name' => 'Arjun Verma',
            'email' => 'student@thrumble.driving-school',
            'phone' => '+91 98374 22334',
            'password' => Hash::make('password123'),
            'role_slug' => 'student',
            'is_active' => true,
        ]);
        $studentUser1->syncRoles(['student']);

        $admission1 = $admissionService->createAdmission([
            'branch_id' => $branchMain->id,
            'first_name' => 'Arjun',
            'last_name' => 'Verma',
            'phone' => '+91 98374 22334',
            'email' => 'student@thrumble.driving-school',
            'date_of_birth' => '1998-05-14',
            'gender' => 'male',
            'blood_group' => 'O+',
            'address' => 'Flat 302, Taj Heights, Civil Lines',
            'city' => 'Agra',
            'state' => 'Uttar Pradesh',
            'postal_code' => '282002',
            'guardian_name' => 'Ramesh Verma',
            'guardian_phone' => '+91 98374 99887',
            'learner_license_number' => 'UP-80-LL-2026-004128',
            'll_issue_date' => now()->subDays(15)->toDateString(),
            'll_expiry_date' => now()->addMonths(5)->toDateString(),
            'course_id' => $courseCar->id,
            'course_package_id' => $pkgCarStandard->id,
            'instructor_id' => $instructor1->id,
            'discount_amount' => 500.00,
            'initial_payment_amount' => 5000.00,
            'payment_mode' => 'upi',
            'transaction_reference' => 'UPI/2026/994411',
            'remarks' => 'Enrolled with 500 discount for festive offer.',
        ]);

        // Link student to user account
        $admission1->student->update([
            'user_id' => $studentUser1->id,
            'status' => 'in_training',
        ]);

        // Student 2: Sneha Nair (Enrolled, fully paid, automatic car)
        $admission2 = $admissionService->createAdmission([
            'branch_id' => $branchKamlaNagar->id,
            'first_name' => 'Sneha',
            'last_name' => 'Nair',
            'phone' => '+91 98375 33445',
            'email' => 'sneha.nair@gmail.com',
            'date_of_birth' => '2001-08-22',
            'gender' => 'female',
            'blood_group' => 'B+',
            'address' => 'House 14, Block D, Kamla Nagar',
            'city' => 'Agra',
            'state' => 'Uttar Pradesh',
            'postal_code' => '282005',
            'guardian_name' => 'K. Gopal Nair',
            'guardian_phone' => '+91 98375 88776',
            'learner_license_number' => 'UP-80-LL-2026-007788',
            'll_issue_date' => now()->subDays(10)->toDateString(),
            'll_expiry_date' => now()->addMonths(5)->toDateString(),
            'course_id' => $courseCar->id,
            'course_package_id' => $pkgCarVIP->id,
            'instructor_id' => $instructor3->id,
            'discount_amount' => 0.00,
            'initial_payment_amount' => 14750.00, // fully paid (12500 + 18% GST = 14750)
            'payment_mode' => 'card',
            'transaction_reference' => 'POS-HDFC-991122',
            'remarks' => 'VIP Doorstep Package fully paid.',
        ]);

        $admission2->student->update(['status' => 'in_training']);

        // Student 3: Rahul Hegde (Learner License Expiring in 10 days - for alert testing!)
        $admission3 = $admissionService->createAdmission([
            'branch_id' => $branchMain->id,
            'first_name' => 'Rahul',
            'last_name' => 'Hegde',
            'phone' => '+91 98376 44556',
            'email' => 'rahul.hegde@techcorp.in',
            'date_of_birth' => '1995-12-03',
            'gender' => 'male',
            'blood_group' => 'A+',
            'address' => '12B, Khandari Enclave, MG Road',
            'city' => 'Agra',
            'state' => 'Uttar Pradesh',
            'postal_code' => '282002',
            'learner_license_number' => 'UP-80-LL-2025-009845',
            'll_issue_date' => now()->subMonths(5)->subDays(20)->toDateString(),
            'll_expiry_date' => now()->addDays(10)->toDateString(), // ALERT: Expiring in 10 days!
            'course_id' => $courseCar->id,
            'course_package_id' => $pkgCarStandard->id,
            'instructor_id' => $instructor2->id,
            'discount_amount' => 0.00,
            'initial_payment_amount' => 4000.00,
            'payment_mode' => 'cash',
            'remarks' => 'Urgent: Learner license expiring in 10 days, needs fast-track slots.',
        ]);

        // Additional students for full academy roster
        $extraStudents = [
            ['first' => 'Priyanka', 'last' => 'Sen', 'phone' => '+91 98377 11223', 'email' => 'priyanka.sen@gmail.com', 'branch' => $branchMain->id, 'gender' => 'female'],
            ['first' => 'Karthik', 'last' => 'Rao', 'phone' => '+91 98377 22334', 'email' => 'karthik.rao@gmail.com', 'branch' => $branchKamlaNagar->id, 'gender' => 'male'],
            ['first' => 'Divya', 'last' => 'Deshmukh', 'phone' => '+91 98377 33445', 'email' => 'divya.deshmukh@gmail.com', 'branch' => $branchMain->id, 'gender' => 'female'],
            ['first' => 'Mohammed', 'last' => 'Zaid', 'phone' => '+91 98377 44556', 'email' => 'zaid.mohammed@gmail.com', 'branch' => $branchKamlaNagar->id, 'gender' => 'male'],
            ['first' => 'Ananya', 'last' => 'Iyer', 'phone' => '+91 98377 55667', 'email' => 'ananya.iyer@gmail.com', 'branch' => $branchMain->id, 'gender' => 'female'],
        ];

        foreach ($extraStudents as $es) {
            $admissionService->createAdmission([
                'tenant_id' => $tenant->id,
                'branch_id' => $es['branch'],
                'first_name' => $es['first'],
                'last_name' => $es['last'],
                'phone' => $es['phone'],
                'email' => $es['email'],
                'gender' => $es['gender'],
                'course_id' => $courseCar->id,
                'course_package_id' => $pkgCarStandard->id,
                'total_amount' => 8500.00,
                'initial_payment_amount' => 8500.00,
                'payment_mode' => 'upi',
                'remarks' => 'Enrolled with standard package.',
            ]);
        }

        // Student Documents
        StudentDocument::create([
            'tenant_id' => $tenant->id,
            'student_id' => $admission1->student_id,
            'document_type' => 'aadhaar',
            'document_number' => 'XXXX-XXXX-9012',
            'file_path' => 'documents/students/aadhaar_arjun.pdf',
            'original_filename' => 'aadhaar_card_arjun.pdf',
            'verification_status' => 'verified',
        ]);

        StudentDocument::create([
            'tenant_id' => $tenant->id,
            'student_id' => $admission1->student_id,
            'document_type' => 'll_copy',
            'document_number' => 'UP-80-LL-2026-004128',
            'file_path' => 'documents/students/ll_arjun.pdf',
            'original_filename' => 'learner_license_arjun.pdf',
            'verification_status' => 'verified',
        ]);

        // 13. LESSON SCHEDULES & ATTENDANCE
        $schedulerService = app(\App\Services\LessonSchedulerService::class);

        // Lesson 1: Completed yesterday (Practical)
        $lesson1 = LessonSchedule::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMain->id,
            'student_id' => $admission1->student_id,
            'admission_id' => $admission1->id,
            'instructor_id' => $instructor1->id,
            'vehicle_id' => $veh1->id,
            'lesson_type' => 'practical',
            'topic' => 'Cockpit Drill, Steering & First Gear Takeoff',
            'scheduled_date' => now()->subDays(2)->toDateString(),
            'start_time' => '08:00:00',
            'end_time' => '09:00:00',
            'duration_minutes' => 60,
            'status' => 'completed',
        ]);

        LessonAttendance::create([
            'tenant_id' => $tenant->id,
            'lesson_schedule_id' => $lesson1->id,
            'student_id' => $admission1->student_id,
            'instructor_id' => $instructor1->id,
            'status' => 'present',
            'actual_start_time' => '08:02:00',
            'actual_end_time' => '09:05:00',
            'start_odometer' => 24450,
            'end_odometer' => 24468,
            'kilometers_driven' => 18.00,
            'student_rating' => 5,
            'student_feedback' => 'Instructor was very patient and explained clutch biting point clearly.',
            'instructor_notes' => 'Good understanding of accelerator pedal control. Needs practice on clutch release smoothness.',
        ]);

        // Lesson 2: Completed yesterday (Practical)
        $lesson2 = LessonSchedule::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMain->id,
            'student_id' => $admission1->student_id,
            'admission_id' => $admission1->id,
            'instructor_id' => $instructor1->id,
            'vehicle_id' => $veh1->id,
            'lesson_type' => 'practical',
            'topic' => 'Gear Shifting 2nd to 3rd & Lane Discipline',
            'scheduled_date' => now()->subDay()->toDateString(),
            'start_time' => '08:00:00',
            'end_time' => '09:00:00',
            'duration_minutes' => 60,
            'status' => 'completed',
        ]);

        LessonAttendance::create([
            'tenant_id' => $tenant->id,
            'lesson_schedule_id' => $lesson2->id,
            'student_id' => $admission1->student_id,
            'instructor_id' => $instructor1->id,
            'status' => 'present',
            'actual_start_time' => '08:00:00',
            'actual_end_time' => '09:00:00',
            'start_odometer' => 24468,
            'end_odometer' => 24489,
            'kilometers_driven' => 21.00,
            'student_rating' => 5,
            'instructor_notes' => 'Smooth upshifts. Keep checking rear-view mirror during lane changes.',
        ]);

        // Lesson 3: Scheduled TODAY
        $lesson3 = LessonSchedule::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMain->id,
            'student_id' => $admission1->student_id,
            'admission_id' => $admission1->id,
            'instructor_id' => $instructor1->id,
            'vehicle_id' => $veh1->id,
            'lesson_type' => 'practical',
            'topic' => 'Reverse Parking & Mirror Alignment',
            'scheduled_date' => now()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'duration_minutes' => 60,
            'status' => 'scheduled',
            'notes' => 'Ground practice for parallel & reverse parking.',
        ]);

        // Lesson 4: Scheduled TODAY for Student 2
        $lesson4 = LessonSchedule::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branchKamlaNagar->id,
            'student_id' => $admission2->student_id,
            'admission_id' => $admission2->id,
            'instructor_id' => $instructor3->id,
            'vehicle_id' => $veh3->id,
            'lesson_type' => 'practical',
            'topic' => 'Automatic Transmission Controls & City Driving',
            'scheduled_date' => now()->toDateString(),
            'start_time' => '11:30:00',
            'end_time' => '12:30:00',
            'duration_minutes' => 60,
            'status' => 'scheduled',
        ]);

        // 14. MOCK TESTS & PROGRESS
        $mockTest = MockTest::create([
            'tenant_id' => $tenant->id,
            'student_id' => $admission1->student_id,
            'test_title' => 'RTO Traffic Signs & Mandatory Rules',
            'category' => 'road_signs',
            'total_questions' => 20,
            'correct_answers' => 17,
            'passing_score' => 12,
            'score_percentage' => 85.00,
            'is_passed' => true,
            'time_taken_seconds' => 450,
            'remarks' => 'Scored 85%. Excellent understanding of mandatory road signs.',
        ]);

        // Recalculate progress for student 1
        $progress1 = StudentProgress::where('student_id', $admission1->student_id)->first();
        if ($progress1) {
            $progress1->recalculate();
        }

        // 14.5 FORM 5 CERTIFICATE
        $certCode = 'FORM5-2026-THRUMBLE-0001';
        Certificate::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMain->id,
            'student_id' => $admission1->student_id,
            'admission_id' => $admission1->id,
            'issued_by_user_id' => $owner->id,
            'certificate_number' => $certCode,
            'verification_code' => $certCode,
            'issue_date' => now()->toDateString(),
            'course_name' => $admission1->course->name,
            'vehicle_category_code' => 'LMV',
            'practical_hours_completed' => 15.0,
            'theory_hours_completed' => 5.0,
            'final_grade' => 92.50,
            'status' => 'issued',
            'remarks' => 'Completed all Central Motor Vehicles Rule 31 practical driving maneuvers and RTO road sign competencies.',
        ]);

        // 15. EXPENSES
        Expense::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMain->id,
            'vehicle_id' => $veh1->id,
            'created_by_user_id' => $owner->id,
            'expense_category' => 'fuel',
            'title' => 'Petrol refill for Swift (UP-80-MJ-1001)',
            'expense_date' => now()->subDays(2)->toDateString(),
            'amount' => 2841.00,
            'payment_mode' => 'upi',
            'reference_number' => 'IOCL-58921',
            'paid_to' => 'Indian Oil Corporation, Agra',
            'notes' => '30 Liters @ ₹94.70/L',
        ]);

        Expense::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMain->id,
            'vehicle_id' => $veh1->id,
            'created_by_user_id' => $owner->id,
            'expense_category' => 'maintenance',
            'title' => 'Scheduled Periodic Servicing for UP-80-MJ-1001',
            'expense_date' => now()->subDays(20)->toDateString(),
            'amount' => 4200.00,
            'payment_mode' => 'bank_transfer',
            'reference_number' => 'AM-SRV-2026-118',
            'paid_to' => 'Agra Motors Maruti Service',
        ]);

        Expense::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMain->id,
            'created_by_user_id' => $owner->id,
            'expense_category' => 'electricity',
            'title' => 'Sanjay Place Branch Office Torrent Power Electricity Bill',
            'expense_date' => now()->subDays(10)->toDateString(),
            'amount' => 3850.00,
            'payment_mode' => 'upi',
            'reference_number' => 'TPAGRA-991283',
            'paid_to' => 'Torrent Power Agra',
        ]);

        // 17. CALENDAR SCHEDULE FILLER
        if (!app()->environment('testing')) {
            $this->call(CalendarScheduleSeeder::class);
        }
    }
}
