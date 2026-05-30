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

#### `POST /api/auth/login`
Melakukan login user (mengembalikan Sanctum Bearer token).

| Field | Tipe | Wajib | Keterangan |
| :--- | :--- | :---: | :--- |
| `email` | string (email) | ✅ | Email akun pengguna |
| `password` | string | ✅ | Password akun pengguna |

---

#### `POST /api/auth/register`
Mendaftarkan candidate baru (role otomatis = `candidate`).

| Field | Tipe | Wajib | Keterangan |
| :--- | :--- | :---: | :--- |
| `name` | string | ✅ | Nama lengkap |
| `email` | string (email) | ✅ | Email unik, belum terdaftar |
| `password` | string (min: 8) | ✅ | Password baru |
| `password_confirmation` | string | ✅ | Harus sama dengan `password` |

---

### Public Vacancy & Application

- `GET /api/vacancies` - Melihat daftar lowongan pekerjaan yang terbuka
- `GET /api/vacancies/{id}` - Melihat detail lowongan pekerjaan
- `GET /api/track/{applicationCode}` - Melacak status lamaran menggunakan kode lamaran

#### `POST /api/applications/generate-code`
Membuat `application_code` unik berdasarkan email kandidat dan ID lowongan. Ini adalah **langkah pertama** sebelum kandidat mengirim lamaran lengkap. Jika kandidat dengan email yang sama sudah pernah apply ke lowongan yang sama, kode yang sudah ada akan dikembalikan (idempotent).

| Field | Tipe | Wajib | Keterangan |
| :--- | :--- | :---: | :--- |
| `email` | string (email) | ✅ | Email kandidat |
| `vacancy_id` | integer | ✅ | ID lowongan yang dituju (harus berstatus `open`) |

> **Response `201`** — Kode baru berhasil dibuat.
> **Response `200`** — Kode sudah ada (email + vacancy_id sudah pernah didaftarkan).

#### `POST /api/apply`
Mengirim lamaran ke lowongan tertentu. Request menggunakan `multipart/form-data` karena mendukung upload file.

| Field | Tipe | Wajib | Keterangan |
| :--- | :--- | :---: | :--- |
| `vacancy_id` | integer | ✅ | ID lowongan yang dituju (harus berstatus `open`) |
| `name` | string | ✅ | Nama lengkap kandidat |
| `email` | string (email) | ✅ | Email kandidat |
| `phone` | string (max: 20) | ✅ | Nomor telepon kandidat |
| `gender` | string (`L` / `P`) | ✅ | Jenis kelamin |
| `city` | string | ✅ | Kota domisili |
| `zip_code` | string (max: 10) | ✅ | Kode pos |
| `complete_address` | string | ✅ | Alamat lengkap |
| `experience` | string | ✅ | Pengalaman kerja kandidat |
| `web_portofolio_url` | string (URL) | ❌ | URL portofolio online |
| `cv` | file (pdf/doc/docx, max 5MB) | ❌ | File CV kandidat |
| `portofolio` | file (pdf/zip, max 10MB) | ❌ | File portofolio kandidat |

---

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

#### `POST /api/auth/logout`
Melakukan logout (mencabut token aktif). Tidak memerlukan request body.

---

## 👑 ADMIN ROUTES (`role:admin`)

### User Management
- `GET /api/admin/users` - Mengambil daftar seluruh user
- `GET /api/admin/users/{id}` - Melihat detail user
- `PUT /api/admin/users/{id}` - Memperbarui data user
- `PATCH /api/admin/users/{id}/role` - Memperbarui role/peran user
- `PATCH /api/admin/users/{id}/status` - Memperbarui status user (aktif/nonaktif)
- `DELETE /api/admin/users/{id}` - Menghapus user

#### `POST /api/admin/users`
Membuat user baru (HR, Supervisor, atau Employee). Candidate dibuat via `/api/auth/register`.

| Field | Tipe | Wajib | Keterangan |
| :--- | :--- | :---: | :--- |
| `name` | string | ✅ | Nama lengkap user |
| `email` | string (email) | ✅ | Email unik, belum terdaftar |
| `password` | string (min: 8) | ✅ | Password untuk akun baru |
| `role` | string | ✅ | Role user: `hr` / `supervisor` / `employee` |
| `status` | string | ❌ | Status akun: `active` (default) / `inactive` |

