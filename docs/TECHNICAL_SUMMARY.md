# Resume Teknis Proyek

## Ringkasan Stack
- Laravel 12 + PHP 8.2
- Livewire 4 dan Livewire Volt (single-file components)
- Filament 5 (admin panel)
- Fortify (auth, reset password, email verification, 2FA)
- Spatie Permission (role/permission)
- Vite + Tailwind CSS
- PHPUnit (testing)

## Struktur Folder Penting
- app/Filament: Resource, Page, Widget untuk panel admin
- app/Policies: Policy akses domain
- app/Providers: konfigurasi global, Fortify, dan Filament panel
- app/Livewire: action class Livewire (logout)
- resources/views/livewire: komponen Volt (anonymous class + view)
- resources/views/pages: halaman auth dan settings berbasis Volt
- database/migrations: skema tabel user, task, pivot, dan permission
- database/seeders: data awal role, permission, dan akun

## Arsitektur Modul
### Autentikasi dan Keamanan
- Fortify mengaktifkan: reset password, email verification, dan 2FA.
- Rate limit login dan 2FA diatur di FortifyServiceProvider.
- Default password policy diperketat di production (min 12, mix case, angka, simbol, uncompromised).
- Middleware panel admin mengecek role admin/supervisor.
- Destructive DB command diblok saat production.

### Role dan Permission
- Role: admin, supervisor, staff.
- Permission utama:
  - users.manage
  - tasks.manage
  - tasks.manage.staff
- Policy Task mengatur akses view, create, update, delete, dan updateStatus.

### Filament Admin Panel
- Panel admin tersedia pada path /admin dengan middleware autentikasi dan cek role admin/supervisor.
- Resource tersedia:
  - TaskResource: CRUD task, filter status dan assignee.
  - UserResource: CRUD user, pengaturan role.
- Widget dashboard: statistik total task, status, dan user.
- TaskResource polling setiap 5 detik untuk data table.

### Dashboard User (Livewire Volt)
- Halaman /dashboard menampilkan komponen Livewire Volt.
- Menampilkan statistik task (todo, doing, done) dan daftar task terkait.
- Update status dilakukan via komponen Livewire Volt terpisah.
- Komponen menggunakan `wire:poll` untuk refresh status task.

## Data Model
### User
- Relasi:
  - createdTasks: hasMany Task (created_by)
  - assignedTasks: belongsToMany Task (pivot task_user)
- Trait Spatie HasRoles untuk role/permission.

### Task
- Kolom utama: title, description, status, deadline, created_by.
- Status enum: todo, doing, done.
- Relasi:
  - creator: belongsTo User (created_by)
  - assignees: belongsToMany User (pivot task_user)
- Cast deadline ke datetime.

### Pivot task_user
- Menyimpan assignment task ke user.
- Unique key (task_id, user_id).

## Alur Akses
- Admin: akses penuh ke semua task dan user.
- Supervisor: akses task dengan permission tasks.manage.staff (khusus alur assignment ke staff).
- Staff: akses task yang di-assign ke dirinya.
- Update status task dibatasi oleh policy `updateStatus`.

## Routing Utama
- /: halaman welcome
- /dashboard: halaman dashboard user (auth + verified)
- /settings/*: halaman pengaturan user (profile, password, appearance, two-factor)
- /admin: panel Filament
- Route settings memakai Livewire Volt dengan namespace `pages::settings.*`.

## Seeder dan Data Awal
- Seeder membuat role, permission, serta akun default (admin, supervisor, staff).
- Seeder membuat sample task dan assignment ke staff saat database kosong.

## Catatan Implementasi & Workflow
- Livewire Volt menggunakan file blade tunggal yang berisi class anonymous + view.
- Task status update dibatasi hanya perubahan status (todo/doing/done).
- Query task untuk role supervisor diarahkan pada task dengan assignee staff.
- Form Task memuat assignee khusus role staff.
- Form User mengatur role dan hash password saat create/update.

### Workflow Task & Notifikasi
- **Create Task (Admin/Filament):**
  - Admin membuat task di panel Filament.
  - Setelah create, assignee dipilih dan notifikasi dikirim ke user terkait.
  - Log assignment dicatat di activity log.
- **Edit Task (Admin/Filament):**
  - Admin mengubah assignee atau detail task.
  - Jika assignee berubah, notifikasi dikirim ke user baru.
  - Log perubahan dicatat di activity log.
- **Update Status (Staff/User Dashboard):**
  - Staff mengubah status task via Livewire Volt.
  - Observer (`TaskObserver`) mendeteksi perubahan status dan mengirim notifikasi ke assignee dan creator.
  - Log status change dicatat di activity log.
- **Notifikasi:**
  - Tiga jenis notifikasi: assignment, status change, deadline soon.
  - Notifikasi dikirim via database dan email.
  - Komponen Livewire dan Filament Widget menampilkan notifikasi sesuai role.
- **Audit Trail:**
  - Semua perubahan task (assignment, status, deadline) dicatat otomatis oleh Spatie Activitylog.
  - Audit trail dapat diekspor jika diperlukan.

### Pemanggilan Fungsi & Efisiensi
- Logika notifikasi assignment dan status tersebar di Filament Page dan Observer.
- Untuk maintainability, disarankan refactor ke service/helper agar satu sumber logika.
- Policy dan query filtering harus konsisten di semua tempat (Filament, Livewire, Controller).
- Hindari duplikasi logika statistik dan notifikasi antara dashboard admin dan staff.
- Tambahkan test untuk workflow assignment, status update, dan notifikasi.
- Optimalkan query dengan eager loading jika volume data besar.

### Rekomendasi
- Dokumentasikan alur workflow di file ini agar onboarding developer lebih mudah.
- Refactor logika notifikasi dan assignment ke service layer jika project berkembang.
- Pastikan policy dan query filtering konsisten.
- Tambahkan test untuk workflow penting.

---

## Dependency Kunci
- filament/filament
- laravel/fortify
- livewire/livewire
- livewire/flux
- spatie/laravel-permission
- laravel/tinker
- laravel/boost (dev)

## Testing
- PHPUnit terpasang.
- Belum terlihat test spesifik policy atau resource, bisa ditambahkan untuk skenario akses role.

## Saran Peningkatan
- Tambahkan test policy untuk `TaskPolicy` dan visibilitas query di `TaskResource`.
- Tambahkan audit event untuk perubahan status task (opsional).
- Dokumentasikan alur assignment task untuk supervisor dan staff di README.
