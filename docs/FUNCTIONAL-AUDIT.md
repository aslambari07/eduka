# EDUKA Functional Audit

Tanggal verifikasi: 27 Oktober 2026  
Workspace: `/home/aslam/Desktop/EDUKA`  
Database test: MySQL terpisah `eduka_rebuild_testing` dari `phpunit.xml`

## Ringkasan hasil

Alur inti Admin, Pengajar, dan Peserta sudah diimplementasikan dan diuji secara otomatis. Evaluasi penyelesaian sekarang terpusat di `App\Services\CourseCompletionService`; controller hanya menyimpan event domain lalu memanggil service tersebut.

Hasil verifikasi terakhir:

- `php artisan test --compact`: **102 passed, 717 assertions**.
- `php artisan view:cache`: **passed**.
- `npm run build`: **passed** dengan Vite 7.3.7.
- `php artisan migrate:status --no-ansi`: seluruh **20 migration Ran**.
- `php artisan route:list --no-ansi`: **69 routes**, tanpa nama route duplikat.
- HTTP smoke publik dan tiga role: berhasil pada halaman utama dan route role utama.
- Browser smoke lokal: login Admin, Pengajar, dan Peserta berhasil; dashboard ketiga role berhasil dirender. Screenshot homepage desktop/mobile, katalog desktop, dashboard Admin/Pengajar, dan dashboard Peserta mobile telah diambil serta diperiksa.

## Inventaris halaman dan alur Admin

| Area | Route/view | Status |
|---|---|---|
| Dashboard | `GET /admin` → `admin/dashboard.blade.php` | Lulus smoke dan feature test |
| Pengguna | `GET /admin/pengguna` → `admin/users.blade.php` | Lulus; role/status memiliki otorisasi admin |
| Kategori | `GET /admin/kategori` → `admin/categories.blade.php` | CRUD create/update/delete lulus |
| Moderasi kelas | `GET /admin/kelas` dan `POST /admin/kelas/{course}/status` → `admin/courses.blade.php` | Lulus; status/visibility tetap server-side |
| Laporan | `GET /admin/laporan` → `admin/reports.blade.php` | Lulus; progres memakai completion service |

Perbaikan yang diverifikasi: kategori tidak lagi hanya create, penghapusan kategori memutus relasi course secara aman, laporan tidak lagi menghitung progres dari jumlah lesson saja, dan semua workspace memakai navigasi role yang nyata.

## Inventaris halaman dan alur Pengajar

| Area | Route/view | Status |
|---|---|---|
| Dashboard | `GET /pengajar` → `instructor/dashboard.blade.php` | Lulus smoke |
| Kelas | `GET/POST/PUT/DELETE /pengajar/kelas` → `instructor/courses.blade.php` | Create/read/update/delete lulus; kelas dengan aktivitas diarsipkan |
| Editor materi | `GET/POST/PUT/DELETE /pengajar/kelas/{course}/materi` → `instructor/content.blade.php` | Lulus; kepemilikan course wajib |
| Kuis | `GET/POST/PUT/DELETE /pengajar/kelas/{course}/kuis` | Lulus; `pass_score` assignment dibatasi terhadap poin |
| Pertanyaan kuis | `POST/PUT/DELETE /pengajar/kuis/{quiz}/pertanyaan` | Create/update/delete lulus; pertanyaan terjawab tidak boleh dihapus |
| Pengumpulan | `GET /pengajar/pengumpulan` dan `POST .../nilai` → `instructor/submissions.blade.php` | Lulus; hanya submission course milik pengajar |

Form authoring menyediakan flag `is_required` dan `is_published`. Penghapusan lesson/quiz/assignment yang sudah memiliki data belajar tidak menghapus histori; konten tersebut diarsipkan dengan unpublished state.

## Inventaris halaman dan alur Peserta

