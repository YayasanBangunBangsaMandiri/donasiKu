<?php
namespace App\Controllers;

use App\Models\User;

/**
 * Controller untuk autentikasi
 */
class AuthController extends Controller {
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
    }
    
    /**
     * Menampilkan halaman login
     * 
     * @return void
     */
    public function login() {
        // Jika user sudah login, redirect ke dashboard
        if (isset($_SESSION['user'])) {
            $this->redirect($_SESSION['user']['role'] === 'admin' ? 'admin/dashboard' : 'user/dashboard');
            return;
        }
        
        $this->view('auth/login', [
            'title' => 'Login - ' . APP_NAME
        ]);
    }
    
    /**
     * Memproses form login
     * 
     * @return void
     */
    public function doLogin() {
        // Validasi input
        $errors = $this->validate($_POST, [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        
        if (!empty($errors)) {
            $this->setFlash('errors', $errors);
            $this->setFlash('old', ['email' => $_POST['email']]);
            $this->redirect('login');
            return;
        }
        
        // Cek user di database
        $user = $this->userModel->authenticate($_POST['email'], $_POST['password']);
        
        // Jika user tidak ditemukan atau password salah
        if (!$user) {
            $this->setFlash('error', 'Email atau password salah');
            $this->setFlash('old', ['email' => $_POST['email']]);
            $this->redirect('login');
            return;
        }
        
        // Jika user tidak aktif
        if ($user['status'] !== 'active') {
            $this->setFlash('error', 'Akun Anda tidak aktif. Silakan hubungi admin.');
            $this->redirect('login');
            return;
        }
        
        // Jika email belum diverifikasi
        if (!$user['email_verified_at'] && $user['role'] !== 'admin') {
            $this->setFlash('error', 'Email Anda belum diverifikasi. Silakan cek email Anda untuk verifikasi.');
            $this->redirect('login');
            return;
        }
        
        // Jika 2FA diaktifkan dan user bukan admin
        if (ENABLE_2FA && isset($user['two_factor_enabled']) && $user['two_factor_enabled'] && $user['role'] !== 'admin') {
            // Simpan user_id di session untuk verifikasi 2FA
            $_SESSION['temp_user_id'] = $user['id'];
            $this->redirect('verify-2fa');
            return;
        }
        
        // Simpan data user di session (kecuali password)
        unset($user['password']);
        $_SESSION['user'] = $user;
        
        // Update last login
        $this->userModel->update($user['id'], [
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        // Redirect ke dashboard sesuai role
        $this->redirect($user['role'] === 'admin' ? 'admin/dashboard' : 'user/dashboard');
    }
    
    /**
     * Menampilkan halaman register
     * 
     * @return void
     */
    public function register() {
        // Jika user sudah login, redirect ke dashboard
        if (isset($_SESSION['user'])) {
            $this->redirect($_SESSION['user']['role'] === 'admin' ? 'admin/dashboard' : 'user/dashboard');
            return;
        }
        
        $this->view('auth/register', [
            'title' => 'Register - ' . APP_NAME
        ]);
    }
    
    /**
     * Memproses form register
     * 
     * @return void
     */
    public function doRegister() {
        // Validasi input
        $errors = $this->validate($_POST, [
            'name' => 'required|max:100',
            'email' => 'required|email',
            'password' => 'required|min:8',
            'password_confirmation' => 'required'
        ]);
        
        // Validasi password confirmation
        if ($_POST['password'] !== $_POST['password_confirmation']) {
            $errors['password_confirmation'][] = 'Konfirmasi password tidak sesuai';
        }
        
        // Cek apakah email sudah terdaftar
        $existingUser = $this->userModel->findBy('email', $_POST['email']);
        
        if ($existingUser) {
            $errors['email'][] = 'Email sudah terdaftar';
        }
        
        if (!empty($errors)) {
            $this->setFlash('errors', $errors);
            $this->setFlash('old', [
                'name' => $_POST['name'],
                'email' => $_POST['email']
            ]);
            $this->redirect('register');
            return;
        }
        
        // Generate verification token
        $verificationToken = bin2hex(random_bytes(32));
        
        // Buat user baru
        $userData = [
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'password' => $_POST['password'],
            'verification_token' => $verificationToken,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $userId = $this->userModel->register($userData);
        
        // Kirim email verifikasi (implementasi sebenarnya akan menggunakan library email)
        // TODO: Implementasi pengiriman email
        
        // Set flash message sukses
        $this->setFlash('success', 'Registrasi berhasil! Silakan cek email Anda untuk verifikasi.');
        
        // Redirect ke halaman login
        $this->redirect('login');
    }
    
    /**
     * Verifikasi email
     * 
     * @param string $token Token verifikasi
     * @return void
     */
    public function verifyEmail($token) {
        // Cek token di database
        $user = $this->userModel->findBy('verification_token', $token);
        
        if (!$user) {
            $this->setFlash('error', 'Token verifikasi tidak valid');
            $this->redirect('login');
            return;
        }
        
        // Update status email verified
        $this->userModel->update($user['id'], [
            'email_verified_at' => date('Y-m-d H:i:s'),
            'verification_token' => null,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        // Set flash message sukses
        $this->setFlash('success', 'Email berhasil diverifikasi! Silakan login.');
        
        // Redirect ke halaman login
        $this->redirect('login');
    }
    
    /**
     * Menampilkan halaman lupa password
     * 
     * @return void
     */
    public function forgotPassword() {
        $this->view('auth/forgot-password', [
            'title' => 'Lupa Password - ' . APP_NAME
        ]);
    }
    
    /**
     * Memproses form lupa password
     * 
     * @return void
     */
    public function doForgotPassword() {
        // Validasi input
        $errors = $this->validate($_POST, [
            'email' => 'required|email'
        ]);
        
        if (!empty($errors)) {
            $this->setFlash('errors', $errors);
            $this->setFlash('old', ['email' => $_POST['email']]);
            $this->redirect('forgot-password');
            return;
        }
        
        // Cek user di database
        $user = $this->userModel->findBy('email', $_POST['email']);
        
        if (!$user || $user['status'] !== 'active') {
            // Untuk keamanan, tetap tampilkan pesan sukses meskipun email tidak ditemukan
            $this->setFlash('success', 'Jika email terdaftar, Anda akan menerima instruksi reset password.');
            $this->redirect('forgot-password');
            return;
        }
        
        // Generate reset token
        $resetToken = bin2hex(random_bytes(32));
        
        // Simpan token ke database
        $this->userModel->update($user['id'], [
            'remember_token' => $resetToken,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        // Kirim email reset password (implementasi sebenarnya akan menggunakan library email)
        // TODO: Implementasi pengiriman email
        
        // Set flash message sukses
        $this->setFlash('success', 'Instruksi reset password telah dikirim ke email Anda.');
        
        // Redirect kembali ke halaman lupa password
        $this->redirect('forgot-password');
    }
    
    /**
     * Menampilkan halaman reset password
     * 
     * @param string $token Token reset password
     * @return void
     */
    public function resetPassword($token) {
        // Cek token di database
        $user = $this->userModel->findBy('remember_token', $token);
        
        if (!$user) {
            $this->setFlash('error', 'Token reset password tidak valid atau sudah kadaluarsa.');
            $this->redirect('login');
            return;
        }
        
        $this->view('auth/reset-password', [
            'title' => 'Reset Password - ' . APP_NAME,
            'token' => $token
        ]);
    }
    
    /**
     * Memproses form reset password
     * 
     * @return void
     */
    public function doResetPassword() {
        // Validasi input
        $errors = $this->validate($_POST, [
            'token' => 'required',
            'password' => 'required|min:8',
            'password_confirmation' => 'required'
        ]);
        
        // Validasi password confirmation
        if ($_POST['password'] !== $_POST['password_confirmation']) {
            $errors['password_confirmation'][] = 'Konfirmasi password tidak sesuai';
        }
        
        if (!empty($errors)) {
            $this->setFlash('errors', $errors);
            $this->redirect('reset-password/' . $_POST['token']);
            return;
        }
        
        // Cek token di database
        $user = $this->userModel->findBy('remember_token', $_POST['token']);
        
        if (!$user) {
            $this->setFlash('error', 'Token reset password tidak valid atau sudah kadaluarsa.');
            $this->redirect('login');
            return;
        }
        
        // Update password dan hapus token
        $this->userModel->update($user['id'], [
            'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'remember_token' => null,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        // Set flash message sukses
        $this->setFlash('success', 'Password berhasil diubah! Silakan login dengan password baru Anda.');
        
        // Redirect ke halaman login
        $this->redirect('login');
    }
    
    /**
     * Logout
     * 
     * @return void
     */
    public function logout() {
        // Hapus session
        unset($_SESSION['user']);
        session_destroy();
        
        // Redirect ke halaman utama
        $this->redirect('');
    }
} 