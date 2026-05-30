# HRIS MAIRCA - API Documentation

Aplikasi ini menggunakan **Laravel Sanctum** untuk autentikasi API berbasis token (Bearer Token) dan mengimplementasikan arsitektur berbasis peran (Role-Based Access Control) dengan middleware `role`.

## Base Configuration
- **Base URL:** `/api`
- **Authentication:** Bearer Token (Laravel Sanctum)
- **Swagger UI Documentation:** `/api/documentation` (ter-generate melalui l5-swagger)

## 📌 Role Hierarchy & Middleware
| Middleware | Keterangan |
| :--- | :--- |
| `public` | Rute tanpa perlu autentikasi |
| `auth:sanctum` | Semua role yang sudah login memiliki akses |
| `role:admin` | Hanya untuk Admin |
| `role:hr` | Hanya untuk HR |
| `role:supervisor`| Hanya untuk Supervisor |
| `role:employee` | Hanya untuk Employee |
| `role:candidate` | Hanya untuk Candidate |

---

## 🔓 PUBLIC ROUTES (Tanpa Autentikasi)

### Authentication
- `POST /api/auth/login` - Melakukan login user (mengembalikan token)
- `POST /api/auth/register` - Mendaftarkan candidate baru

### Public Vacancy & Application (Sesuai SRS)
- `GET /api/vacancies` - Melihat daftar lowongan pekerjaan yang terbuka
- `GET /api/vacancies/{id}` - Melihat detail lowongan pekerjaan
- `POST /api/apply` - Mengirim lamaran pekerjaan (apply)
- `GET /api/track/{applicationCode}` - Melacak status lamaran menggunakan kode lamaran

### Public Departments
- `GET /api/departments` - Melihat daftar departemen yang aktif
  - Query param `?is_active=false` untuk melihat departemen non-aktif
- `GET /api/departments/{id}` - Melihat detail departemen beserta daftar posisi aktifnya

### Public Positions
- `GET /api/positions` - Melihat daftar posisi yang aktif beserta departemennya
  - Query param `?department_id={id}` untuk filter berdasarkan departemen
  - Query param `?is_active=false` untuk melihat posisi non-aktif
- `GET /api/positions/{id}` - Melihat detail posisi beserta departemennya

---

## 🔐 AUTHENTICATED ROUTES (Perlu Bearer Token)

### General Auth Actions
- `GET /api/auth/me` - Mengambil profil user yang sedang login
- `POST /api/auth/logout` - Melakukan logout (mencabut token)

---

## 👑 ADMIN ROUTES (`role:admin`)

### User Management
- `GET /api/admin/users` - Mengambil daftar seluruh user
- `POST /api/admin/users` - Membuat user baru
- `GET /api/admin/users/{id}` - Melihat detail user
- `PUT /api/admin/users/{id}` - Memperbarui data user
- `PATCH /api/admin/users/{id}/role` - Memperbarui role/peran user
- `PATCH /api/admin/users/{id}/status` - Memperbarui status user (aktif/nonaktif)
- `DELETE /api/admin/users/{id}` - Menghapus user

### Department Management
- `GET /api/admin/departments` - Mengambil daftar departemen
- `POST /api/admin/departments` - Menambahkan departemen baru
- `GET /api/admin/departments/{id}` - Melihat detail departemen
- `PUT /api/admin/departments/{id}` - Memperbarui departemen
- `DELETE /api/admin/departments/{id}` - Menghapus departemen

### Position Management
- `GET /api/admin/positions` - Mengambil daftar posisi
- `POST /api/admin/positions` - Menambahkan posisi baru
- `GET /api/admin/positions/{id}` - Melihat detail posisi
- `PUT /api/admin/positions/{id}` - Memperbarui data posisi
- `DELETE /api/admin/positions/{id}` - Menghapus posisi