| Area | Route/view | Status |
|---|---|---|
| Dashboard/progres | `GET /dashboard` → `dashboard.blade.php` | Progres berasal dari required published requirements |
| Katalog/detail | `GET /kelas`, `GET /kelas/{course:slug}` | Lulus; lesson draft tidak tampil sebagai kurikulum publik |
| Learning player | `GET/POST /belajar/{course:slug}/{lesson:slug}` | Lulus; lesson draft ditolak termasuk direct POST completion |
| Quiz | `GET/POST /belajar/{course:slug}/kuis/{quiz:slug}` | Server-scored; quiz tanpa pertanyaan ditolak |
| Tugas | `GET/POST /belajar/{course:slug}/tugas/{assignment}` | Upload private, deadline dan tipe file divalidasi |
| Catatan | `GET/POST/PUT/DELETE /belajar/{course:slug}/catatan` | Ownership peserta diverifikasi |
| Profil | `GET/PUT /profil`, `PUT /profil/kata-sandi` | Lulus smoke/feature test |
| Sertifikat | `GET /sertifikat`, owner-only `GET /sertifikat/{code}`, `GET /sertifikat/{code}/unduh` | Record tersimpan; halaman dan PDF A4 owner-only |
| Verifikasi publik | `GET /verifikasi/sertifikat/{code}` | Hanya menampilkan validitas, course, code, tanggal; tidak membocorkan identitas peserta |

## Completion, nilai, dan sertifikat

`CourseCompletionService` menghitung persyaratan dari database, bukan nilai progres dari frontend:

- hanya materi, quiz, dan tugas yang `is_published = true` serta `is_required = true` yang dihitung;
- quiz/tugas yang terikat pada lesson draft tidak eligible;
- materi selesai jika ada `lesson_progress.completed_at`;
- quiz selesai jika ada attempt `passed = true`;
- tugas selesai hanya jika submission berstatus `graded` dan `score >= pass_score`;
- kelas tanpa required published content menghasilkan `0%` dan tidak dapat disertifikasi;
- status `submitted`, `pending`, dan `needs_revision` tidak memenuhi syarat;
- setiap perubahan lesson completion, quiz submission, dan assignment grading memanggil service;
- enrollment dan certificate dibuat/diperbarui dalam transaksi; unique constraint `certificates(course_id, user_id)` mencegah duplikasi;
- pemanggilan berulang menghasilkan satu certificate code yang sama.

Test yang membuktikan alur ini mencakup progres 33/50/100 persen, komponen opsional, quiz gagal, tugas perlu revisi, penerbitan dari lesson terakhir, quiz terakhir, grading tugas terakhir, retry/idempotensi, akses certificate peserta lain, unduhan owner-only, dan quiz kosong. `CertificateAcceptanceTest` menambahkan alur request-to-database penuh: pendaftaran kelas, penyelesaian materi, submit quiz bernilai lulus, submit tugas, grading pengajar, verifikasi record certificate/code/owner/course/tanggal, unduhan file, evaluasi berulang tanpa certificate kedua, dan akses privat peserta lain. `DemoCourseEndToEndTest` mengulang alur ini pada keempat kelas demo seeded.

## Flag wajib/opsional dan data lama

Migration `2026_10_27_000017_add_required_flags_to_learning_content` menambahkan `is_required` ke `lessons`, `quizzes`, dan `assignments` dengan default `true`. Ini additive, tidak menghapus atau memigrasikan data lama secara destruktif. Model melakukan cast boolean dan form pengajar dapat mengubah flag tersebut.

## DemoCourseSeeder

Seeder terpisah: `database/seeders/DemoCourseSeeder.php`.

Perintah eksplisit untuk local/testing:

```bash
php artisan db:seed --class=DemoCourseSeeder
```

Seeder menolak environment selain `local`/`testing`, tidak mereset password account pengajar yang sudah ada, tidak mengambil alih course slug existing milik pengajar lain, tidak menghapus atau menimpa course existing, dan idempotent. Ia menyediakan empat course pada database yang belum memiliki slug tersebut:

