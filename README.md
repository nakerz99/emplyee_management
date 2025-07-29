# DTR (Daily Time Record) System

## Overview
A comprehensive web-based time tracking and payroll management system designed for companies with employees across different time zones. The system uses EST as the default timezone while accommodating employees from various locations including the Philippines, India, and other regions.

## Core Features

### 1. Employee Management
- **Individual Employee Accounts**
  - Employee registration with basic information
  - Profile management (name, email, position, department, hourly rate)
  - Timezone settings (default: EST, with support for multiple timezones)
  - Employee status (active, inactive, on leave)

### 2. Authentication & Security
- **Multi-role Authentication**
  - Employee login/logout
  - Admin login with elevated privileges
  - Password reset functionality
  - Session management

### 3. Time Tracking
- **Real-time Time Tracking**
  - Clock in/out functionality
  - Break management (start/pause/resume breaks)
  - Automatic timezone conversion
  - Real-time status indicators
  - Work session history

### 4. Admin Dashboard
- **Comprehensive Overview**
  - Real-time employee status (active, inactive, on break, absent)
  - Employee list with current status
  - Leave management
  - Attendance overview
  - System statistics
  - **Spending Analytics**
    - Total monthly payroll spending
    - Department-wise spending breakdown
    - Employee cost analysis
    - Spending trends and projections
    - Cost per hour analysis
    - Overtime cost tracking
  - **Employee Management**
    - Add, edit, and delete employees
    - Search and filter employees by name, email, position, department, and status
    - **Bulk employee operations** (delete, export, status updates)
    - Employee profile management
    - Role assignment (admin/employee)
    - Status management (active/inactive/on leave)
  - **Department Management**
    - **Full CRUD operations** for departments
    - **Department analytics and statistics**
    - Assign employees to departments
    - **Budget tracking and spending analysis**
    - Department-wise analytics
    - Department spending reports
  - **Time Record Management**
    - **Full CRUD operations** for time records
    - **Advanced filtering and search**
    - **Bulk operations** (delete, export)
    - **Real-time statistics and analytics**
    - View and edit employee time records
    - Manual clock-in/out entries
    - Break time management
    - Time record corrections
    - Bulk time record operations
  - **Advanced Reporting System**
    - **Payroll reports** with detailed calculations
    - **Time analytics** with employee breakdowns
    - **Department reports** with spending analysis
    - **Attendance reports** with rate calculations
    - **Multiple export formats** (Web, Excel, PDF)
    - **Advanced filtering** by date, department, employee

### 5. Payroll Management
- **Hourly Rate Tracking**
  - Automatic calculation of hours worked
  - Break time deduction
  - Overtime calculation
  - Monthly salary computation

### 6. Payslip Generation
- **Individual Payslips**
  - Monthly payslip generation for each employee
  - Detailed breakdown of hours worked
  - Salary calculation with deductions
  - PDF export functionality

### 7. Advanced Reporting System
- **Comprehensive Reports**
  - **Payroll reports** with detailed calculations (regular pay, overtime pay, total pay)
  - **Time analytics** with employee breakdowns and daily statistics
  - **Department reports** with spending analysis and employee counts
  - **Attendance reports** with attendance rates and performance metrics
- **Export Functionality**
  - **Multiple export formats** (Web view, Excel, PDF)
  - **Advanced filtering** by date range, department, employee
  - **Real-time report generation**
  - **Customizable report parameters**

### 8. Leave Management
- **Leave Tracking**
  - Leave request submission
  - Leave approval workflow
  - Leave balance tracking
  - Leave history

### 9. Announcements & Communication
- **Company Announcements**
  - Admin can post company-wide announcements
  - Employee notification system
  - Announcement history and archiving
  - Priority announcements (urgent, normal, info)

### 10. Birthday Celebrants
- **Birthday Tracking**
  - Monthly birthday celebrants display
  - Birthday notifications
  - Birthday calendar view
  - Birthday celebrants dashboard widget

## Technical Requirements

### Database Schema
- **Users Table**: Employee and admin accounts
- **Time_Records Table**: Clock in/out records
- **Breaks Table**: Break session tracking
- **Payslips Table**: Generated payslips
- **Leave_Requests Table**: Leave management
- **Settings Table**: System configuration

