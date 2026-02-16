# Dokumentasi Lengkap Project Task Management System

## 1. Ringkasan Proyek
Aplikasi manajemen tugas berbasis Laravel 12, Livewire, Filament, dan Fortify. Mendukung panel admin, dashboard user, notifikasi, dan sistem role/permission.

---

## 2. Stack Teknologi
- **Backend:** Laravel 12 (PHP 8.2)
- **Frontend:** Livewire 4, Livewire Volt, Filament 5, Vite, Tailwind CSS
- **Auth:** Fortify (login, reset password, email verification, 2FA)
- **Role/Permission:** Spatie Laravel Permission
- **Testing:** PHPUnit
- **Cache:** Redis (default), fallback ke database/file
- **Activity Log:** Spatie Activitylog

---

## 3. Struktur Folder
- `app/Filament`: Resource, Page, Widget untuk admin panel
- `app/Policies`: Policy akses domain
- `app/Providers`: Konfigurasi global, Fortify, Filament
- `app/Livewire`: Komponen Livewire (logout, notifikasi)
- `resources/views/livewire`: Komponen Volt (dashboard, task, notifikasi)
- `resources/views/pages`: Halaman auth/settings berbasis Volt
- `database/migrations`: Skema tabel user, task, permission, activity log
- `database/seeders`: Seeder role, permission, akun default, sample task

---

## 4. Data Model
### User
- Relasi: createdTasks (hasMany), assignedTasks (belongsToMany)
- Trait: Spatie HasRoles

### Task
- Kolom: title, description, status (enum: todo, doing, done), deadline, created_by
- Relasi: creator (belongsTo), assignees (belongsToMany)

### Pivot task_user
- Assignment task ke user, unique key (task_id, user_id)

---

## 5. Role & Permission
- **Role:** admin, supervisor, staff
- **Permission:** users.manage, tasks.manage, tasks.manage.staff
- **Policy:** TaskPolicy (view, create, update, delete, updateStatus)

---

## 6. Routing
- `/`: Halaman welcome
- `/dashboard`: Dashboard user (auth + verified)
- `/settings/*`: Pengaturan user (profile, password, appearance, 2FA)
- `/admin`: Panel Filament

---

## 7. Panel Admin (Filament)
- Path: `/admin` (middleware: auth, role admin/supervisor)
- Resource: TaskResource (CRUD task, filter status/assignee), UserResource (CRUD user, role management), RoleResource
- Widget: Statistik task, status, user
- Polling: TaskResource polling setiap 5 detik

---

## 8. Dashboard User (Livewire Volt)
- Path: `/dashboard`
- Komponen: Statistik task (todo, doing, done), daftar task terkait
- Update status via Livewire Volt
- wire:poll untuk refresh status

---

## 9. Workflow & Notifikasi
- **Create Task:** Admin membuat task, pilih assignee, notifikasi ke user, log assignment
- **Edit Task:** Admin ubah assignee/detail, notifikasi ke user baru, log perubahan
- **Update Status:** Staff ubah status via dashboard, observer kirim notifikasi ke assignee/creator, log status change
- **Notifikasi:** Assignment, status change, deadline soon (via database/email)
- **Audit Trail:** Semua perubahan dicatat oleh Spatie Activitylog

---

## 10. Cache
- Modular service di `app/Services/Cache`
- TaskCacheService: cache daftar tugas, pencarian, assignment
- DashboardCacheService: cache statistik dashboard
- TTL: 60 detik (user-facing), 300 detik (admin stats)
- Invalidasi cache otomatis via observer/service

---

## 11. Testing
- PHPUnit, test workflow assignment, status update, notifikasi
- Test admin/user dashboard, policy, database relasi

---

## 12. Setup & Deployment
- `composer install`, `npm install`, `php artisan migrate --seed`, `php artisan serve`
- Admin user: admin@example.com / password
- Regular user: user@example.com / password

---

## 13. Rekomendasi
- Dokumentasi workflow, cache key, TTL
- Refactor logika notifikasi ke service layer
- Konsistensi policy dan query filtering
- Tambahkan test untuk workflow penting

---

## 14. Completion Status
- Setup & Dependencies ✅
- Database (Users + Tasks) ✅
- User Relationships ✅
- Authentication (Fortify) ✅
- Filament Admin Panel ✅
- Task CRUD Resource ✅
- Filters & Search ✅
- Authorization & Policies ✅
- Livewire Quick Update ✅
- User Dashboard ✅
- Database Seeding ✅
- All Features Tested ✅

**Status:** 🚀 READY FOR PRODUCTION (v1)

---

## 15. Next Steps (v2 Potential)
- Real-time notifications
- Task comments
- File attachments
- Task templates
- Recurring tasks
- Team collaboration
- Export to CSV/PDF
- Mobile app
- Advanced analytics

---

## 16. Useful Commands
```
php artisan migrate:fresh --seed
php artisan test
php artisan config:cache
php artisan serve
```

---

## 17. Referensi File Penting
- `app/Models/Task.php`, `app/Models/User.php`
- `app/Policies/TaskPolicy.php`, `app/Policies/UserPolicy.php`
- `app/Observers/TaskObserver.php`
- `app/Notifications/TaskAssignedNotification.php`, `TaskStatusChangedNotification.php`, `TaskDeadlineSoonNotification.php`
- `app/Services/Cache/TaskCacheService.php`, `DashboardCacheService.php`
- `app/Services/Notification/TaskNotificationService.php`
- `app/Filament/Resources/Tasks/TaskResource.php`, `Users/UserResource.php`, `Roles/RoleResource.php`
- `app/Filament/Widgets/AdminNotificationsWidget.php`, `StatsOverview.php`
- `database/migrations/*`, `database/seeders/DatabaseSeeder.php`
- `resources/views/livewire/*`, `resources/views/pages/*`
- `routes/web.php`, `routes/settings.php`, `routes/api.php`

---

## 18. License
MIT

---

## 19. Author & Contributors
- Tim Developer
- Kontributor: Lihat commit history

---

## 20. Changelog
- v1: Initial release, all features implemented

---

## 21. Catatan Tambahan
- Dokumentasi ini wajib diperbarui setiap ada perubahan besar pada workflow, data model, atau fitur utama.
- Untuk onboarding developer, baca bagian Workflow, Struktur Folder, dan Referensi File.