1. Dasar Pemrograman Web
2. Laravel untuk Pemula
3. UI/UX Design Dasar
4. Database dan SQL

Setiap course berisi lima lesson bermakna yang published/required dan satu quiz published/required dengan empat pertanyaan serta empat pilihan per pertanyaan. Kelas `dasar-pemrograman-web` memiliki tepat empat tugas demo published/required yang terhubung ke modul HTML, CSS, JavaScript, dan proyek akhir; tiga course demo lainnya tetap memiliki satu tugas proyek masing-masing.

### Rekonsiliasi database development aktual

| Course | Instructor | Konten wajib aktual | Status peserta `peserta@eduka.test` | Sertifikat development |
|---|---|---|---|---|
| Dasar Pemrograman Web | `pengajar@eduka.test` (active) | 5 lesson; 1 quiz/4 soal/4 opsi; 4 assignment | completed; 10/10; quiz 75 lulus; tugas 90/80/100/80 | `EDK-OPHPKT6675WP`, issued `2026-10-27 06:47:17 UTC` |
| Laravel untuk Pemula | `pengajar@eduka.test` (active) | 5 lesson; 1 quiz/4 soal/4 opsi; 1 assignment | completed; 7/7; quiz 100 lulus; tugas 90 | `EDK-W7XSMELFT3OG`, issued `2026-10-27 06:47:36 UTC` |
| UI/UX Design Dasar | `pengajar@eduka.test` (active) | 5 lesson; 1 quiz/4 soal/4 opsi; 1 assignment | completed; 7/7; quiz 100 lulus; tugas 90 | `EDK-TOGW5LDJX4JF`, issued `2026-10-27 06:47:58 UTC` |
| Database dan SQL | `pengajar@eduka.test` (active) | 5 lesson; 1 quiz/4 soal/4 opsi; 1 assignment | completed; 7/7; quiz 100 lulus; tugas 90 | `EDK-JE8V7AXZW8NK`, issued `2026-10-27 06:47:58 UTC` |

Keadaan awal audit adalah dua course pertama `90%/86%` tanpa attempt kuis dan dua course terakhir tanpa enrollment. Perbaikan development dilakukan melalui HTTP form dan route aplikasi: login peserta, enrollment dua course, penyelesaian 10 lesson, submit empat kuis dengan scoring server, submit dua tugas baru, login Pengajar, dan grading dua submission pada course owner. Attempt pertama Web menghasilkan nilai sah 75 (passing score 70) dan dipertahankan; tidak ada nilai atau progres yang diubah langsung di database. Setelah setiap event, `CourseCompletionService` menghitung ulang dari database. Course legacy `prinsip-ui-design` dan `manajemen-proyek-digital` tetap dipertahankan dan tidak diubah.

Verifikasi akhir pada database development `eduka_rebuild`: empat enrollment berstatus `completed`, seluruh course `100%`, empat certificate tersimpan tepat satu per course, dan setiap PDF owner-download mengembalikan HTTP 200, `application/pdf`, serta signature `%PDF-`. Seeder tidak otomatis mendaftarkan peserta ke empat course; enrollment tambahan dilakukan melalui UI/route nyata. `DemoCourseEndToEndTest` tetap menjadi bukti terisolasi di database testing, sedangkan tabel di atas adalah bukti record development yang persisten.

### Langkah UI untuk mengulang alur peserta