### Technology Stack
- **Backend**: Laravel 10+ with Livewire 3
- **Database**: MySQL 8.0+
- **Frontend**: Livewire SPA with Tailwind CSS
- **Authentication**: Laravel Sanctum
- **File Generation**: Laravel Excel for reports, DomPDF for PDF generation
- **Timezone**: Carbon for timezone handling
- **UI Components**: Alpine.js, Headless UI
- **Charts**: Chart.js or ApexCharts for analytics

### Key Features by Role

#### Employee Features
- Login/logout
- Clock in/out
- Break management
- View personal time records
- Generate monthly payslip
- **Profile Management**
  - Update personal information (name, email, phone, address)
  - Change timezone settings
  - Update password securely
  - View employee information (read-only)
  - View employment details (position, department, hourly rate, status)
- Submit leave requests

#### Admin Features
- **Employee Management (Full CRUD)**
  - Add, edit, and delete employees
  - Search and filter employees
  - Bulk operations
  - Role and status management
  - Employee profile administration
- **Department Management**
  - Create and manage departments
  - Assign employees to departments
  - Department analytics and reports
- **Time Record Management**
  - View and edit employee time records
  - Manual time entries
  - Break time management
  - Time record corrections
- Real-time dashboard
- Generate reports
- Approve leave requests
- System configuration
- Payroll management
- Export data to Excel
- **Spending Analytics & Statistics**
  - Monthly/quarterly/yearly spending overview
  - Department cost analysis
  - Employee cost comparison
  - Spending projections and budgeting
  - Cost efficiency metrics
- **Announcement Management**
  - Create and manage company announcements
  - Set announcement priorities
  - Schedule announcements
- **Birthday Management**
  - View monthly birthday celebrants
  - Birthday notifications
  - Birthday calendar management

## Database Design

### Core Tables
1. **users**
   - id, email, password, role, name, position, department
   - hourly_rate, timezone, status, created_at, updated_at

2. **time_records**
   - id, user_id, clock_in, clock_out, total_hours
   - date, status, created_at

3. **breaks**
   - id, time_record_id, break_start, break_end
   - total_break_time, created_at

4. **payslips**
   - id, user_id, month, year, total_hours, total_pay
   - generated_at, status

5. **leave_requests**
   - id, user_id, start_date, end_date, reason
   - status, approved_by, created_at

6. **announcements**
   - id, title, content, priority, author_id
   - is_active, scheduled_at, created_at, updated_at

7. **notifications**
   - id, user_id, type, title, message, is_read
   - created_at, updated_at

## Web Routes

### Authentication
- GET / - Login page
- POST /logout - Logout

### Admin Routes
- GET /admin/dashboard - Admin dashboard
- GET /admin/employees - Employee management
- GET /admin/departments - Department management
- GET /admin/time-records - Time record management
- GET /admin/reports - Advanced reporting system

### Employee Routes
- GET /employee/dashboard - Employee dashboard
- GET /employee/profile - Employee profile management

## API Endpoints

### Authentication
- POST /api/auth/login
- POST /api/auth/logout
- POST /api/auth/reset-password

### Employee Management
- GET /api/employees
- POST /api/employees
- PUT /api/employees/:id
- DELETE /api/employees/:id

### Time Tracking
- POST /api/time/clock-in
- POST /api/time/clock-out
- POST /api/time/break-start
- POST /api/time/break-end
- GET /api/time/records/:userId

### Payslips
- GET /api/payslips/:userId
- POST /api/payslips/generate
- GET /api/payslips/download/:id

### Reports
- GET /api/reports/payroll
- GET /api/reports/attendance
- GET /api/reports/export-excel
- GET /api/reports/spending-analytics
- GET /api/reports/department-costs
- GET /api/reports/employee-costs

## Installation & Setup

### Prerequisites
- PHP 8.1 or higher
- Composer
- MySQL 8.0 or higher
- Node.js and npm
- Laravel Valet (for local development)

### Quick Start with Laravel Valet
```bash
# Clone the repository
git clone <repository-url>
cd dtr

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Start MySQL (if not running)
brew services start mysql

# Create database
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS dtr_system;"

# Run database migrations
php artisan migrate

# Seed the database with initial data
php artisan db:seed --class=DtrSeeder

# Install and build frontend assets
npm install
npm run build

# Link the site with Valet
valet link dtr

# Start the application
valet open
```

