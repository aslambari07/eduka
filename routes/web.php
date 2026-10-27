<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstructorWorkspaceController;
use App\Http\Controllers\LearningController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/kelas', [CourseController::class, 'index'])->name('courses.index');
Route::get('/kelas/{course:slug}/materi/{lesson:slug}', [CourseController::class, 'lesson'])->name('courses.lesson');
Route::get('/kelas/{course:slug}', [CourseController::class, 'show'])->name('courses.show');
Route::get('/verifikasi/sertifikat/{certificate}', [CertificateController::class, 'verify'])->name('certificates.verify');

Route::middleware('guest')->group(function () {
    Route::get('/masuk', [LoginController::class, 'create'])->name('login');
    Route::post('/masuk', [LoginController::class, 'store'])->middleware('throttle:6,1')->name('login.store');
    Route::get('/daftar', [RegisterController::class, 'create'])->name('register');
    Route::post('/daftar', [RegisterController::class, 'store'])->name('register.store');
    Route::get('/lupa-kata-sandi', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/lupa-kata-sandi', [ForgotPasswordController::class, 'store'])->middleware('throttle:6,1')->name('password.email');
    Route::get('/atur-ulang-kata-sandi/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/atur-ulang-kata-sandi', [ResetPasswordController::class, 'store'])->name('password.update');
});

Route::middleware(['auth', 'role:peserta'])->group(function () {
    Route::post('/kelas/{course:slug}/daftar', [EnrollmentController::class, 'store'])->name('enrollments.store');
    Route::prefix('belajar')->name('learning.')->group(function () {
        Route::get('/{course:slug}/{lesson:slug}', [LearningController::class, 'player'])->name('player');
        Route::post('/{course:slug}/{lesson:slug}/selesai', [LearningController::class, 'complete'])->name('lesson.complete');
        Route::get('/{course:slug}/kuis/{quiz:slug}', [LearningController::class, 'quiz'])->name('quiz');
        Route::post('/{course:slug}/kuis/{quiz:slug}', [LearningController::class, 'submitQuiz'])->name('quiz.submit');
        Route::get('/{course:slug}/tugas/{assignment}', [LearningController::class, 'assignment'])->name('assignment');
        Route::post('/{course:slug}/tugas/{assignment}', [LearningController::class, 'submitAssignment'])->name('assignment.submit');
        Route::get('/{course:slug}/tugas/{assignment}/file', [LearningController::class, 'downloadAssignmentFile'])->name('assignment.file');
        Route::post('/{course:slug}/catatan', [LearningController::class, 'storeNote'])->name('note.store');
        Route::get('/{course:slug}/catatan/{note}', [LearningController::class, 'note'])->name('note');
        Route::put('/{course:slug}/catatan/{note}', [LearningController::class, 'updateNote'])->name('note.update');
        Route::delete('/{course:slug}/catatan/{note}', [LearningController::class, 'deleteNote'])->name('note.delete');
        Route::post('/{course:slug}/sertifikat', [CertificateController::class, 'issue'])->name('certificate.issue');
    });
    Route::get('/sertifikat', [CertificateController::class, 'index'])->name('certificates.index');
    Route::get('/sertifikat/{certificate}', [CertificateController::class, 'show'])->name('certificates.show');
    Route::get('/sertifikat/{certificate}/unduh', [CertificateController::class, 'download'])->name('certificates.download');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profil/kata-sandi', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/keluar', LogoutController::class)->name('logout');
});

Route::middleware(['auth', 'role:pengajar'])->prefix('pengajar')->name('instructor.')->group(function () {
    Route::get('/', [InstructorWorkspaceController::class, 'dashboard'])->name('dashboard');
    Route::get('/kelas', [InstructorWorkspaceController::class, 'courses'])->name('courses.index');
    Route::post('/kelas', [InstructorWorkspaceController::class, 'storeCourse'])->name('courses.store');
    Route::put('/kelas/{course}', [InstructorWorkspaceController::class, 'updateCourse'])->name('courses.update');
    Route::delete('/kelas/{course}', [InstructorWorkspaceController::class, 'deleteCourse'])->name('courses.destroy');
    Route::get('/kuis', [InstructorWorkspaceController::class, 'quizzes'])->name('quizzes.index');
    Route::get('/kelas/{course}/materi', [InstructorWorkspaceController::class, 'content'])->name('content.index');
    Route::post('/kelas/{course}/materi', [InstructorWorkspaceController::class, 'storeLesson'])->name('lessons.store');
    Route::put('/kelas/{course}/materi/{lesson}', [InstructorWorkspaceController::class, 'updateLesson'])->name('lessons.update');
    Route::delete('/kelas/{course}/materi/{lesson}', [InstructorWorkspaceController::class, 'deleteLesson'])->name('lessons.destroy');
    Route::post('/kelas/{course}/kuis', [InstructorWorkspaceController::class, 'storeQuiz'])->name('quizzes.store');
    Route::put('/kelas/{course}/kuis/{quiz}', [InstructorWorkspaceController::class, 'updateQuiz'])->name('quizzes.update');
    Route::delete('/kelas/{course}/kuis/{quiz}', [InstructorWorkspaceController::class, 'deleteQuiz'])->name('quizzes.destroy');
    Route::post('/kuis/{quiz}/pertanyaan', [InstructorWorkspaceController::class, 'storeQuestion'])->name('questions.store');
    Route::put('/kuis/{quiz}/pertanyaan/{question}', [InstructorWorkspaceController::class, 'updateQuestion'])->name('questions.update');
    Route::delete('/kuis/{quiz}/pertanyaan/{question}', [InstructorWorkspaceController::class, 'deleteQuestion'])->name('questions.destroy');
    Route::post('/kelas/{course}/tugas', [InstructorWorkspaceController::class, 'storeAssignment'])->name('assignments.store');
    Route::put('/kelas/{course}/tugas/{assignment}', [InstructorWorkspaceController::class, 'updateAssignment'])->name('assignments.update');
    Route::delete('/kelas/{course}/tugas/{assignment}', [InstructorWorkspaceController::class, 'deleteAssignment'])->name('assignments.destroy');
    Route::get('/pengumpulan', [InstructorWorkspaceController::class, 'submissions'])->name('submissions.index');
    Route::post('/pengumpulan/{submission}/nilai', [InstructorWorkspaceController::class, 'grade'])->name('submissions.grade');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/pengguna', [AdminController::class, 'users'])->name('users');
    Route::post('/pengguna/{user}/role', [AdminController::class, 'updateRole'])->name('users.role');
    Route::post('/pengguna/{user}/status', [AdminController::class, 'updateStatus'])->name('users.status');
    Route::get('/kategori', [AdminController::class, 'categories'])->name('categories');
    Route::post('/kategori', [AdminController::class, 'storeCategory'])->name('categories.store');
    Route::put('/kategori/{category}', [AdminController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/kategori/{category}', [AdminController::class, 'deleteCategory'])->name('categories.destroy');
    Route::get('/kelas', [AdminController::class, 'courses'])->name('courses');
    Route::post('/kelas/{course}/status', [AdminController::class, 'updateCourse'])->name('courses.status');
    Route::get('/laporan', [AdminController::class, 'reports'])->name('reports');
});