1. Masuk sebagai `peserta@eduka.test` melalui `/masuk`.
2. Buka `/kelas/ui-ux-design-dasar` dan `/kelas/database-dan-sql`, lalu tekan **Daftar kelas** pada masing-masing halaman.
3. Dari dashboard, buka setiap lesson dan tekan **Tandai selesai**. Jangan mengubah progres melalui browser console atau request buatan.
4. Buka kartu evaluasi kelas, pilih jawaban untuk seluruh pertanyaan, lalu tekan **Kirim jawaban**. Nilai dihitung server; quiz wajib harus mencapai minimal 70.
5. Buka setiap tugas, kirim catatan atau file yang diminta, dan tunggu status **Menunggu penilaian**.
6. Masuk sebagai `pengajar@eduka.test`, buka **Tinjau pengumpulan**, nilai setiap submission melalui form **Simpan**, dan berikan feedback bila diperlukan. Instructor hanya dapat menilai submission dari course miliknya.
7. Kembali sebagai peserta, buka dashboard dan **Sertifikat**. Setelah requirement terakhir lulus, status harus `100%` dan sertifikat dapat diunduh dari tombol **Unduh sertifikat**.

Pada acceptance development yang dilakukan, langkah tersebut dijalankan melalui HTTP form dari halaman aktual aplikasi. Ini bukan klaim pixel/browser automation penuh; hasil akhirnya diverifikasi dari response HTTP dan record MySQL development.

## Ketidaksesuaian dan perbaikan

- Logika progres tersebar dan lesson-only → dipusatkan pada `CourseCompletionService`.
- Sertifikat sebelumnya dapat dibuat lewat jalur manual dengan aturan berbeda → `CertificateService` menjadi adapter ke completion service.
- Redirect sertifikat memakai identifier yang tidak konsisten → seluruh owner link memakai certificate code.
- Certificate dapat diakses berdasarkan code tanpa ownership → halaman peserta menjadi owner-only; verifikasi publik dipisahkan dan minim data.
- Konten opsional belum memiliki aturan → migration flag dan filter eligibility ditambahkan.
- Pengajar belum memiliki CRUD nyata untuk kelas dan pertanyaan → route/controller/form update/delete ditambahkan dengan aturan pengarsipan.
- Quiz kosong dengan `pass_score = 0` berpotensi lulus → submission ditolak sebelum attempt dibuat.
- Draft parent lesson dapat dilewati melalui quiz/tugas → akses dan completion eligibility sama-sama memeriksa parent.
- Data demo awal hanya membuat satu tugas per course; dashboard hanya mengambil deadline non-null dan detail kelas tidak merender assignment. Seeder kini merekonsiliasi empat tugas stabil pada `dasar-pemrograman-web`, sedangkan dashboard/detail kelas mengambil seluruh assignment published yang dapat diakses dan menampilkan status submission peserta.

## Yang belum dapat diverifikasi

Tidak ada deployment ke server produksi. Browser smoke lokal tersedia dan sudah dijalankan dengan Chrome/CDP, tetapi belum mencakup seluruh CRUD melalui browser, upload/unduh file melalui browser, modal/error state setiap halaman, atau pixel comparison terhadap project Superdesign. Inspeksi visual yang dilakukan mencakup homepage desktop/mobile, katalog desktop, dashboard Admin/Pengajar desktop, dan dashboard Peserta mobile; hasilnya bukan klaim pixel parity. Konfigurasi SMTP, HTTPS, permission hosting, backup/restore, dan private storage di server nyata tetap `BELUM DIVERIFIKASI`.

## Bukti acceptance test terbaru

`tests/Feature/CertificateAcceptanceTest.php` memakai database MySQL testing terpisah `eduka_rebuild_testing` dan `RefreshDatabase`; tidak menyentuh database development. Skenario berakhir dengan enrollment `completed`, satu certificate dengan code unik dan `issued_at`, serta HTTP `403` ketika certificate privat dibuka peserta lain. Kondisi pending/failed/optional juga dicakup oleh `ParticipantWorkflowTest`, `CourseCompletionServiceTest`, dan `InstructorAdminWorkflowTest`.

