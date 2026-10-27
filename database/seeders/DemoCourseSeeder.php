<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Category;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DemoCourseSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('DemoCourseSeeder hanya boleh dijalankan pada environment local atau testing.');
        }

        DB::transaction(function (): void {
        $instructor = User::firstOrCreate(
            ['email' => 'pengajar@eduka.test'],
            ['name' => 'Pengajar EDUKA', 'role' => User::ROLE_INSTRUCTOR, 'status' => 'active', 'password' => Hash::make('PengajarEDUKA2026!')],
        );
        if (! $instructor->isInstructor()) {
            throw new RuntimeException('Akun pengajar@eduka.test sudah ada tetapi bukan role pengajar.');
        }

        $categories = [
            'pemrograman' => ['name' => 'Pemrograman', 'description' => 'Bangun fondasi teknis untuk membuat produk digital.'],
            'desain' => ['name' => 'Desain', 'description' => 'Rancang pengalaman yang mudah dipahami dan digunakan.'],
            'database' => ['name' => 'Database', 'description' => 'Kelola data dengan struktur yang aman dan mudah dirawat.'],
        ];
        foreach ($categories as $slug => $attributes) {
            $categories[$slug]['model'] = Category::updateOrCreate(['slug' => $slug], $attributes);
        }

        foreach ($this->courses() as $definition) {
            $course = Course::where('slug', $definition['slug'])->first();
            if ($course && (int) $course->instructor_id !== (int) $instructor->id) {
                throw new RuntimeException('DemoCourseSeeder menolak mengambil alih course existing: '.$definition['slug']);
            }
            if ($course && $course->title !== $definition['title']) {
                continue;
            }
            if (! $course) {
                $course = new Course(['slug' => $definition['slug']]);
                $course->fill([
                    'category_id' => $categories[$definition['category']]['model']->id,
                    'instructor_id' => $instructor->id,
                    'title' => $definition['title'],
                    'short_description' => $definition['short_description'],
                    'description' => $definition['description'],
                    'level' => $definition['level'],
                    'visibility' => 'public',
                    'status' => 'published',
                    'duration_minutes' => 360,
                    'published_at' => now(),
                ]);
                $course->save();
            }

            $this->syncLessons($course, $definition['lessons']);
            $this->syncQuiz($course, $definition);
            $this->syncAssignments($course, $definition);
        }

        $participant = User::where('email', 'peserta@eduka.test')->first();
        $firstCourse = Course::where('slug', 'dasar-pemrograman-web')->first();
        if ($participant && $firstCourse) {
            Enrollment::firstOrCreate(
                ['course_id' => $firstCourse->id, 'user_id' => $participant->id],
                ['status' => 'active', 'enrolled_at' => now()],
            );
        }
        });
    }

    private function syncLessons(Course $course, array $lessons): void
    {
        foreach ($lessons as $position => $lesson) {
            $course->lessons()->updateOrCreate(
                ['slug' => $course->slug.'-modul-'.str_pad((string) ($position + 1), 2, '0', STR_PAD_LEFT)],
                [
                    'title' => $lesson['title'],
                    'summary' => $lesson['summary'],
                    'content' => $lesson['content'],
                    'type' => 'article',
                    'position' => $position + 1,
                    'estimated_minutes' => 35,
                    'is_published' => true,
                    'is_required' => true,
                ],
            );
        }
    }

    private function syncQuiz(Course $course, array $definition): void
    {
        $quiz = Quiz::updateOrCreate(
            ['course_id' => $course->id, 'slug' => $course->slug.'-quiz'],
            [
                'lesson_id' => $course->lessons()->orderByDesc('position')->value('id'),
                'title' => 'Evaluasi '.$course->title,
                'description' => 'Uji pemahamanmu setelah menyelesaikan seluruh modul.',
                'time_limit_minutes' => 30,
                'attempts_allowed' => 2,
                'pass_score' => 70,
                'is_published' => true,
                'is_required' => true,
            ],
        );
        foreach ($definition['questions'] as $position => $question) {
            $quizQuestion = $quiz->questions()->updateOrCreate(
                ['position' => $position + 1],
                ['question' => $question['question'], 'points' => 25, 'explanation' => $question['explanation']],
            );
            foreach ($question['options'] as $optionPosition => $option) {
                $quizQuestion->options()->updateOrCreate(
                    ['position' => $optionPosition + 1],
                    ['option_text' => $option, 'is_correct' => $optionPosition === $question['correct']],
                );
            }
        }
    }

    private function syncAssignments(Course $course, array $definition): void
    {
        $assignments = $definition['assignments'] ?? [[
            'title' => 'Proyek akhir '.$course->title,
            'description' => $definition['assignment'],
            'lesson_position' => count($definition['lessons']),
            'due_days' => 21,
        ]];

        foreach ($assignments as $position => $assignment) {
            $title = $assignment['title'];
            $attributes = [
                'lesson_id' => $course->lessons()->where('position', $assignment['lesson_position'])->value('id'),
                'description' => $assignment['description'],
                'due_at' => now()->addDays($assignment['due_days']),
                'max_file_kb' => 5120,
                'allowed_file_types' => ['pdf', 'zip'],
                'points' => 100,
                'pass_score' => 70,
                'is_published' => true,
                'is_required' => true,
            ];
            $existing = $course->assignments()->where('title', $title)->first();

            // Preserve the legacy first assignment record while moving it to the stable demo title.
            if (! $existing && $position === 0 && $course->slug === 'dasar-pemrograman-web') {
                $existing = $course->assignments()->where('title', 'Proyek akhir '.$course->title)->first();
                if ($existing) {
                    $existing->update(['title' => $title] + $attributes);
                    continue;
                }
            }

            Assignment::updateOrCreate(['course_id' => $course->id, 'title' => $title], $attributes);
        }
    }

    private function courses(): array
    {
        return [
            [
                'slug' => 'dasar-pemrograman-web', 'title' => 'Dasar Pemrograman Web', 'category' => 'pemrograman', 'level' => 'pemula',
                'short_description' => 'Bangun fondasi web modern dari HTML, CSS, dan JavaScript.',
                'description' => 'Kelas praktik untuk memahami cara kerja web dan membuat halaman responsif dari struktur hingga interaksi.',
                'lessons' => [
                    ['title' => 'Pengantar pemrograman web', 'summary' => 'Kenali browser, server, dan alur request-response.', 'content' => 'Web tersusun dari browser sebagai client, server sebagai penyedia resource, dan protokol HTTP sebagai bahasa komunikasi. Pahami alur ini sebelum menulis kode.'],
                    ['title' => 'Struktur HTML semantik', 'summary' => 'Susun dokumen dengan elemen yang bermakna.', 'content' => 'Gunakan heading berurutan, landmark seperti header/main/footer, serta elemen form yang memiliki label. Struktur semantik membantu aksesibilitas dan SEO.'],
                    ['title' => 'CSS dan layout responsif', 'summary' => 'Atur tampilan menggunakan box model, flexbox, dan grid.', 'content' => 'Mulai dari box model, lalu gunakan flexbox untuk satu dimensi dan grid untuk dua dimensi. Tambahkan breakpoint hanya ketika konten membutuhkannya.'],
                    ['title' => 'JavaScript dasar', 'summary' => 'Tambahkan perilaku interaktif yang terukur.', 'content' => 'Pelajari variabel, fungsi, event listener, dan manipulasi DOM. Pisahkan data, perilaku, dan presentasi agar perubahan tetap mudah dilacak.'],
                    ['title' => 'Praktik halaman web', 'summary' => 'Gabungkan struktur, gaya, dan interaksi menjadi satu halaman.', 'content' => 'Buat halaman profil produk sederhana dengan navigasi, kartu informasi, form kontak, dan validasi interaksi dasar. Uji pada layar sempit sebelum menyelesaikan pekerjaan.'],
                ],
                'questions' => $this->questions('web'),
                'assignment' => 'Buat halaman web responsif bertema pilihanmu. Sertakan struktur HTML semantik, layout CSS yang beradaptasi, dan satu interaksi JavaScript yang berguna.',
                'assignments' => [
                    ['title' => 'Tugas HTML: Profil Sederhana', 'description' => 'Buat halaman profil sederhana dengan HTML semantik, heading yang teratur, navigasi, dan formulir kontak yang memiliki label.', 'lesson_position' => 2, 'due_days' => 7],
                    ['title' => 'Tugas CSS: Halaman Responsif', 'description' => 'Buat halaman responsif dengan layout flexbox atau grid, tipografi terbaca, dan breakpoint yang nyaman pada layar mobile.', 'lesson_position' => 3, 'due_days' => 10],
                    ['title' => 'Tugas JavaScript: Interaksi Sederhana', 'description' => 'Tambahkan satu interaksi JavaScript yang berguna, seperti toggle detail, validasi form, atau filter daftar, lalu jelaskan cara kerjanya.', 'lesson_position' => 4, 'due_days' => 14],
                    ['title' => 'Tugas Akhir: Integrasi HTML CSS JavaScript', 'description' => 'Gabungkan HTML, CSS, dan JavaScript menjadi satu halaman web yang utuh, responsif, mudah digunakan, dan siap dipresentasikan.', 'lesson_position' => 5, 'due_days' => 21],
                ],
            ],
            [
                'slug' => 'laravel-untuk-pemula', 'title' => 'Laravel untuk Pemula', 'category' => 'pemrograman', 'level' => 'pemula',
                'short_description' => 'Bangun aplikasi Laravel dengan routing, Blade, model, dan CRUD.',
                'description' => 'Kelas bertahap untuk memahami fondasi Laravel dan menerapkannya pada aplikasi CRUD kecil yang tervalidasi.',
                'lessons' => [
                    ['title' => 'Instalasi dan struktur Laravel', 'summary' => 'Pahami folder aplikasi dan alur bootstrap.', 'content' => 'Kenali routes, controllers, models, migrations, resources, dan konfigurasi environment. Simpan rahasia di environment, bukan di source code.'],
                    ['title' => 'Routing dan controller', 'summary' => 'Hubungkan URL dengan aksi aplikasi yang jelas.', 'content' => 'Gunakan named route, route model binding, dan controller yang fokus. Validasi kepemilikan resource sebelum menjalankan perubahan.'],
                    ['title' => 'Blade template', 'summary' => 'Bangun tampilan reusable dengan layout dan component.', 'content' => 'Gunakan layout, section, props, escaping default, dan komponen Blade. Hindari menaruh query database di view.'],
                    ['title' => 'Migration, model, dan relasi', 'summary' => 'Rancang data yang dapat dipertanggungjawabkan.', 'content' => 'Migration mendefinisikan struktur, model menjaga aturan data, dan relasi menjelaskan koneksi antar entitas. Tambahkan foreign key dan unique constraint yang relevan.'],
                    ['title' => 'Validasi form dan CRUD', 'summary' => 'Simpan data dengan validasi dan otorisasi server.', 'content' => 'Validasi input di server, batasi mass assignment, gunakan CSRF, dan uji create-read-update-delete dengan database testing terpisah.'],
                ],
                'questions' => $this->questions('laravel'),
                'assignment' => 'Bangun aplikasi CRUD sederhana dengan migration, model, relasi, form request/validasi, Blade view, dan pengujian feature untuk jalur utama.',
            ],
            [
                'slug' => 'ui-ux-design-dasar', 'title' => 'UI/UX Design Dasar', 'category' => 'desain', 'level' => 'pemula',
                'short_description' => 'Rancang antarmuka yang jelas, konsisten, dan mudah digunakan.',
                'description' => 'Pelajari cara menerjemahkan kebutuhan pengguna menjadi user flow, wireframe, dan rancangan antarmuka yang dapat diuji.',
                'lessons' => [
                    ['title' => 'Prinsip desain antarmuka', 'summary' => 'Pahami hirarki, affordance, feedback, dan konsistensi.', 'content' => 'Antarmuka yang baik memberi orientasi, menunjukkan aksi yang tersedia, dan memberi feedback setelah interaksi. Prioritaskan kejelasan sebelum dekorasi.'],
                    ['title' => 'User flow dan wireframe', 'summary' => 'Petakan tujuan pengguna dari awal hingga selesai.', 'content' => 'Mulai dari tujuan, susun langkah minimum, lalu visualisasikan dengan wireframe. Tandai kondisi sukses, error, empty state, dan jalur kembali.'],
                    ['title' => 'Tipografi, warna, dan spacing', 'summary' => 'Gunakan token visual untuk membangun ritme.', 'content' => 'Pilih skala tipografi, kontras warna, dan jarak yang konsisten. Uji teks terhadap background terang dan gelap agar informasi tetap terbaca.'],
                    ['title' => 'Komponen dan konsistensi desain', 'summary' => 'Buat pola reusable untuk mempercepat iterasi.', 'content' => 'Dokumentasikan button, input, card, badge, dan navigasi beserta state default, hover, focus, disabled, dan error.'],
                    ['title' => 'Responsivitas dan aksesibilitas', 'summary' => 'Rancang pengalaman yang inklusif di berbagai perangkat.', 'content' => 'Pastikan target sentuh cukup, keyboard focus terlihat, form memiliki label, tabel tidak memaksa teks terlalu kecil, dan layout dapat mengalir pada mobile.'],
                ],
                'questions' => $this->questions('design'),
                'assignment' => 'Rancang satu halaman aplikasi dari user flow hingga high-fidelity mockup. Sertakan token warna, tipografi, spacing, state komponen, dan catatan aksesibilitas.',
            ],
            [
                'slug' => 'database-dan-sql', 'title' => 'Database dan SQL', 'category' => 'database', 'level' => 'pemula',
                'short_description' => 'Bangun fondasi database relasional dan query SQL yang aman.',
                'description' => 'Kelas praktik untuk merancang tabel, menjaga integritas relasi, dan menulis query SQL untuk kebutuhan aplikasi sehari-hari.',
                'lessons' => [
                    ['title' => 'Dasar database relasional', 'summary' => 'Kenali tabel, baris, kolom, dan relasi.', 'content' => 'Database relasional menyimpan entitas dalam tabel yang saling terhubung melalui key. Mulai dari kebutuhan data sebelum menentukan bentuk tabel.'],
                    ['title' => 'Primary key dan foreign key', 'summary' => 'Jaga identitas dan integritas antar tabel.', 'content' => 'Primary key mengidentifikasi baris secara unik. Foreign key menghubungkan tabel dan sebaiknya memiliki aturan penghapusan yang sesuai dengan kebutuhan bisnis.'],
                    ['title' => 'SELECT, INSERT, UPDATE, DELETE', 'summary' => 'Gunakan operasi CRUD SQL dengan aman.', 'content' => 'Gunakan WHERE yang spesifik saat update/delete, parameter binding untuk input, dan transaksi ketika beberapa perubahan harus berhasil bersama.'],
                    ['title' => 'JOIN dan relasi', 'summary' => 'Gabungkan data yang tersebar dengan tepat.', 'content' => 'INNER JOIN mengambil pasangan yang cocok, LEFT JOIN mempertahankan baris dari tabel kiri. Pahami cardinality agar hasil query tidak menggandakan data.'],
                    ['title' => 'Normalisasi dasar', 'summary' => 'Kurangi duplikasi dan anomali data.', 'content' => 'Normalisasi memisahkan fakta agar satu informasi disimpan di tempat yang tepat. Pertahankan desain praktis dengan indeks pada kolom yang sering dicari.'],
                ],
                'questions' => $this->questions('database'),
                'assignment' => 'Rancang skema database untuk aplikasi pengelolaan kelas. Sertakan tabel, key, relasi, contoh query CRUD, JOIN, dan alasan normalisasi yang dipilih.',
            ],
        ];
    }

    private function questions(string $topic): array
    {
        return [
            ['question' => 'Apa prinsip utama yang perlu diterapkan pada '.$topic.'?', 'options' => ['Gunakan struktur dan aturan yang jelas', 'Abaikan validasi', 'Simpan semua hal di satu tempat', 'Percayakan keamanan pada browser'], 'correct' => 0, 'explanation' => 'Struktur dan aturan yang jelas membantu aplikasi tetap dapat dipahami, diuji, dan dirawat.'],
            ['question' => 'Mengapa hasil kerja perlu diuji?', 'options' => ['Untuk menemukan regresi sebelum digunakan', 'Agar kode terlihat lebih panjang', 'Untuk menghapus semua data', 'Agar browser menentukan nilai'], 'correct' => 0, 'explanation' => 'Pengujian memberikan bukti bahwa perilaku penting tetap benar setelah perubahan.'],
            ['question' => 'Apa yang sebaiknya menjadi sumber kebenaran status?', 'options' => ['Data server yang tervalidasi', 'Nilai tersembunyi di form', 'Teks pada tombol', 'Cache browser saja'], 'correct' => 0, 'explanation' => 'Status penting harus ditentukan dari data server dan aturan domain, bukan manipulasi client.'],
            ['question' => 'Apa langkah yang baik saat menemukan kebutuhan baru?', 'options' => ['Pahami konteks lalu buat perubahan terukur', 'Langsung hapus data lama', 'Lewati validasi', 'Tambahkan data fiktif'], 'correct' => 0, 'explanation' => 'Perubahan terukur dengan konteks dan pengujian mengurangi risiko regresi.'],
        ];
    }
}