---

### Department Management
- `GET /api/admin/departments` - Mengambil daftar departemen
- `GET /api/admin/departments/{id}` - Melihat detail departemen
- `PUT /api/admin/departments/{id}` - Memperbarui departemen
- `DELETE /api/admin/departments/{id}` - Menghapus departemen

#### `POST /api/admin/departments`
Menambahkan departemen baru ke dalam sistem.

| Field | Tipe | Wajib | Keterangan |
| :--- | :--- | :---: | :--- |
| `department_name` | string (max: 255) | ✅ | Nama departemen, harus unik |
| `is_active` | boolean | ❌ | Status aktif departemen (default: `true`) |

---

### Position Management
- `GET /api/admin/positions` - Mengambil daftar posisi
- `GET /api/admin/positions/{id}` - Melihat detail posisi
- `PUT /api/admin/positions/{id}` - Memperbarui data posisi
- `DELETE /api/admin/positions/{id}` - Menghapus posisi

#### `POST /api/admin/positions`
Menambahkan posisi baru ke dalam departemen tertentu.

| Field | Tipe | Wajib | Keterangan |
| :--- | :--- | :---: | :--- |
| `department_id` | integer | ✅ | ID departemen yang menaungi posisi ini |
| `position_name` | string (max: 255) | ✅ | Nama posisi (unik dalam satu departemen) |
| `is_active` | boolean | ❌ | Status aktif posisi (default: `true`) |

---

### DSS MAIRCA Criteria Management
- `GET /api/admin/criteria` - Mengambil daftar kriteria (untuk perhitungan MAIRCA)
- `GET /api/admin/criteria/{id}` - Melihat detail kriteria
- `PUT /api/admin/criteria/{id}` - Memperbarui kriteria
- `DELETE /api/admin/criteria/{id}` - Menghapus kriteria
- `GET /api/admin/criteria/{id}/likert` - Mengambil skala Likert untuk kriteria tertentu
- `DELETE /api/admin/criteria/{id}/likert/{scaleId}` - Menghapus skala Likert pada kriteria

#### `POST /api/admin/criteria`
Menambahkan kriteria baru untuk suatu posisi. Total bobot per posisi tidak boleh melebihi 100%.

| Field | Tipe | Wajib | Keterangan |
| :--- | :--- | :---: | :--- |
| `position_id` | integer | ✅ | ID posisi yang menggunakan kriteria ini |
| `name` | string (max: 255) | ✅ | Nama kriteria (misal: "Kemampuan Komunikasi") |
| `weight` | number (0–100) | ✅ | Bobot kriteria dalam persen |
| `type` | string | ✅ | Tipe kriteria: `benefit` / `cost` |
| `data_type` | string | ✅ | Tipe data: `kualitatif` / `kuantitatif` |
| `description` | string | ❌ | Deskripsi tambahan kriteria |

#### `POST /api/admin/criteria/{id}/likert`
Menambahkan opsi skala Likert pada kriteria bertipe `kualitatif`.

| Field | Tipe | Wajib | Keterangan |
| :--- | :--- | :---: | :--- |
| `label` | string (max: 255) | ✅ | Label opsi (misal: "Sangat Baik") |
| `value` | number | ✅ | Nilai numerik dari opsi skala ini |

---

### Reports (Read-only)
- `GET /api/admin/reports/recruitment` - Mengambil laporan rekrutmen

---

## 👩‍💼 HR ROUTES (`role:hr`)

### Vacancy Management
- `GET /api/hr/vacancies` - Mengambil semua data lowongan
- `GET /api/hr/vacancies/{id}` - Melihat detail lowongan
- `PUT /api/hr/vacancies/{id}` - Memperbarui data lowongan
- `PATCH /api/hr/vacancies/{id}/close` - Menutup lowongan (ubah status ke `closed`)
- `DELETE /api/hr/vacancies/{id}` - Menghapus lowongan

#### `POST /api/hr/vacancies`
Membuat lowongan pekerjaan baru. `hr_id` diisi otomatis dari token HR yang login.