`DemoCourseSeeder` dijalankan dua kali pada database development lokal tanpa reset; hasil akhir kelas `dasar-pemrograman-web` tetap memiliki 4 assignment dan judul stabil. Setelah itu acceptance peserta dilakukan melalui route aplikasi dan menghasilkan empat enrollment completed serta empat certificate development pada tabel di atas.

## PDF certificate

PDF dibuat on-demand oleh `App\Services\CertificatePdfService` menggunakan `barryvdh/laravel-dompdf` **3.1.2** dengan Dompdf **3.1.6**. Endpoint `GET /sertifikat/{code}/unduh` tetap memeriksa autentikasi dan ownership sebelum rendering, mengembalikan `application/pdf`, filename `sertifikat-eduka-{code}.pdf`, `nosniff`, dan private no-store cache headers. Template `resources/views/certificates/pdf.blade.php` memakai A4 landscape, DejaVu fonts, aset lokal/inline, tanpa remote fetch, QR, tanda tangan, atau klaim akreditasi yang tidak dikonfigurasi.

Probe PDF nyata pada database testing menghasilkan signature `%PDF-`, satu halaman A4 landscape `841.89 x 595.28 pt`, dan text extraction menemukan nama penerima, course, issuer, kode sertifikat, serta URL verifikasi. Screenshot render PDF juga diperiksa. Browser click-download untuk endpoint PDF belum diulang karena Chrome/CDP runtime tidak aktif; endpoint request/response sudah diverifikasi oleh feature test dan probe nyata.

Browser smoke lokal:

- Admin: login berhasil, landing `/admin`, dashboard dirender, screenshot desktop diperiksa.
- Pengajar: login berhasil, landing `/pengajar`, dashboard dirender, screenshot desktop diperiksa.
- Peserta: login berhasil, landing `/dashboard`, dashboard dirender, screenshot mobile 390px diperiksa; `scrollWidth` tidak melebihi `clientWidth`.
- Acceptance request/database: keempat kelas demo dapat didaftarkan dan diselesaikan; masing-masing menghasilkan tepat satu certificate yang dapat diunduh owner.
- Homepage: screenshot desktop dan viewport 390px diperiksa; katalog: screenshot desktop diperiksa.

## Assignment upload dan HTTP 413

Audit pada `POST /belajar/{course}/tugas/{assignment}` menemukan assignment demo 7 (`laravel-untuk-pemula`) memiliki batas aplikasi 5120 KB dan format `pdf, zip`. Sebelum perbaikan, PHP CLI aktif memakai `upload_max_filesize=2M` dan `post_max_size=8M`, sehingga file yang melewati batas PHP dapat ditolak sebelum controller memvalidasi.

Perbaikan sekarang memakai `config/eduka.php` dengan batas aplikasi 5120 KB, validasi MIME/content dan ekstensi, pesan error ramah, private `local` storage, serta render khusus `PostTooLargeException` untuk form tugas. Upload invalid PHP tidak diperlakukan sebagai submission tanpa file. Batas server yang direkomendasikan adalah `upload_max_filesize=8M` dan `post_max_size=10M`; nilai default CLI belum diubah karena `/etc/php/8.3/cli/php.ini` root-owned.

Regression test mencakup file valid, file lebih besar dari batas tugas, MIME/ekstensi invalid, request `CONTENT_LENGTH` di atas `post_max_size`, dan `UPLOAD_ERR_INI_SIZE`. Semua lulus dalam suite final 102 test. Upload melalui browser dengan PHP server default belum diklaim berhasil sampai administrator mengaktifkan nilai PHP 8M/10M dan melakukan upload nyata.

Audit log lokal menemukan error historis sebelum perbaikan/migration, termasuk relasi `quiz_options.quiz_question_id`, komponen/view yang belum tersedia, dan `is_required` sebelum migration. Entri terakhir yang terdeteksi bertanggal 27 Oktober 2026 pukul 05:10:07; log tidak dihapus dan tidak ada entri baru setelah migration serta smoke terakhir.