The application will be available at `http://dtr.test`

### Access the Application
- **URL**: http://dtr.test
- **Demo Admin**: admin@dtr.com / password
- **Demo Employees**: john@dtr.com, jane@dtr.com, raj@dtr.com, maria@dtr.com / password

### Environment Variables
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dtr_system
DB_USERNAME=root
DB_PASSWORD=qweqweqwe
APP_KEY=your_app_key
APP_ENV=local
APP_DEBUG=true
APP_URL=http://dtr.test
```

## Development Phases

### Phase 1: Core Setup
- Project structure setup
- Database schema creation
- Basic authentication system
- User management

### Phase 2: Time Tracking
- Clock in/out functionality
- Break management
- Timezone handling
- Real-time status updates

### Phase 3: Admin Features
- Admin dashboard
- Employee management
- Real-time monitoring

### Phase 4: Payroll & Reports
- Payslip generation
- Excel report export
- Payroll calculations

### Phase 5: Advanced Features
- Leave management
- Advanced reporting
- UI/UX improvements

## Security Considerations
- Password hashing with bcrypt
- JWT token authentication
- Input validation and sanitization
- SQL injection prevention
- XSS protection
- Rate limiting

## Testing (TDD Implementation)

### Test-Driven Development (TDD) Approach
The project follows Test-Driven Development principles with comprehensive test coverage for all features:

### Test Coverage
- ✅ **User Model Tests** - Core user functionality and relationships
- ✅ **Authentication Tests** - Login/logout and role-based access
- ✅ **TimeRecord Tests** - Time tracking functionality
- ✅ **Department Management Tests** - Full CRUD operations and validation
- ✅ **Time Record Management Tests** - Advanced filtering and bulk operations
- ✅ **Advanced Reports Tests** - All report types and calculations

### Running Tests
```bash
# Run all tests
php artisan test

# Run specific test suites
php artisan test --filter=UserTest
php artisan test --filter=DepartmentManagementTest
php artisan test --filter=TimeRecordManagementTest
php artisan test --filter=ReportsTest

# Run tests with coverage
php artisan test --coverage

# Run tests in parallel
php artisan test --parallel
```

### Test Data
The application includes comprehensive test factories for:
- **User Factory** - Employees and admins with realistic data
- **TimeRecord Factory** - Time tracking records with various scenarios
- **BreakSession Factory** - Break time tracking
- **Payslip Factory** - Payroll calculations
- **LeaveRequest Factory** - Leave management
- **Announcement Factory** - Company communications
- **Notification Factory** - System notifications

## Comprehensive Data Seeding

### 3 Months of Realistic Data
The application includes a comprehensive seeder that creates 3 months of realistic data:

#### Employee Data
- **10 Employees** across different departments and timezones
- **Multiple departments**: Engineering, Marketing, Analytics, Design, HR, Product, Sales, Finance
- **Different timezones**: EST, IST, CST for global team simulation
- **Realistic hourly rates** ranging from $22 to $45 per hour

#### Time Records
- **3 months of time records** (current month + 2 previous months)
- **Weekday work patterns** with realistic clock-in/out times
- **Overtime calculations** for hours worked over 8 hours
- **Break sessions** with lunch breaks and short breaks
- **Work from home notes** for some records

#### Payroll Data
- **Monthly payslips** for all employees
- **Regular and overtime pay** calculations
- **Deductions** and net pay calculations
- **Payment status** tracking

#### Leave Management
- **Leave requests** with various types (vacation, sick, personal, other)
- **Approval workflows** with different statuses
- **Realistic leave patterns** across employees

#### Communications
- **Company announcements** with different priorities
- **System notifications** for various events
- **Realistic communication patterns**

### Seeding Commands
```bash
# Seed with comprehensive data
php artisan db:seed --class=DtrSeeder

# Fresh migration with seeding
php artisan migrate:fresh --seed

# Reset and reseed
php artisan migrate:fresh && php artisan db:seed --class=DtrSeeder
```

## Future Enhancements
- Mobile app development
- Biometric integration
- Advanced analytics
- Integration with accounting software
- Email notifications
- Multi-language support 