| Field | Tipe | Wajib | Keterangan |
| :--- | :--- | :---: | :--- |
| `position_id` | integer | ✅ | ID posisi yang dibuka lowongannya |
| `title` | string (max: 255) | ✅ | Judul lowongan |
| `description` | string | ✅ | Deskripsi pekerjaan |
| `requirements` | string | ✅ | Persyaratan pelamar |
| `deadline` | string (date) | ✅ | Batas waktu pendaftaran (harus setelah hari ini) |

---

### Application Management
- `GET /api/hr/applications` - Mengambil semua data lamaran masuk
- `GET /api/hr/applications/{id}` - Melihat detail lamaran
- `PATCH /api/hr/applications/{id}/screening` - Memindahkan status lamaran ke tahap *screening*
- `PATCH /api/hr/applications/{id}/reject` - Menolak lamaran

### Interview Management
- `GET /api/hr/interviews` - Mengambil jadwal interview
- `GET /api/hr/interviews/{id}` - Melihat detail interview
- `PUT /api/hr/interviews/{id}` - Memperbarui detail/jadwal interview
- `GET /api/hr/interviews/{id}/scores` - Melihat nilai interview kandidat

#### `POST /api/hr/interviews`
Menjadwalkan sesi interview untuk lamaran yang berstatus `screening`. Mengubah status lamaran menjadi `interview_scheduled`.

| Field | Tipe | Wajib | Keterangan |
| :--- | :--- | :---: | :--- |
| `application_id` | integer | ✅ | ID lamaran (harus berstatus `screening`) |
| `interviewer_id` | integer | ✅ | ID user yang bertugas sebagai interviewer |
| `interview_date` | string (datetime) | ✅ | Tanggal & waktu interview (harus setelah sekarang) |
| `notes` | string | ❌ | Catatan tambahan untuk sesi interview |

#### `POST /api/hr/interviews/{id}/scores`
Menyimpan nilai/scoring MAIRCA per kriteria setelah interview selesai. Jika semua kriteria terisi, status lamaran otomatis berubah menjadi `interview_done`.

| Field | Tipe | Wajib | Keterangan |
| :--- | :--- | :---: | :--- |
| `scores` | array | ✅ | Array objek skor per kriteria |
| `scores[].criteria_id` | integer | ✅ | ID kriteria yang dinilai |
| `scores[].score` | number (0–100) | ✅ | Nilai untuk kriteria tersebut |

---

### DSS MAIRCA — Kalkulasi & Ranking
- `GET /api/hr/mairca/ranking/{vacancyId}` - Melihat hasil perankingan MAIRCA pada lowongan tersebut

#### `POST /api/hr/mairca/calculate/{vacancyId}`
Menjalankan kalkulasi perhitungan MAIRCA pada suatu lowongan. Parameter `vacancyId` dikirim via **path** (URL), bukan request body.

> Tidak ada field request body — ID lowongan diambil langsung dari parameter URL.

---

### Keputusan Final & Onboarding

#### `POST /api/hr/decisions/{applicationId}`
Memberikan keputusan final untuk lamaran berstatus `interview_done`. Membutuhkan hasil kalkulasi MAIRCA terlebih dahulu.

| Field | Tipe | Wajib | Keterangan |
| :--- | :--- | :---: | :--- |
| `decission` | string | ✅ | Keputusan: `hired` / `rejected` |
| `notes` | string | ❌ | Catatan keputusan (opsional) |

#### `POST /api/hr/onboarding/{applicationId}`
Melakukan proses onboarding untuk kandidat yang berstatus `hired`. Otomatis membuat/memperbarui user dengan role `employee` dan record data karyawan.

| Field | Tipe | Wajib | Keterangan |
| :--- | :--- | :---: | :--- |
| `department_id` | integer | ✅ | ID departemen tempat karyawan ditempatkan |
| `position_id` | integer | ✅ | ID posisi yang diisi oleh karyawan |
| `supervisor_id` | integer | ✅ | ID employee yang menjadi atasan langsung |
| `join_date` | string (date) | ✅ | Tanggal mulai bergabung |
| `contract_status` | string | ✅ | Status kontrak: `permanent` / `contract` / `probation` |

---

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
