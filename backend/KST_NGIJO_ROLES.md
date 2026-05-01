# Role-Based Access Control (RBAC) - Sistem KST Ngijo

## Overview
Sistem KST Ngijo mengimplementasikan tiga role utama dengan permissions yang berbeda-beda. Setiap role memiliki tanggung jawab dan akses spesifik sesuai kebutuhan operasional kawasan sains dan teknologi Universitas Brawijaya.

---

## 1. **Admin**

### Deskripsi
Admin adalah pengguna dengan akses tertinggi di sistem. Role ini bertanggung jawab atas pengelolaan keseluruhan data, konfigurasi sistem, dan manajemen pengguna.

### Fungsi & Kemampuan

#### Data Management
- **Mengelola Data Pengunjung KST Ngijo**
  - Melihat, menambah, mengedit, dan menghapus data pengunjung
  - Mengakses history/log pengunjung secara detail
  - Export data pengunjung untuk analisis

- **Mengelola Data Riset KST Ngijo**
  - Melihat, menambah, mengedit, dan menghapus proyek riset
  - Menetapkan status riset (ongoing, completed, archived)
  - Mengelola researcher profiles dan research teams

- **Mengelola Data Tenant/Bisnis KST Ngijo**
  - Melihat, menambah, mengedit, dan menghapus tenant/bisnis
  - Mengelola kontrak dan lisensi tenant
  - Monitoring aktivitas bisnis dan revenue

#### User Management
- Membuat akun pengguna (Admin, Operator, Publik)
- Mengubah role dan permissions pengguna
- Menonaktifkan atau menghapus akun pengguna
- Reset password pengguna
- Melihat aktivitas/audit log semua pengguna

#### Sistem Configuration
- Mengakses dashboard analytics lengkap
- Mengkonfigurasi settings sistem
- Mengelola integrasi dengan Kelompok 1 (Executive Dashboard UB)
- Melihat dan menganalisis system logs dan errors
- Melakukan backup dan restore data

#### Reporting
- Generate laporan komprehensif (pengunjung, riset, tenant)
- Export data dalam berbagai format (CSV, PDF, Excel)
- Schedule automated reports

---

## 2. **Operator**

### Deskripsi
Operator adalah pengguna tingkat menengah yang bertanggung jawab atas input, verifikasi, dan pemeliharaan data harian. Operator tidak memiliki akses ke konfigurasi sistem atau user management.

### Fungsi & Kemampuan

#### Data Entry & Verification
- **Data Pengunjung KST Ngijo**
  - Melihat dan menambah data pengunjung baru
  - Mengedit data pengunjung yang ada
  - Verifikasi data pengunjung sebelum disimpan
  - Tidak bisa menghapus data (hanya soft-delete dengan approval admin)

- **Data Riset KST Ngijo**
  - Melihat daftar proyek riset
  - Menambah riset baru dengan form terstruktur
  - Mengedit status riset yang sudah ada
  - Update informasi peneliti dan tim riset
  - Tidak bisa menghapus riset (hanya arsipkan)

- **Data Tenant/Bisnis KST Ngijo**
  - Melihat daftar tenant/bisnis aktif
  - Menambah tenant baru dengan dokumentasi
  - Mengedit informasi tenant (kontak, lokasi, kategori)
  - Tidak bisa menghapus tenant (hanya arsipkan)

#### Reporting & Analytics
- Melihat dashboard operasional dasar
- Melihat statistik pengunjung, riset, dan tenant dalam periode tertentu
- Export data (dengan batasan: hanya data yang berhak diakses)
- Generate basic reports

#### Limitations
- **Tidak bisa**: Mengelola user atau role
- **Tidak bisa**: Mengakses system configuration
- **Tidak bisa**: Melihat audit log pengguna lain
- **Tidak bisa**: Melakukan delete permanent (hanya request ke admin)

---

## 3. **Publik / User**

### Deskripsi
Publik adalah pengguna dengan akses terbatas, hanya bisa melihat informasi tertentu, dashboard umum, dan melakukan registrasi/booking pengunjung. Role ini dirancang untuk pengunjung KST Ngijo, tenaga riset eksternal, atau calon tenant. Mereka tidak bisa mengakses sistem internal admin/operator seperti input data, user management, atau konfigurasi sistem.

