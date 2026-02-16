# Task Management System

Aplikasi manajemen tugas modern berbasis Laravel, Filament, dan Livewire. Mendukung panel admin, dashboard user, notifikasi, role/permission, dan audit trail.

---

## ✨ Fitur Utama
- CRUD Task & Assignment
- Panel Admin (Filament)
- Dashboard User (Livewire Volt)
- Sistem Role & Permission (admin, supervisor, staff)
- Notifikasi (assignment, status change, deadline)
- Statistik & Widget Dashboard
- Audit Trail (Spatie Activitylog)
- Otentikasi lengkap (login, reset password, email verification, 2FA)
- Caching untuk performa
- Testing terintegrasi

---

## 🛠️ Package & Dependensi
- Laravel 12 (PHP 8.2)
- Filament 5
- Livewire 4 & Volt
- Fortify
- Spatie Laravel Permission
- Spatie Activitylog
- Redis (cache)
- PHPUnit (testing)
- Tailwind CSS, Vite

---

## 🔑 Fungsi & Modul Utama
- **TaskResource, UserResource** (Filament CRUD)
- **Livewire Volt**: Komponen dashboard, update status
- **Service**: Cache & Notifikasi
- **Observer**: Event pada Task
- **Policy**: Akses data berbasis role/permission
- **Seeder**: Data awal role, user, task

---

## 🔄 Flow Utama

### 1. Alur Pembuatan & Assignment Task
1. Admin membuat task di panel admin
2. Pilih assignee (user/staff)
3. Notifikasi dikirim ke user terkait
4. Log assignment dicatat

### 2. Alur Update Status Task
1. User mengubah status task di dashboard
2. Observer mendeteksi perubahan
3. Notifikasi dikirim ke assignee/creator
4. Log status change dicatat

### 3. Alur Notifikasi
- Event (assignment/status/deadline) → Notification Service → Database/Email

### 4. Alur Otorisasi
- Role/Permission → Policy → Akses resource (Task/User)

### 5. Alur Caching
- Request → Cache Service → DB/Cache → Response

---

## ⚡ Instalasi & Setup
```bash
composer install
npm install
php artisan migrate --seed
php artisan serve
```

- Admin: admin@example.com / password
- User: user@example.com / password

---

## 📁 Struktur Folder Singkat
- `app/Filament`: Resource, Page, Widget admin
- `app/Livewire`: Komponen dashboard, notifikasi
- `app/Services`: Service cache, notifikasi
- `app/Policies`: Policy akses
- `database/seeders`: Seeder data awal
- `resources/views/livewire`: Komponen Volt

---

## 📊 Diagram Flow (Contoh)

```
sequenceDiagram
    Admin->>System: Create Task
    System->>User: Send Notification
    User->>System: Update Status
    System->>Admin/User: Send Notification
```

---

## 🤝 Kontribusi & Lisensi
- Pull request & issue dipersilakan
- Lisensi: MIT

---

## 👨‍💻 Author & Kontak
- Tim Developer

---

## 📚 Dokumentasi Lengkap
Lihat file `PROJECT_DOCUMENTATION.md` untuk detail arsitektur, workflow, dan referensi file.
