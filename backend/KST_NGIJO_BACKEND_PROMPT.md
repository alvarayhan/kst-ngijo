# KST NGIJO - Backend Laravel Development Prompt

**Project**: Sistem Manajemen Digital KST Ngijo (Universitas Brawijaya)  
**Stack**: React.js (Frontend) + Laravel 12 (Backend) + MySQL (Database)  
**Timeline**: < 2 minggu untuk development backend  
**Scope**: Data Pengunjung, Data Riset, Data Tenant/Bisnis KST Ngijo  

---

## 📋 TABLE OF CONTENTS

1. [Project Overview](#project-overview)
2. [System Architecture](#system-architecture)
3. [Stakeholders & User Requirements](#stakeholders--user-requirements)
4. [Use Cases & Business Logic](#use-cases--business-logic)
5. [Database Design (MySQL Schema)](#database-design-mysql-schema)
6. [API Endpoints Specification (RESTful)](#api-endpoints-specification-restful)
7. [Laravel Implementation Structure](#laravel-implementation-structure)
8. [Authentication & Authorization (RBAC)](#authentication--authorization-rbac)
9. [Integration with Kelompok 1 (Central Dashboard)](#integration-with-kelompok-1-central-dashboard)
10. [React Frontend Integration](#react-frontend-integration)
11. [Deployment & Environment Setup](#deployment--environment-setup)

---

## 1. PROJECT OVERVIEW

### 1.1 Context
KST Ngijo adalah Science and Technology Park yang beroperasi di bawah Universitas Brawijaya. Sistem ini dirancang untuk mengelola dan mengintegrasikan data operasional KST secara terpusat, aman, dan real-time. Sistem ini merupakan **sub-system** dari Executive Dashboard Kelompok 1, yang berfungsi sebagai central dashboard untuk semua KST kawasan UB.

### 1.2 Goals
- ✅ Mengelola data terpusat (pengunjung, riset, tenant/bisnis) secara digital dan terstruktur
- ✅ Menyediakan validasi data otomatis dari operator
- ✅ Menampilkan dashboard real-time untuk monitoring performa KST
- ✅ Menyediakan API endpoint untuk integrasi dengan Kelompok 1 (central system)
- ✅ Implementasi RBAC (Role-Based Access Control) dengan JWT authentication
- ✅ Mudah di-maintain dan scalable untuk growth mendatang

### 1.3 Key Metrics
- **Data Pengunjung**: Tracking jumlah, jenis, dan waktu kunjungan ke KST
- **Data Riset**: Pencatatan proyek riset, outputs, collaborators, status progress
- **Data Tenant/Bisnis**: Informasi bisnis yang beroperasi di KST, metrics performa, sustainability
- **Dashboard Monitoring**: Real-time visualization untuk Admin dan Management

---

## 2. SYSTEM ARCHITECTURE

### 2.1 Architecture Pattern
**Hybrid Centralized–Modular Architecture** dengan tiga layers:

```
┌─────────────────────────────────────────────────────────────┐
│                   CLIENT LAYER (React.js)                   │
│  - Landing Page (Public)                                    │
│  - Dashboard (Production, Research, Sustainability)         │
│  - Data Input Forms (Production, Research, Sustainability)  │
│  - Admin Panel (Data Management, Validation)                │
└────────────────────────┬────────────────────────────────────┘
                         │ (API REST + JWT Token)
┌────────────────────────▼────────────────────────────────────┐
│              APPLICATION LAYER (Laravel 12)                 │
│  - Routes & Controllers (Resource-based)                    │
│  - Models & Business Logic                                  │
│  - Middleware (Auth, RBAC, Validation)                      │
│  - Services (Notification, Integration)                     │
└────────────────────────┬────────────────────────────────────┘
                         │ (SQL Queries, ORM)
┌────────────────────────▼────────────────────────────────────┐
│           DATA LAYER (MySQL 5.7+)                           │
│  - Users & Roles (RBAC)                                     │
│  - Production Data (KST Operational)                        │
│  - Research Data (Projects, Collaborators)                  │
│  - Tenant/Business Data (Companies, Metrics)                │
│  - System Logs & Audit Trail                                │
└─────────────────────────────────────────────────────────────┘
```

### 2.2 Integration Points

**Endpoint Internal (Ngijo System Only)**:
- `/api/internal/*` — digunakan untuk komunikasi antar modul dalam sistem Ngijo

**Endpoint Integrasi (ke Kelompok 1 - Central Dashboard)**:
- `/api/external/integration/*` — dedicated endpoint untuk push/sync data ke central system
- Authentication: API Key (provided by Kelompok 1)
- Format: Standard RESTful JSON

---

## 3. STAKEHOLDERS & USER REQUIREMENTS

### 3.1 Stakeholder Analysis

| No | Stakeholder | Role | Primary Need | Permission Level |
|----|-------------|------|--------------|------------------|
| 1 | Admin KST | System Manager | Mengelola & validasi data terpusat | Full Access |
| 2 | Operator KST | Data Entry | Input data operasional (produksi, riset, sustainability) | Create/Update Own Data |
| 3 | Management | Decision Maker | Monitoring dashboard real-time KPI | Read-Only Dashboard |
| 4 | Peneliti/Dosen | Researcher | Akses data riset terstruktur untuk analisis | Read-Only Research Data |
| 5 | Pengguna Publik | General User | Akses informasi umum KST (landing page, public dashboard) | Read-Only Public Data |

### 3.2 User Requirements Mapping

| Req # | Requirement | Stakeholder(s) | Priority | Implementation |
|-------|-------------|-----------------|----------|-----------------|
| UR-01 | Sistem dapat mengelola & mengintegrasikan data secara terpusat & aman | Admin | **HIGH** | RBAC + Encryption + Audit Log |
| UR-02 | Sistem dapat mencatat data operasional secara otomatis (digital) | Operator | **HIGH** | Form Input + Auto Validation |
| UR-03 | Dashboard real-time untuk monitoring | Management | **HIGH** | WebSocket/Polling + Charts |
| UR-04 | Akses data terstruktur untuk penelitian | Peneliti | **MEDIUM** | API Export + Data Structure |
| UR-05 | Akses informasi umum mudah (landing page) | Publik | **MEDIUM** | Public Endpoint + Caching |

---

## 4. USE CASES & BUSINESS LOGIC

### 4.1 Use Case Summary

```
ADMIN
├── UC-01: Mengelola data KST Ngijo (CRUD)
├── UC-02: Memvalidasi data dari operator
├── UC-03: Monitoring dashboard
└── UC-09: Manage users & roles (Implicit)

OPERATOR
├── UC-04: Menginput data produksi
├── UC-05: Menginput data riset
└── UC-06: Menginput data sustainability

USER (All)
├── UC-07: Mengakses dashboard
└── UC-08: Mengakses landing page
```

### 4.2 Detailed Use Case Specifications

#### **UC-01: Mengelola Data KST Ngijo**
- **Actor**: Admin
- **Precondition**: Admin telah login
- **Main Flow**:
  1. Admin membuka menu pengelolaan data
  2. Admin melihat daftar data KST (dengan filter, search, pagination)
  3. Admin menambah/mengubah/menghapus data
  4. Sistem validasi data (field requirement, data type)
  5. Sistem menyimpan ke database + log to audit trail
  6. Sistem menampilkan success/error message
- **Alternative Flow**: Jika data invalid → System shows detailed error message
- **Postcondition**: Data KST berhasil diperbarui, audit log tercatat
- **API Implementation**: 
  - `GET /api/internal/kst-data` (list)
  - `POST /api/internal/kst-data` (create)
  - `PUT /api/internal/kst-data/{id}` (update)
  - `DELETE /api/internal/kst-data/{id}` (delete)

#### **UC-02: Memvalidasi Data dari Operator**
- **Actor**: Admin
- **Precondition**: Data dari operator tersedia dengan status "pending"
- **Main Flow**:
  1. Admin membuka "Pending Validation" queue
  2. Admin membaca detail data operator (dengan comparison to previous data)
  3. Admin menyetujui atau menolak dengan komentar (optional)
  4. Sistem update status ke "approved" atau "rejected"
  5. Sistem kirim notifikasi ke operator (extend UC: Notifikasi)
- **Postcondition**: Status data menjadi "approved"/"rejected", notifikasi terkirim
- **API Implementation**:
  - `GET /api/internal/validations/pending`
  - `PATCH /api/internal/validations/{id}/approve`
  - `PATCH /api/internal/validations/{id}/reject`

#### **UC-03: Monitoring Dashboard**
- **Actor**: Admin (also Management)
- **Main Flow**:
  1. Admin membuka dashboard
  2. Sistem query data real-time dari database
  3. Sistem menampilkan grafik KPI, statistik, trend analysis
  4. Admin bisa apply filter (date range, kategori, dll)
- **Data Displayed**:
  - Total visitors per time period
  - Active research projects count
  - Tenant/business activity metrics
  - System health status
- **API Implementation**:
  - `GET /api/internal/dashboard/overview`
  - `GET /api/internal/dashboard/production`
  - `GET /api/internal/dashboard/research`
  - `GET /api/internal/dashboard/sustainability`

#### **UC-04: Menginput Data Produksi**
- **Actor**: Operator
- **Precondition**: Operator telah login
- **Main Flow**:
  1. Operator membuka form input produksi
  2. Operator mengisi field (visitor count, category, date, notes)
  3. Operator submit form
  4. Sistem validasi (required fields, data type, range check)
  5. Jika valid → simpan dengan status "pending" (menunggu approval Admin)
  6. Jika invalid → tampilkan error message
  7. Sistem kirim notifikasi ke Admin (new data pending)
- **Postcondition**: Data produksi tersimpan dengan status "pending"
- **API Implementation**:
  - `POST /api/internal/production-data` (create with status=pending)
  - `GET /api/internal/production-data/my-submissions` (view own submissions)

#### **UC-05: Menginput Data Riset**
- **Actor**: Operator
- **Main Flow**:
  1. Operator membuka form input riset
  2. Operator mengisi field (project name, duration, collaborators, output, status)
  3. Operator submit form
  4. Sistem validasi dan simpan dengan status "pending"
  5. Include ke dashboard riset (UC-07 variation)
- **Postcondition**: Data riset tersimpan, accessible di dashboard
- **API Implementation**:
  - `POST /api/internal/research-data` (create)
  - `PUT /api/internal/research-data/{id}` (operator dapat update own data before approval)

#### **UC-06: Menginput Data Sustainability**
- **Actor**: Operator
- **Main Flow**:
  1. Operator membuka form sustainability
  2. Operator mengisi metrics (energy use, waste management, social impact, etc)
  3. Operator submit
  4. Sistem validasi dan simpan dengan status "pending"
  5. Include ke dashboard sustainability
- **API Implementation**:
  - `POST /api/internal/sustainability-data` (create)

#### **UC-07: Mengakses Dashboard**
- **Actor**: User (semua role yang authenticated)
- **Main Flow**:
  1. User membuka aplikasi → landing page / dashboard (berdasarkan role)
  2. User memilih tipe dashboard:
     - **Production Dashboard**: visitor stats, trends, peak hours
     - **Research Dashboard**: active projects, collaborators, outputs
     - **Sustainability Dashboard**: environmental metrics, goals, progress
     - **Executive Dashboard**: summary KPI, alerts, recommendations
  3. Sistem fetch data real-time dari database
  4. Sistem render charts, tables, statistics
  5. User bisa apply filter (date range, kategori)
- **Role-based visibility**:
  - Admin: Full data visibility
  - Management: Approved data only
  - Researcher: Research data + aggregated metrics
  - Public User: Public summary only
- **API Implementation**:
  - `GET /api/internal/dashboard/{type}` (production/research/sustainability/executive)
  - `GET /api/internal/dashboard/{type}?start_date=...&end_date=...&category=...`

#### **UC-08: Mengakses Landing Page**
- **Actor**: User (Public/Unauthenticated)
- **Precondition**: No login required
- **Main Flow**:
  1. User membuka website
  2. Sistem render landing page dengan:
     - KST information (description, vision, mission)
     - Quick statistics (total visitors, active projects)
     - Photo gallery / highlights
     - Contact information
  3. User dapat melihat public dashboard (summary only)
- **API Implementation**:
  - `GET /api/external/landing-page/info` (public endpoint)
  - `GET /api/external/landing-page/stats` (public aggregated stats)

### 4.3 Implicit Requirements
- **User Management (UC-09)**: Admin dapat manage users (create, edit, delete, assign roles)
  - `GET /api/internal/users`
  - `POST /api/internal/users` (create with role assignment)
  - `PUT /api/internal/users/{id}`
  - `DELETE /api/internal/users/{id}`

- **Authentication/Login**: Semua user harus login dengan JWT token
  - `POST /api/auth/login` (credentials → JWT token)
  - `POST /api/auth/logout`
  - `POST /api/auth/refresh` (refresh token)

- **Notifikasi**: Admin dapat menerima notifikasi untuk pending validations, new data submissions
  - `POST /api/internal/notifications` (send)
  - `GET /api/internal/notifications` (list for current user)
  - `PATCH /api/internal/notifications/{id}/read`

---

## 5. DATABASE DESIGN (MySQL SCHEMA)

### 5.1 Database Structure Overview

```sql
-- 1. USER & ROLE MANAGEMENT
users
├── id (PK)
├── name
├── email (UNIQUE)
├── password (hashed)
├── role_id (FK to roles)
├── status (active/inactive)
├── email_verified_at
├── created_at, updated_at
└── deleted_at (soft delete)

roles
├── id (PK)
├── name (admin, operator, management, researcher, public)
├── description
├── created_at, updated_at

permissions (future-proofing)
├── id (PK)
├── name
├── description

role_permissions (pivot)
├── role_id (FK)
├── permission_id (FK)

-- 2. PRODUCTION DATA (Pengunjung KST)
production_data
├── id (PK)
├── date
├── visitor_count
├── visitor_category (individuals/groups/researchers/students)
├── time_slot
├── notes
├── status (pending/approved/rejected)
├── created_by_user_id (FK to users - operator)
├── approved_by_user_id (FK to users - admin, nullable)
├── rejection_reason (nullable)
├── created_at, updated_at

-- 3. RESEARCH DATA
research_projects
├── id (PK)
├── title
├── description
├── start_date
├── end_date (nullable if ongoing)
├── status (planning/active/completed/paused)
├── category (technology/agriculture/energy/sustainability/other)
├── principal_investigator_id (FK to users)
├── created_by_user_id (FK to users - operator)
├── budget (nullable)
├── created_at, updated_at

research_collaborators (pivot)
├── research_project_id (FK)
├── collaborator_name
├── institution
├── role
├── created_at

research_outputs
├── id (PK)
├── research_project_id (FK)
├── output_type (publication/patent/prototype/report/other)
├── title
├── description
├── date_produced
├── link (nullable - URL to publication)
├── created_at, updated_at

-- 4. TENANT/BUSINESS DATA
tenants
├── id (PK)
├── company_name
├── industry_category (manufacturing/tech/agriculture/energy/other)
├── address
├── contact_person
├── phone
├── email
├── website (nullable)
├── registration_date
├── status (active/inactive/graduated)
├── notes
├── created_by_user_id (FK)
├── created_at, updated_at

tenant_metrics (for performance tracking)
├── id (PK)
├── tenant_id (FK)
├── metric_date
├── employees_count
├── revenue (nullable)
├── products_produced
├── market_reach (local/regional/national/international)
├── sustainability_score (1-100, nullable)
├── created_at

-- 5. SUSTAINABILITY DATA
sustainability_data
├── id (PK)
├── record_date
├── category (energy/water/waste/emissions/social)
├── metric_name
├── value
├── unit (kWh, liters, kg, etc)
├── target_value (nullable)
├── notes
├── created_by_user_id (FK)
├── approved_by_user_id (FK, nullable)
├── status (pending/approved/rejected)
├── created_at, updated_at

-- 6. SYSTEM & AUDIT
data_validations (queue untuk pending approvals)
├── id (PK)
├── validatable_id (polymorphic - production_data, research, sustainability, etc)
├── validatable_type (polymorphic type)
├── submitted_by_user_id (FK)
├── status (pending/approved/rejected)
├── admin_comments (nullable)
├── approved_by_user_id (FK, nullable)
├── approved_at (nullable)
├── created_at, updated_at

audit_logs
├── id (PK)
├── user_id (FK)
├── action (create/update/delete/approve/reject)
├── model_type (which table was affected)
├── model_id
├── old_values (JSON)
├── new_values (JSON)
├── ip_address
├── user_agent
├── created_at

notifications
├── id (PK)
├── user_id (FK)
├── type (data_pending_approval, data_rejected, data_approved)
├── title
├── message
├── data_reference_id (nullable - link to related data)
├── is_read
├── read_at (nullable)
├── created_at

integration_logs (for Kelompok 1 integration)
├── id (PK)
├── endpoint (which external endpoint was called)
├── method (POST/PUT/GET)
├── payload (JSON)
├── response_status
├── response_body (JSON)
├── external_system (kelompok_1)
├── success (boolean)
├── error_message (nullable)
├── created_at

-- Laravel built-in tables (migrations already exist)
sessions
migrations
password_reset_tokens
```

### 5.2 Relationships Diagram (ERD)

```
┌─────────────────────────────────────────────────────────────┐
│                  USERS (Central Entity)                     │
├─────────────────────────────────────────────────────────────┤
│ id (PK) | name | email | password | role_id (FK)           │
│ status | email_verified_at | created_at | updated_at       │
└──────────┬──────────────────────────────────────────────────┘
           │
     1 ┌───┴────┐ N
         │ ROLES
         ├──────────────────────────────────────┐
         │ id | name | description | ...        │
         └──────────────────────────────────────┘

PRODUCTION_DATA          RESEARCH_PROJECTS        TENANTS
├─ date                  ├─ title                 ├─ company_name
├─ visitor_count         ├─ status                ├─ industry_category
├─ visitor_category      ├─ start_date            ├─ contact_person
├─ status                ├─ end_date              ├─ status
├─ created_by_user_id ─┐ ├─ created_by_user_id ┐ ├─ created_by_user_id ┐
└─ approved_by_user_id─┐ └─ principal_... ──────┼─┴─ ...               │
                       │                         │                       │
                       └─────────────────────────┴───────── (all FK to users)

RESEARCH_OUTPUTS         RESEARCH_COLLABORATORS   TENANT_METRICS
├─ research_project_id ──┤ ├─ research_project_id┤ ├─ tenant_id ────┐
├─ output_type           │ ├─ collaborator_name   │ ├─ employees_count
└─ ...                   │ └─ ...                 │ └─ ...
                         └─ (pivot/related)      └─ (related to tenants)

SUSTAINABILITY_DATA      DATA_VALIDATIONS        AUDIT_LOGS
├─ category              ├─ validatable_id       ├─ user_id
├─ metric_name           ├─ validatable_type     ├─ action
├─ value                 ├─ status               ├─ model_type
├─ created_by_user_id    ├─ submitted_by_user_id├─ old_values (JSON)
├─ approved_by_user_id   └─ approved_by_user_id  └─ new_values (JSON)
└─ status
```

### 5.3 Key Design Decisions

1. **Soft Deletes**: Users table pakai `deleted_at` untuk audit trail (data tidak benar-benar dihapus)

2. **Polymorphic Data Validation**: `data_validations` table menggunakan polymorphic relationship, sehingga production, research, sustainability bisa melalui validation flow yang sama

3. **JSON Columns**: `audit_logs.old_values` & `new_values` menyimpan change history dalam JSON format (flexible untuk berbagai model)

4. **Status Workflow**: 
   - Production/Research/Sustainability: `pending → approved/rejected`
   - Dimungkinkan untuk update before approval (operator data correction)

5. **Audit Trail**: Setiap create/update/delete/approve di-log ke `audit_logs` untuk compliance & debugging

6. **Integration Tracking**: `integration_logs` mencatat setiap push ke Kelompok 1, berguna untuk debugging dan reconciliation

---

## 6. API ENDPOINTS SPECIFICATION (RESTful)

### 6.1 Authentication Endpoints

#### `POST /api/auth/login`
- **Description**: User login dengan email & password, returns JWT token
- **Auth**: None (public)
- **Request Body**:
```json
{
  "email": "operator@kst.local",
  "password": "secure_password"
}
```
- **Response** (200 OK):
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "user": {
      "id": 1,
      "name": "Operator KST",
      "email": "operator@kst.local",
      "role": "operator",
      "permissions": ["create_production_data", "view_dashboard"]
    }
  }
}
```
- **Error** (401 Unauthorized):
```json
{
  "success": false,
  "message": "Invalid email or password"
}
```

#### `POST /api/auth/logout`
- **Description**: Logout user (invalidate token)
- **Auth**: Bearer token (required)
- **Response** (200 OK):
```json
{
  "success": true,
  "message": "Logout successful"
}
```

#### `POST /api/auth/refresh`
- **Description**: Refresh JWT token
- **Auth**: Bearer token (required)
- **Response** (200 OK):
```json
{
  "success": true,
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
  }
}
```

#### `GET /api/auth/me`
- **Description**: Get current authenticated user info
- **Auth**: Bearer token (required)
- **Response** (200 OK):
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Operator KST",
    "email": "operator@kst.local",
    "role": "operator",
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

---

### 6.2 Production Data Endpoints (UC-04, UC-01)

#### `GET /api/internal/production-data`
- **Description**: List semua production data (with filters, pagination)
- **Auth**: Bearer token (role: admin, management)
- **Query Parameters**:
  - `page=1` (default)
  - `per_page=20` (default)
  - `date_from=2024-01-01`
  - `date_to=2024-12-31`
  - `category=individuals|groups|researchers|students|all` (default: all)
  - `status=pending|approved|rejected|all` (default: approved)
  - `sort=-date` (sort by date descending)
- **Response** (200 OK):
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "date": "2024-01-15",
      "visitor_count": 45,
      "visitor_category": "students",
      "time_slot": "morning",
      "notes": "Campus visit dari ITB",
      "status": "approved",
      "created_by": {
        "id": 2,
        "name": "Operator 1"
      },
      "approved_by": {
        "id": 1,
        "name": "Admin KST"
      },
      "approved_at": "2024-01-15T14:20:00Z",
      "created_at": "2024-01-15T10:30:00Z",
      "updated_at": "2024-01-15T14:20:00Z"
    }
  ],
  "pagination": {
    "total": 150,
    "per_page": 20,
    "current_page": 1,
    "last_page": 8
  }
}
```

#### `GET /api/internal/production-data/my-submissions`
- **Description**: List production data submitted by current operator
- **Auth**: Bearer token (role: operator)
- **Response**: Same as above, filtered to current user submissions

#### `POST /api/internal/production-data`
- **Description**: Create new production data entry
- **Auth**: Bearer token (role: operator)
- **Request Body**:
```json
{
  "date": "2024-01-16",
  "visitor_count": 32,
  "visitor_category": "individuals",
  "time_slot": "afternoon",
  "notes": "Walk-in visitors"
}
```
- **Response** (201 Created):
```json
{
  "success": true,
  "message": "Production data created successfully, pending admin approval",
  "data": {
    "id": 2,
    "date": "2024-01-16",
    "visitor_count": 32,
    "visitor_category": "individuals",
    "status": "pending",
    "created_by_user_id": 2,
    "created_at": "2024-01-16T09:15:00Z"
  }
}
```
- **Validation Rules**:
  - `date`: required, date format (YYYY-MM-DD), cannot be future date
  - `visitor_count`: required, integer, min=0, max=10000
  - `visitor_category`: required, in:individuals,groups,researchers,students
  - `time_slot`: required, in:morning,afternoon,evening
  - `notes`: optional, string, max=500

#### `GET /api/internal/production-data/{id}`
- **Description**: Get single production data entry
- **Auth**: Bearer token
- **Response** (200 OK): Same structure as list item
- **Error** (404 Not Found):
```json
{
  "success": false,
  "message": "Production data not found"
}
```

#### `PUT /api/internal/production-data/{id}`
- **Description**: Update production data (only if not yet approved)
- **Auth**: Bearer token (role: operator who created, or admin)
- **Request Body**: Same as POST
- **Response** (200 OK): Updated data object
- **Error** (403 Forbidden):
```json
{
  "success": false,
  "message": "Cannot update approved data"
}
```

#### `DELETE /api/internal/production-data/{id}`
- **Description**: Delete production data (only if pending)
- **Auth**: Bearer token (role: operator who created, or admin)
- **Response** (200 OK):
```json
{
  "success": true,
  "message": "Production data deleted successfully"
}
```

---

### 6.3 Research Data Endpoints (UC-05)

#### `GET /api/internal/research-data`
- **Description**: List all research projects
- **Auth**: Bearer token
- **Query Parameters**:
  - `page=1`
  - `per_page=20`
  - `status=active|completed|paused|all` (default: all)
  - `category=technology|agriculture|energy|sustainability|other|all`
  - `search=project_name` (search by title or description)
  - `sort=-start_date`
- **Response** (200 OK):
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Smart Irrigation System for KST",
      "description": "IoT-based water management",
      "start_date": "2023-06-01",
      "end_date": "2024-06-01",
      "status": "active",
      "category": "technology",
      "principal_investigator": {
        "id": 3,
        "name": "Dr. Budi Santoso"
      },
      "budget": 150000000,
      "collaborators": [
        {
          "id": 1,
          "collaborator_name": "Prof. Ahmad Wijaya",
          "institution": "UB",
          "role": "Co-investigator"
        }
      ],
      "outputs": [
        {
          "id": 1,
          "output_type": "prototype",
          "title": "Prototype IoT Sensor v1.0",
          "date_produced": "2024-01-10"
        }
      ],
      "created_at": "2023-06-01T08:00:00Z",
      "updated_at": "2024-01-15T15:30:00Z"
    }
  ],
  "pagination": { ... }
}
```

#### `POST /api/internal/research-data`
- **Description**: Create new research project
- **Auth**: Bearer token (role: operator, admin)
- **Request Body**:
```json
{
  "title": "Renewable Energy Solutions for KST",
  "description": "Exploring solar and wind integration",
  "start_date": "2024-03-01",
  "end_date": "2025-03-01",
  "category": "energy",
  "principal_investigator_id": 3,
  "budget": 200000000,
  "collaborators": [
    {
      "collaborator_name": "Prof. Siti Nurhaliza",
      "institution": "ITS",
      "role": "Co-investigator"
    }
  ]
}
```
- **Response** (201 Created): Created research project object
- **Validation Rules**:
  - `title`: required, string, max=255
  - `description`: optional, string, max=1000
  - `start_date`: required, date, cannot be future
  - `end_date`: optional, date, must be after start_date
  - `category`: required, in:technology,agriculture,energy,sustainability,other
  - `principal_investigator_id`: required, exists in users table
  - `budget`: optional, numeric, min=0
  - `collaborators`: optional, array of objects

#### `GET /api/internal/research-data/{id}`
- **Description**: Get single research project with all details
- **Auth**: Bearer token
- **Response** (200 OK): Same structure as list item

#### `PUT /api/internal/research-data/{id}`
- **Description**: Update research project
- **Auth**: Bearer token (role: operator who created, or admin)
- **Request Body**: Same as POST
- **Response** (200 OK): Updated project object

#### `POST /api/internal/research-data/{id}/outputs`
- **Description**: Add output to research project
- **Auth**: Bearer token
- **Request Body**:
```json
{
  "output_type": "publication",
  "title": "Smart Irrigation System: A Case Study",
  "date_produced": "2024-01-10",
  "link": "https://journal.example.com/paper-123"
}
```
- **Response** (201 Created): Created output object

---

### 6.4 Sustainability Data Endpoints (UC-06)

#### `GET /api/internal/sustainability-data`
- **Description**: List sustainability records
- **Auth**: Bearer token
- **Query Parameters**:
  - `page=1`
  - `per_page=20`
  - `date_from=2024-01-01`
  - `date_to=2024-12-31`
  - `category=energy|water|waste|emissions|social|all` (default: all)
  - `status=pending|approved|rejected|all`
  - `sort=-record_date`
- **Response** (200 OK):
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "record_date": "2024-01-15",
      "category": "energy",
      "metric_name": "Electricity consumption",
      "value": 450,
      "unit": "kWh",
      "target_value": 400,
      "progress_percent": 112.5,
      "status": "approved",
      "notes": "Higher than target due to extreme weather",
      "created_by": { "id": 2, "name": "Operator 1" },
      "created_at": "2024-01-15T10:30:00Z"
    }
  ],
  "pagination": { ... }
}
```

#### `POST /api/internal/sustainability-data`
- **Description**: Create new sustainability record
- **Auth**: Bearer token (role: operator)
- **Request Body**:
```json
{
  "record_date": "2024-01-16",
  "category": "water",
  "metric_name": "Water consumption",
  "value": 850,
  "unit": "liters",
  "target_value": 800,
  "notes": "From monthly meter reading"
}
```
- **Response** (201 Created): Created record object
- **Validation Rules**:
  - `record_date`: required, date, cannot be future
  - `category`: required, in:energy,water,waste,emissions,social
  - `metric_name`: required, string, max=255
  - `value`: required, numeric, min=0
  - `unit`: required, string (kWh, liters, kg, etc)
  - `target_value`: optional, numeric
  - `notes`: optional, string, max=500

#### `GET /api/internal/sustainability-data/{id}`
- **Description**: Get single sustainability record
- **Auth**: Bearer token
- **Response** (200 OK): Same as list item

#### `PUT /api/internal/sustainability-data/{id}`
- **Description**: Update sustainability record (only if pending)
- **Auth**: Bearer token (role: operator who created, or admin)
- **Request Body**: Same as POST
- **Response** (200 OK): Updated record

---

### 6.5 Data Validation Endpoints (UC-02)

#### `GET /api/internal/validations/pending`
- **Description**: Get all pending data validation queue
- **Auth**: Bearer token (role: admin)
- **Query Parameters**:
  - `page=1`
  - `per_page=20`
  - `type=production|research|sustainability|all` (default: all)
  - `sort=-created_at`
- **Response** (200 OK):
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "type": "production_data",
      "data": {
        "id": 2,
        "date": "2024-01-16",
        "visitor_count": 32,
        "visitor_category": "individuals",
        "notes": "Walk-in visitors"
      },
      "submitted_by": {
        "id": 2,
        "name": "Operator 1",
        "email": "operator@kst.local"
      },
      "created_at": "2024-01-16T09:15:00Z"
    }
  ],
  "pagination": { ... }
}
```

#### `PATCH /api/internal/validations/{id}/approve`
- **Description**: Approve pending data
- **Auth**: Bearer token (role: admin)
- **Request Body**:
```json
{
  "comments": "Data looks good" // optional
}
```
- **Response** (200 OK):
```json
{
  "success": true,
  "message": "Data approved successfully",
  "data": {
    "id": 1,
    "status": "approved",
    "approved_by": { "id": 1, "name": "Admin KST" },
    "approved_at": "2024-01-16T10:45:00Z"
  }
}
```
- **Side Effects**:
  - Status data → "approved"
  - Create notification untuk operator (data approved)
  - Log to audit_logs

#### `PATCH /api/internal/validations/{id}/reject`
- **Description**: Reject pending data
- **Auth**: Bearer token (role: admin)
- **Request Body**:
```json
{
  "rejection_reason": "Visitor count seems too high, please verify"
}
```
- **Response** (200 OK):
```json
{
  "success": true,
  "message": "Data rejected successfully",
  "data": {
    "id": 1,
    "status": "rejected",
    "rejection_reason": "Visitor count seems too high, please verify",
    "rejected_at": "2024-01-16T10:45:00Z"
  }
}
```
- **Side Effects**:
  - Status data → "rejected"
  - Create notification untuk operator (data rejected with reason)
  - Log to audit_logs

---

### 6.6 Dashboard Endpoints (UC-03, UC-07)

#### `GET /api/internal/dashboard/overview`
- **Description**: Get dashboard overview (summary across all data)
- **Auth**: Bearer token
- **Query Parameters**:
  - `date_from=2024-01-01`
  - `date_to=2024-12-31` (default: current month)
- **Response** (200 OK):
```json
{
  "success": true,
  "data": {
    "summary": {
      "total_visitors_month": 1250,
      "total_visitors_year": 12450,
      "active_research_projects": 8,
      "active_tenants": 15,
      "sustainability_score": 72.5
    },
    "recent_activities": [
      {
        "type": "new_research",
        "description": "Project 'Smart Irrigation' created",
        "timestamp": "2024-01-16T14:30:00Z"
      }
    ],
    "alerts": [
      {
        "level": "warning",
        "message": "Energy consumption above target for 3 consecutive weeks"
      }
    ]
  }
}
```

#### `GET /api/internal/dashboard/production`
- **Description**: Production dashboard with visitor analytics
- **Auth**: Bearer token
- **Query Parameters**:
  - `date_from=2024-01-01`
  - `date_to=2024-12-31`
  - `grouping=daily|weekly|monthly` (default: daily)
- **Response** (200 OK):
```json
{
  "success": true,
  "data": {
    "total_visitors": 1250,
    "average_daily": 40,
    "peak_day": "2024-01-15",
    "peak_visitors": 85,
    "by_category": {
      "individuals": 250,
      "groups": 350,
      "researchers": 400,
      "students": 250
    },
    "by_time_slot": {
      "morning": 450,
      "afternoon": 600,
      "evening": 200
    },
    "trend": [
      {
        "date": "2024-01-01",
        "count": 32
      },
      {
        "date": "2024-01-02",
        "count": 45
      }
    ]
  }
}
```

#### `GET /api/internal/dashboard/research`
- **Description**: Research dashboard with project metrics
- **Auth**: Bearer token
- **Query Parameters**:
  - `status=active|completed|all` (default: active)
  - `category=all` or specific category
- **Response** (200 OK):
```json
{
  "success": true,
  "data": {
    "total_projects": 12,
    "active_projects": 8,
    "completed_projects": 4,
    "by_category": {
      "technology": 5,
      "agriculture": 3,
      "energy": 2,
      "sustainability": 2
    },
    "total_outputs": 23,
    "outputs_by_type": {
      "publication": 12,
      "prototype": 8,
      "patent": 2,
      "report": 1
    },
    "projects": [
      {
        "id": 1,
        "title": "Smart Irrigation System",
        "status": "active",
        "progress_days": 225,
        "total_duration_days": 365,
        "completion_percent": 61.6,
        "outputs_count": 3
      }
    ]
  }
}
```

#### `GET /api/internal/dashboard/sustainability`
- **Description**: Sustainability dashboard with environmental metrics
- **Auth**: Bearer token
- **Query Parameters**:
  - `date_from=2024-01-01`
  - `date_to=2024-12-31`
  - `category=all` or specific category
- **Response** (200 OK):
```json
{
  "success": true,
  "data": {
    "overall_score": 72.5,
    "score_trend": [70, 71, 71.5, 72, 72.5],
    "by_category": {
      "energy": {
        "score": 75,
        "value": 450,
        "unit": "kWh",
        "target": 400,
        "status": "above_target"
      },
      "water": {
        "score": 70,
        "value": 850,
        "unit": "liters",
        "target": 800,
        "status": "above_target"
      },
      "waste": {
        "score": 75,
        "value": 120,
        "unit": "kg",
        "target": 150,
        "status": "below_target_good"
      },
      "emissions": {
        "score": 68,
        "value": 2.5,
        "unit": "tons CO2e",
        "target": 2.0,
        "status": "above_target"
      },
      "social": {
        "score": 72,
        "description": "Community engagement activities"
      }
    },
    "goals": {
      "current_month_target": 75,
      "year_target": 80,
      "next_target_actions": [
        "Reduce energy consumption by 10%",
        "Implement rainwater harvesting"
      ]
    }
  }
}
```

#### `GET /api/internal/dashboard/executive`
- **Description**: Executive summary dashboard (for management)
- **Auth**: Bearer token (role: admin, management)
- **Response** (200 OK): Combined summary dari production, research, sustainability + KPIs
```json
{
  "success": true,
  "data": {
    "kpis": {
      "total_visitors_ytd": 12450,
      "visitor_growth_percent": 18.5,
      "active_projects": 8,
      "project_success_rate": 85,
      "sustainability_score": 72.5,
      "tenant_satisfaction": 4.2
    },
    "alerts": [
      {
        "severity": "high",
        "category": "sustainability",
        "message": "Energy usage trending above target for Q1"
      }
    ],
    "recommendations": [
      {
        "category": "operations",
        "suggestion": "Implement visitor flow optimization during peak hours"
      }
    ]
  }
}
```

---

### 6.7 User Management Endpoints (UC-09, Admin Panel)

#### `GET /api/internal/users`
- **Description**: List all users
- **Auth**: Bearer token (role: admin)
- **Query Parameters**:
  - `page=1`
  - `per_page=20`
  - `role=admin|operator|management|researcher|public`
  - `status=active|inactive`
  - `search=name_or_email`
- **Response** (200 OK):
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Admin KST",
      "email": "admin@kst.local",
      "role": "admin",
      "status": "active",
      "created_at": "2024-01-01T00:00:00Z"
    }
  ],
  "pagination": { ... }
}
```

#### `POST /api/internal/users`
- **Description**: Create new user
- **Auth**: Bearer token (role: admin)
- **Request Body**:
```json
{
  "name": "Operator Baru",
  "email": "operator.baru@kst.local",
  "password": "temporary_password_123",
  "role": "operator",
  "status": "active"
}
```
- **Response** (201 Created):
```json
{
  "success": true,
  "message": "User created successfully",
  "data": {
    "id": 10,
    "name": "Operator Baru",
    "email": "operator.baru@kst.local",
    "role": "operator",
    "status": "active",
    "created_at": "2024-01-16T15:00:00Z"
  }
}
```
- **Validation Rules**:
  - `name`: required, string, max=255
  - `email`: required, email, unique
  - `password`: required, min=8 characters
  - `role`: required, in:admin,operator,management,researcher,public
  - `status`: required, in:active,inactive

#### `PUT /api/internal/users/{id}`
- **Description**: Update user
- **Auth**: Bearer token (role: admin)
- **Request Body**: Same as POST (without password if not changing)
- **Response** (200 OK): Updated user object

#### `DELETE /api/internal/users/{id}`
- **Description**: Soft delete user (deactivate)
- **Auth**: Bearer token (role: admin)
- **Response** (200 OK):
```json
{
  "success": true,
  "message": "User deleted successfully"
}
```

---

### 6.8 Notification Endpoints

#### `GET /api/internal/notifications`
- **Description**: Get notifications for current user
- **Auth**: Bearer token
- **Query Parameters**:
  - `unread_only=true` (default: false)
  - `page=1`
  - `per_page=20`
- **Response** (200 OK):
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "type": "data_pending_approval",
      "title": "New Production Data Pending Approval",
      "message": "Operator 1 submitted 2 new production data entries",
      "is_read": false,
      "created_at": "2024-01-16T09:15:00Z"
    }
  ]
}
```

#### `PATCH /api/internal/notifications/{id}/read`
- **Description**: Mark notification as read
- **Auth**: Bearer token
- **Response** (200 OK):
```json
{
  "success": true,
  "data": {
    "id": 1,
    "is_read": true,
    "read_at": "2024-01-16T10:30:00Z"
  }
}
```

---

### 6.9 Public Endpoints (No Authentication)

#### `GET /api/external/landing-page/info`
- **Description**: Get KST info for landing page
- **Auth**: None (public)
- **Response** (200 OK):
```json
{
  "success": true,
  "data": {
    "name": "KST Ngijo",
    "description": "Science and Technology Park Universitas Brawijaya",
    "vision": "...",
    "mission": "...",
    "established_year": 2020,
    "address": "...",
    "contact_email": "info@kst-ngijo.local",
    "phone": "+62-...",
    "website": "https://kst-ngijo.local"
  }
}
```

#### `GET /api/external/landing-page/stats`
- **Description**: Get public summary statistics
- **Auth**: None (public)
- **Response** (200 OK):
```json
{
  "success": true,
  "data": {
    "total_visitors_ytd": 12450,
    "active_research_projects": 8,
    "active_tenants": 15,
    "establishment_date": "2020-01-01"
  }
}
```

---

### 6.10 Integration with Kelompok 1 (Central Dashboard)

#### `POST /api/external/integration/sync-data`
- **Description**: Push data to Kelompok 1 central system (scheduled or triggered)
- **Auth**: API Key (header: `X-API-Key`)
- **Request Body**:
```json
{
  "sync_timestamp": "2024-01-16T15:30:00Z",
  "data_type": "production|research|sustainability",
  "data": [...]
}
```
- **Response** (200 OK):
```json
{
  "success": true,
  "message": "Data synchronized successfully",
  "records_synced": 45,
  "sync_id": "sync-001-20240116"
}
```
- **Implementation Notes**:
  - Endpoint ini dipanggil setiap jam atau triggered oleh event besar
  - Log setiap push ke `integration_logs` untuk audit trail
  - Retry logic jika Kelompok 1 temporary unavailable
  - Data yang dikirim: approved data only (status=approved)

---

## 7. LARAVEL IMPLEMENTATION STRUCTURE

### 7.1 Project Directory Structure

```
laravel-kst-ngijo/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── ProductionDataController.php
│   │   │   │   ├── ResearchDataController.php
│   │   │   │   ├── SustainabilityDataController.php
│   │   │   │   ├── ValidationController.php
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── UserController.php
│   │   │   │   └── NotificationController.php
│   │   │   └── Web/ (for legacy blade views if needed)
│   │   ├── Middleware/
│   │   │   ├── CheckRole.php (RBAC middleware)
│   │   │   ├── CheckPermission.php
│   │   │   └── LogActivity.php (audit logging)
│   │   └── Requests/ (Form validation)
│   │       ├── StoreProductionDataRequest.php
│   │       ├── StoreResearchDataRequest.php
│   │       └── ...
│   ├── Models/
│   │   ├── User.php (extends Authenticatable)
│   │   ├── Role.php
│   │   ├── Permission.php
│   │   ├── ProductionData.php
│   │   ├── ResearchProject.php
│   │   ├── ResearchCollaborator.php
│   │   ├── ResearchOutput.php
│   │   ├── Tenant.php
│   │   ├── TenantMetric.php
│   │   ├── SustainabilityData.php
│   │   ├── DataValidation.php (polymorphic)
│   │   ├── AuditLog.php
│   │   └── Notification.php
│   ├── Services/
│   │   ├── ValidationService.php (business logic for validations)
│   │   ├── DashboardService.php (aggregation logic)
│   │   ├── IntegrationService.php (Kelompok 1 sync)
│   │   └── NotificationService.php
│   ├── Events/
│   │   ├── DataSubmitted.php
│   │   ├── DataApproved.php
│   │   ├── DataRejected.php
│   │   └── DataSyncedToExternal.php
│   └── Listeners/
│       ├── SendApprovalNotification.php
│       ├── LogDataChange.php
│       └── SyncToKelompok1.php
├── database/
│   ├── migrations/
│   │   ├── 2024_01_01_000000_create_users_table.php (modified)
│   │   ├── 2024_01_01_000001_create_roles_table.php
│   │   ├── 2024_01_01_000002_create_permissions_table.php
│   │   ├── 2024_01_01_000003_create_production_data_table.php
│   │   ├── 2024_01_01_000004_create_research_projects_table.php
│   │   ├── 2024_01_01_000005_create_research_collaborators_table.php
│   │   ├── 2024_01_01_000006_create_research_outputs_table.php
│   │   ├── 2024_01_01_000007_create_tenants_table.php
│   │   ├── 2024_01_01_000008_create_tenant_metrics_table.php
│   │   ├── 2024_01_01_000009_create_sustainability_data_table.php
│   │   ├── 2024_01_01_000010_create_data_validations_table.php
│   │   ├── 2024_01_01_000011_create_audit_logs_table.php
│   │   ├── 2024_01_01_000012_create_notifications_table.php
│   │   └── 2024_01_01_000013_create_integration_logs_table.php
│   ├── seeders/
│   │   ├── DatabaseSeeder.php
│   │   ├── RoleSeeder.php
│   │   ├── UserSeeder.php
│   │   └── PermissionSeeder.php
│   └── factories/ (for testing)
│       ├── ProductionDataFactory.php
│       ├── ResearchProjectFactory.php
│       └── ...
├── routes/
│   ├── api.php (API routes)
│   ├── web.php (Web routes - if using Blade)
│   └── external.php (Integration endpoints)
├── config/
│   ├── auth.php (JWT config)
│   └── kst.php (custom config - role permissions, etc)
├── tests/
│   ├── Feature/
│   │   ├── AuthTest.php
│   │   ├── ProductionDataApiTest.php
│   │   ├── ResearchDataApiTest.php
│   │   └── ...
│   └── Unit/
│       ├── Models/
│       └── Services/
├── storage/ (logs, exports)
├── .env.example
├── composer.json
└── README.md
```

### 7.2 Key Implementation Steps

#### Step 1: Setup & Configuration
```bash
# Already done in laravel/framework 12
composer install