### Fungsi & Kemampuan

#### Self-Service Features
- **Registrasi Pengunjung**
  - Melakukan self-registration/check-in sebagai pengunjung
  - Mengisi form data diri (nama, email, institusi, tujuan kunjungan)
  - Memilih fasilitas yang ingin dikunjungi
  - Mendapatkan visitor badge/QR code digital

#### Self-Service Features
- **Registrasi Pengunjung**
  - Melakukan self-registration/check-in sebagai pengunjung
  - Mengisi form data diri (nama, email, institusi, tujuan kunjungan)
  - Memilih fasilitas yang ingin dikunjungi
  - Mendapatkan visitor badge/QR code digital

- **Browse Informasi Publik**
  - Melihat daftar tenant/bisnis aktif dengan deskripsi
  - Melihat showcase/portfolio riset (yang di-publish)
  - Melihat informasi fasilitas dan kontak KST Ngijo
  - Melihat jadwal event atau workshop di KST
  - **Melihat semua Dashboard** (Overview, Production, Research, Sustainability, Executive secara publik, tanpa data internal)

#### Data yang Bisa Diakses
- Informasi tenant publik (nama, kategori, deskripsi, kontak)
- Riset showcase (yang di-mark sebagai "public display")
- Fasilitas dan lokasi di KST Ngijo
- Kontak dan jam operasional KST
- **Dashboard lengkap publik** (semua statistik umum tanpa detail operasional)

#### Limitations
- **Tidak bisa**: Melihat data pengunjung lain
- **Tidak bisa**: Melihat data riset detail (hanya yang di-publish)
- **Tidak bisa**: Mengedit atau menghapus apapun
- **Tidak bisa**: Mengakses dashboard analytics internal (hanya overview publik)
- **Tidak bisa**: Mengakses sistem internal admin/operator (input, user management, config)
- **Hanya bisa melihat** informasi dan dashboard yang di-share secara publik

---

## Tabel Perbandingan Role

| Fitur | Admin | Operator | Publik |
|-------|-------|----------|--------|
| **Data Pengunjung** | CRUD + Export | CR(U) + Verify | Self-register |
| **Data Riset** | CRUD + Archive | CR(U) + Archive | View (Published) |
| **Data Tenant** | CRUD + Archive | CR(U) + Archive | View |
| **Dashboard** | ✓ Full Analytics | ✓ Basic Analytics | ✓ All Public Dashboards |
| **User Management** | ✓ Full | ✗ | ✗ |
| **System Config** | ✓ Full | ✗ | ✗ |
| **Analytics** | ✓ Full | ✓ Basic | ✗ |
| **Export Data** | ✓ Full | ✓ Limited | ✗ |
| **Audit Log** | ✓ All Users | ✓ Own Activity | ✗ |
| **Delete Permanent** | ✓ | Request | ✗ |

---

## Implementasi Authentication & Authorization

### Technology Stack
- **Framework**: Laravel 12 (PHP 8.2+)
- **Authentication**: JWT Token + Session
- **Authorization**: Middleware RBAC berbasis role
- **Database**: Role & Permission stored di MySQL

### Flow Umum
1. **Login** → User input email & password
2. **Token Generation** → JWT token dibuat sesuai role
3. **Middleware Check** → Setiap request di-check middleware untuk verifikasi token & role
4. **Authorization** → Controller mengecek permissions spesifik route
5. **Response** → Data dikembalikan sesuai akses level

---

## Catatan untuk Development

- Semua delete operation di-log di audit table
- Role bisa di-extend dengan custom permissions jika diperlukan
- Integration dengan Kelompok 1 hanya bisa diakses oleh Admin
- Session timeout default: 24 jam untuk Admin, 8 jam untuk Operator (Publik tidak perlu login, hanya visitor session untuk badge)
- Setiap action user di-track dalam activity log (untuk Admin review)
- Dashboard publik harus memisahkan data internal dari semua dashboard umum (overview, production, research, sustainability, executive)