### DSS MAIRCA Criteria Management
- `GET /api/admin/criteria` - Mengambil daftar kriteria (untuk perhitungan MAIRCA)
- `POST /api/admin/criteria` - Menambahkan kriteria baru
- `GET /api/admin/criteria/{id}` - Melihat detail kriteria
- `PUT /api/admin/criteria/{id}` - Memperbarui kriteria
- `DELETE /api/admin/criteria/{id}` - Menghapus kriteria
- `GET /api/admin/criteria/{id}/likert` - Mengambil skala Likert untuk kriteria tertentu
- `POST /api/admin/criteria/{id}/likert` - Menambahkan skala Likert pada kriteria
- `DELETE /api/admin/criteria/{id}/likert/{scaleId}` - Menghapus skala Likert pada kriteria

### Reports (Read-only)
- `GET /api/admin/reports/recruitment` - Mengambil laporan rekrutmen

---

## 👩‍💼 HR ROUTES (`role:hr`)

### Vacancy Management
- `GET /api/hr/vacancies` - Mengambil semua data lowongan
- `POST /api/hr/vacancies` - Membuat lowongan baru
- `GET /api/hr/vacancies/{id}` - Melihat detail lowongan
- `PUT /api/hr/vacancies/{id}` - Memperbarui data lowongan
- `PATCH /api/hr/vacancies/{id}/close` - Menutup lowongan (ubah status)
- `DELETE /api/hr/vacancies/{id}` - Menghapus lowongan

### Application Management
- `GET /api/hr/applications` - Mengambil semua data lamaran masuk
- `GET /api/hr/applications/{id}` - Melihat detail lamaran
- `PATCH /api/hr/applications/{id}/screening` - Memindahkan status lamaran ke tahap *screening*
- `PATCH /api/hr/applications/{id}/reject` - Menolak lamaran

### Interview Management
- `GET /api/hr/interviews` - Mengambil jadwal interview
- `POST /api/hr/interviews` - Menjadwalkan interview
- `GET /api/hr/interviews/{id}` - Melihat detail interview
- `PUT /api/hr/interviews/{id}` - Memperbarui detail/jadwal interview
- `GET /api/hr/interviews/{id}/scores` - Melihat nilai interview kandidat
- `POST /api/hr/interviews/{id}/scores` - Menyimpan nilai/scoring interview

### DSS MAIRCA — Kalkulasi & Ranking
- `POST /api/hr/mairca/calculate/{vacancyId}` - Menjalankan kalkulasi perhitungan MAIRCA pada suatu lowongan
- `GET /api/hr/mairca/ranking/{vacancyId}` - Melihat hasil perankingan MAIRCA pada lowongan tersebut

### Keputusan Final & Onboarding
- `POST /api/hr/decisions/{applicationId}` - Memberikan keputusan final untuk lamaran
- `POST /api/hr/onboarding/{applicationId}` - Melakukan proses onboarding pada kandidat terpilih

### Employee Management
- `GET /api/hr/employees` - Mengambil daftar seluruh karyawan
- `GET /api/hr/employees/{id}` - Melihat profil detail karyawan
- `PUT /api/hr/employees/{id}` - Memperbarui data karyawan

---

## 🧑‍💼 SUPERVISOR ROUTES (`role:supervisor`)
- `GET /api/supervisor/profile` - Mengambil data dashboard/profil untuk supervisor

---

## 👤 EMPLOYEE ROUTES (`role:employee`)
- `GET /api/employee/profile` - Melihat profil diri sendiri (karyawan)
- `PUT /api/employee/profile` - Memperbarui data profil diri (karyawan)
- `GET /api/employee/employment` - Melihat detail kepegawaian diri (karyawan)

---

## 🙋 CANDIDATE ROUTES (`role:candidate`)
- `GET /api/candidate/applications` - Melihat daftar lamaran yang pernah di-apply oleh candidate yang sedang login (membutuhkan register & login sebagai candidate)