# Generate APP_KEY
php artisan key:generate

# Setup database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kst-ngijo
DB_USERNAME=root
DB_PASSWORD=

# Install JWT package (untuk authentication)
composer require tymon/jwt-auth:^2.0

# Publish JWT config
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
```

#### Step 2: Create Core Models & Migrations
```bash
# Create models with migrations
php artisan make:model Role --migration
php artisan make:model Permission --migration
php artisan make:model ProductionData --migration
php artisan make:model ResearchProject --migration
php artisan make:model ResearchCollaborator --migration
php artisan make:model ResearchOutput --migration
php artisan make:model Tenant --migration
php artisan make:model TenantMetric --migration
php artisan make:model SustainabilityData --migration
php artisan make:model DataValidation --migration
php artisan make:model AuditLog --migration
php artisan make:model Notification --migration

# Run migrations
php artisan migrate
```

#### Step 3: Create Controllers
```bash
# API Controllers
php artisan make:controller Api/AuthController
php artisan make:controller Api/ProductionDataController --resource
php artisan make:controller Api/ResearchDataController --resource
php artisan make:controller Api/SustainabilityDataController --resource
php artisan make:controller Api/ValidationController
php artisan make:controller Api/DashboardController
php artisan make:controller Api/UserController --resource
php artisan make:controller Api/NotificationController --resource
```

#### Step 4: Setup Routes
In `routes/api.php`:
```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductionDataController;
// ... other imports

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
    Route::get('me', [AuthController::class, 'me'])->middleware('auth:api');
});

Route::middleware('auth:api')->group(function () {
    Route::prefix('internal')->group(function () {
        // Production Data
        Route::apiResource('production-data', ProductionDataController::class);
        Route::get('production-data/my-submissions', [ProductionDataController::class, 'mySubmissions']);
        
        // Research Data
        Route::apiResource('research-data', ResearchDataController::class);
        Route::post('research-data/{id}/outputs', [ResearchDataController::class, 'addOutput']);
        
        // Sustainability Data
        Route::apiResource('sustainability-data', SustainabilityDataController::class);
        
        // Validations (Admin only)
        Route::middleware('role:admin')->group(function () {
            Route::get('validations/pending', [ValidationController::class, 'pending']);
            Route::patch('validations/{id}/approve', [ValidationController::class, 'approve']);
            Route::patch('validations/{id}/reject', [ValidationController::class, 'reject']);
        });
        
        // Dashboards
        Route::get('dashboard/overview', [DashboardController::class, 'overview']);
        Route::get('dashboard/production', [DashboardController::class, 'production']);
        Route::get('dashboard/research', [DashboardController::class, 'research']);
        Route::get('dashboard/sustainability', [DashboardController::class, 'sustainability']);
        Route::get('dashboard/executive', [DashboardController::class, 'executive'])->middleware('role:admin,management');
        
        // Users (Admin only)
        Route::middleware('role:admin')->group(function () {
            Route::apiResource('users', UserController::class);
        });
        
        // Notifications
        Route::apiResource('notifications', NotificationController::class, ['only' => ['index', 'show']]);
        Route::patch('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    });
});

// Public endpoints
Route::prefix('external')->group(function () {
    Route::get('landing-page/info', [PublicController::class, 'landingPageInfo']);
    Route::get('landing-page/stats', [PublicController::class, 'landingPageStats']);
    
    // Integration endpoint (API Key auth)
    Route::middleware('api.key')->group(function () {
        Route::post('integration/sync-data', [IntegrationController::class, 'syncData']);
    });
});
```

#### Step 5: Setup Middleware for RBAC
Create `app/Http/Middleware/CheckRole.php`:
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!auth('api')->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        if (!in_array(auth('api')->user()->role, $roles)) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }
        
        return $next($request);
    }
}
```

Register in `app/Http/Kernel.php`:
```php
protected $routeMiddleware = [
    // ...
    'role' => \App\Http\Middleware\CheckRole::class,
];
```

#### Step 6: Database Seeding (Initial Data)
Create roles, permissions, and sample users:
```bash
php artisan make:seeder RoleSeeder
php artisan make:seeder UserSeeder
php artisan make:seeder PermissionSeeder
```

In `database/seeders/DatabaseSeeder.php`:
```php
public function run(): void
{
    $this->call([
        RoleSeeder::class,
        PermissionSeeder::class,
        UserSeeder::class,
    ]);
}
```

Run seeder:
```bash
php artisan db:seed
```

---

## 8. AUTHENTICATION & AUTHORIZATION (RBAC)

### 8.1 JWT Authentication Setup

The system uses **JWT (JSON Web Tokens)** for API authentication.

**Token Structure**:
```json
{
  "sub": 1,
  "iss": "http://localhost:8000",
  "aud": "http://localhost:8000",
  "iat": 1705435200,
  "exp": 1705438800,
  "data": {
    "id": 1,
    "name": "Operator KST",
    "email": "operator@kst.local",
    "role": "operator"
  }
}
```

**Token Lifespan**: 1 hour (configurable in `config/jwt.php`)

**How to use**:
1. User logs in → GET JWT token
2. Client stores token in localStorage/sessionStorage
3. Client includes token in every API request header: `Authorization: Bearer {token}`
4. Server validates token → grant access

### 8.2 Role-Based Access Control (RBAC)

**Roles Available**:
- `admin` — Full system access, can manage all data & users
- `operator` — Can input production, research, sustainability data; view own submissions
- `management` — Can view all dashboards (read-only), cannot edit data
- `researcher` — Can view research data (read-only)
- `public` — Can access landing page & public stats only

**Permission Matrix**:

| Permission | Admin | Operator | Management | Researcher | Public |
|-----------|-------|----------|-----------|-----------|--------|
| Manage KST Data (CRUD) | ✓ | ✗ | ✗ | ✗ | ✗ |
| Validate Data (approve/reject) | ✓ | ✗ | ✗ | ✗ | ✗ |
| Input Production Data | ✓ | ✓ | ✗ | ✗ | ✗ |
| Input Research Data | ✓ | ✓ | ✗ | ✗ | ✗ |
| Input Sustainability Data | ✓ | ✓ | ✗ | ✗ | ✗ |
| View Production Dashboard | ✓ | ✓ | ✓ | ✗ | ✗ |
| View Research Dashboard | ✓ | ✓ | ✓ | ✓ | ✗ |
| View Sustainability Dashboard | ✓ | ✓ | ✓ | ✗ | ✗ |
| View Executive Dashboard | ✓ | ✗ | ✓ | ✗ | ✗ |
| Manage Users | ✓ | ✗ | ✗ | ✗ | ✗ |
| Access Landing Page | ✓ | ✓ | ✓ | ✓ | ✓ |
| View Public Stats | ✓ | ✓ | ✓ | ✓ | ✓ |

**Implementation in Controllers**:
```php
// In ProductionDataController
public function store(StoreProductionDataRequest $request)
{
    // Only operator or admin can create
    if (!auth('api')->user()->can('create_production_data')) {
        return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
    }
    
    $data = ProductionData::create([
        'date' => $request->date,
        'visitor_count' => $request->visitor_count,
        'visitor_category' => $request->visitor_category,
        'status' => 'pending',
        'created_by_user_id' => auth('api')->id(),
    ]);
    
    return response()->json(['success' => true, 'data' => $data], 201);
}
```

---

## 9. INTEGRATION WITH KELOMPOK 1 (CENTRAL DASHBOARD)

### 9.1 Integration Architecture

```
KST Ngijo System (This project)
    ↓
    └─→ Approved Data (production, research, sustainability)
         ↓
         └─→ Integration Service (Scheduled / Event-driven)
              ↓
              └─→ REST API POST → Kelompok 1 Central Dashboard
                   ↓
                   └─→ Logged in integration_logs table
                        (for audit & reconciliation)
```

### 9.2 Data Sync Strategy

**Frequency**: Hourly sync (configurable via Laravel scheduler)

**Triggered Events**:
- Data approved by admin → immediate sync
- Scheduled sync every hour (for missed events)

**Data Sent**:
- Only `approved` status data
- Aggregated production stats
- Research project summaries
- Sustainability metrics

### 9.3 Integration Endpoint Implementation

In `app/Services/IntegrationService.php`:
```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\ProductionData;
use App\Models\IntegrationLog;

class IntegrationService
{
    protected $externalApiUrl = 'https://kelompok1-api.local/api/external/sync';
    protected $apiKey = env('KELOMPOK1_API_KEY');
    
    public function syncData()
    {
        try {
            // Get approved data
            $productionData = ProductionData::where('status', 'approved')
                ->where('synced_at', null)
                ->get();
            
            $payload = [
                'sync_timestamp' => now()->toIso8601String(),
                'data_type' => 'production',
                'source_system' => 'kst-ngijo',
                'data' => $productionData->toArray(),
            ];
            
            // Send to Kelompok 1
            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->externalApiUrl, $payload);
            
            // Log integration attempt
            IntegrationLog::create([
                'endpoint' => 'kelompok1/sync-data',
                'method' => 'POST',
                'payload' => json_encode($payload),
                'response_status' => $response->status(),
                'response_body' => $response->body(),
                'external_system' => 'kelompok_1',
                'success' => $response->successful(),
                'error_message' => !$response->successful() ? $response->body() : null,
            ]);
            
            if ($response->successful()) {
                // Mark as synced
                $productionData->each(fn($item) => $item->update(['synced_at' => now()]));
            }
            
            return $response;
        } catch (\Exception $e) {
            IntegrationLog::create([
                'endpoint' => 'kelompok1/sync-data',
                'method' => 'POST',
                'success' => false,
                'error_message' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }
}
```

In `app/Console/Kernel.php` (scheduler):
```php
protected function schedule(Schedule $schedule)
{
    // Sync data to Kelompok 1 every hour
    $schedule->call(function () {
        app(IntegrationService::class)->syncData();
    })->hourly();
}
```

---

## 10. REACT FRONTEND INTEGRATION

### 10.1 API Client Setup (Frontend)

Install axios in React project:
```bash
npm install axios
```

Create `src/api/client.js`:
```javascript
import axios from 'axios';

const API_BASE_URL = process.env.REACT_APP_API_URL || 'http://localhost:8000/api';

const client = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Add token to request if exists
client.interceptors.request.use((config) => {
  const token = localStorage.getItem('authToken');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Handle 401 responses (token expired)
client.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('authToken');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default client;
```

### 10.2 API Service Functions (Frontend)

Create service files for each resource:

`src/api/productionDataService.js`:
```javascript
import client from './client';

export const productionDataService = {
  getAll: (params) => client.get('/internal/production-data', { params }),
  getMySubmissions: () => client.get('/internal/production-data/my-submissions'),
  getOne: (id) => client.get(`/internal/production-data/${id}`),
  create: (data) => client.post('/internal/production-data', data),
  update: (id, data) => client.put(`/internal/production-data/${id}`, data),
  delete: (id) => client.delete(`/internal/production-data/${id}`),
};

export default productionDataService;
```

Similarly create: `researchDataService.js`, `sustainabilityDataService.js`, `dashboardService.js`, etc.

### 10.3 Frontend Component Integration Example

`src/pages/ProductionDataForm.jsx`:
```jsx
import React, { useState } from 'react';
import productionDataService from '../api/productionDataService';

function ProductionDataForm() {
  const [formData, setFormData] = useState({
    date: '',
    visitor_count: '',
    visitor_category: 'individuals',
    time_slot: 'morning',
    notes: '',
  });
  const [error, setError] = useState(null);
  const [loading, setLoading] = useState(false);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError(null);
    
    try {
      const response = await productionDataService.create(formData);
      if (response.data.success) {
        alert('Data submitted successfully, pending admin approval');
        setFormData({
          date: '',
          visitor_count: '',
          visitor_category: 'individuals',
          time_slot: 'morning',
          notes: '',
        });
      }
    } catch (err) {
      setError(err.response?.data?.message || 'Error submitting data');
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <input
        type="date"
        name="date"
        value={formData.date}
        onChange={handleChange}
        required
      />
      <input
        type="number"
        name="visitor_count"
        value={formData.visitor_count}
        onChange={handleChange}
        placeholder="Number of visitors"
        required
      />
      <select
        name="visitor_category"
        value={formData.visitor_category}
        onChange={handleChange}
      >
        <option value="individuals">Individuals</option>
        <option value="groups">Groups</option>
        <option value="researchers">Researchers</option>
        <option value="students">Students</option>
      </select>
      <select
        name="time_slot"
        value={formData.time_slot}
        onChange={handleChange}
      >
        <option value="morning">Morning</option>
        <option value="afternoon">Afternoon</option>
        <option value="evening">Evening</option>
      </select>
      <textarea
        name="notes"
        value={formData.notes}
        onChange={handleChange}
        placeholder="Additional notes"
      />
      {error && <p style={{ color: 'red' }}>{error}</p>}
      <button type="submit" disabled={loading}>
        {loading ? 'Submitting...' : 'Submit'}
      </button>
    </form>
  );
}

export default ProductionDataForm;
```

### 10.4 Frontend Environment Setup

Create `.env` file in React project:
```
REACT_APP_API_URL=http://localhost:8000/api
REACT_APP_ENV=development
```

---

## 11. DEPLOYMENT & ENVIRONMENT SETUP

### 11.1 Development Environment

**Requirements**:
- PHP 8.2+ (Laravel 12)
- MySQL 5.7+
- Node.js 16+ (React build)
- Composer

**Setup**:
```bash
# Clone project
git clone <repo-url>
cd laravel-kst-ngijo

# Install dependencies
composer install
npm install

# Setup .env
cp .env.example .env
php artisan key:generate

# Setup JWT
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
php artisan jwt:secret

# Create database
mysql -u root -p
CREATE DATABASE kst-ngijo;

# Run migrations & seeders
php artisan migrate
php artisan db:seed

# Start development servers
php artisan serve # Backend: http://localhost:8000
npm start # Frontend: http://localhost:3000
```

### 11.2 Production Deployment (Shared Hosting)

**Target Hosting**: InfinityFree or 000webhost with cPanel

**Steps**:
1. **Upload Laravel files via FTP**:
   - Upload all files except `node_modules/`, `.git/`, `.env.example`
   - Ensure `public/` is set as document root in cPanel

2. **Configure MySQL Database**:
   - Create database via cPanel
   - Create user with password
   - Grant all privileges

3. **Configure .env**:
   ```
   APP_NAME="KST Ngijo"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://your-domain.com
   
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_DATABASE=your_db_name
   DB_USERNAME=your_db_user
   DB_PASSWORD=your_db_password
   
   JWT_SECRET=your_jwt_secret_here
   
   KELOMPOK1_API_KEY=your_api_key_from_kelompok1
   KELOMPOK1_API_URL=https://kelompok1-api.local/api/external/sync
   ```

4. **Run Migrations**:
   ```bash
   php artisan migrate --force
   php artisan db:seed --force
   ```

5. **Build & Deploy React**:
   ```bash
   npm run build
   # Upload dist/ folder to hosting, configure as separate domain or subdomain
   ```

6. **Configure CORS** (if React on different domain):
   In `config/cors.php`:
   ```php
   'allowed_origins' => [
       'https://your-react-domain.com',
       'https://www.your-react-domain.com',
   ],
   ```

7. **Setup SSL Certificate** (via cPanel AutoSSL)

### 11.3 Environment Variables

**Backend (.env)**:
```
APP_NAME="KST Ngijo"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://api.kst-ngijo.local

LOG_CHANNEL=stack
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=kst_ngijo
DB_USERNAME=kst_user
DB_PASSWORD=strong_password_here

BROADCAST_DRIVER=log
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

JWT_ALGORITHM=HS256
JWT_SECRET=your_generated_secret_here

# External Integration
KELOMPOK1_API_KEY=received_from_kelompok1
KELOMPOK1_API_URL=https://kelompok1-api.local/api/external/sync

# Mail (for notifications)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
```

**Frontend (.env)**:
```
REACT_APP_API_URL=https://api.kst-ngijo.local/api
REACT_APP_ENV=production
```

---

## 📝 DEVELOPMENT CHECKLIST

**Phase 1: Database & Backend (Week 1)**
- [ ] Create all database migrations
- [ ] Seed initial roles, permissions, users
- [ ] Implement authentication (JWT login/logout/refresh)
- [ ] Create CRUD endpoints for production data
- [ ] Create CRUD endpoints for research data
- [ ] Create CRUD endpoints for sustainability data
- [ ] Implement validation queue (approve/reject)
- [ ] Setup audit logging
- [ ] Implement notification system

**Phase 2: Dashboard & Reporting (Week 1.5)**
- [ ] Create dashboard service for data aggregation
- [ ] Implement dashboard endpoints (production, research, sustainability, executive)
- [ ] Setup integration service for Kelompok 1
- [ ] Create integration logging
- [ ] Implement scheduler for hourly sync

**Phase 3: Testing & Refinement (Week 2)**
- [ ] Write API tests
- [ ] Test RBAC scenarios
- [ ] Test integration with Kelompok 1
- [ ] Refine error handling & validation messages
- [ ] Performance optimization
- [ ] Prepare deployment scripts

**Phase 4: Frontend Integration (Parallel with Backend)**
- [ ] Setup React API client
- [ ] Create authentication context/store
- [ ] Build forms for data input
- [ ] Build dashboard components
- [ ] Integrate with backend APIs
- [ ] Testing & refinement

---

## 📚 REFERENCES & USEFUL RESOURCES

- **Laravel 12 Documentation**: https://laravel.com/docs/12
- **JWT-Auth Package**: https://github.com/tymondesigns/jwt-auth
- **MySQL Best Practices**: https://dev.mysql.com/doc/
- **React Hooks Documentation**: https://react.dev/reference/react/hooks
- **Axios Documentation**: https://axios-http.com/docs/intro
- **RESTful API Best Practices**: https://restfulapi.net/

---

## 📞 SUPPORT & TROUBLESHOOTING

**Common Issues**:

1. **JWT Token Expired**
   - Client should call `/api/auth/refresh` endpoint
   - Implement automatic token refresh in React interceptor

2. **CORS Errors**
   - Ensure frontend domain is added to `config/cors.php`
   - Use `axios` with proper headers

3. **Database Connection Error**
   - Verify `.env` database credentials
   - Test connection: `mysql -h host -u user -p database`

4. **Integration with Kelompok 1 Fails**
   - Check `integration_logs` table for error details
   - Verify API key is correct
   - Ensure network connectivity to Kelompok 1 endpoint

---

**Document Version**: 1.0  
**Last Updated**: January 2025  
**Created for**: Kelompok 2 - KST Ngijo Project (Universitas Brawijaya)
