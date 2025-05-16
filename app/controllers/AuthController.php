<?php
namespace App\Controllers;

use App\Models\User;

/**
 * Controller untuk mengelola autentikasi
 */
class AuthController extends Controller {
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
    }
    
    /**
     * Menampilkan form login
     * 
     * @return void
     */
    public function login() {
        // Jika sudah login, redirect ke halaman dashboard
        if (isset($_SESSION['user_id'])) {
            $this->redirect($_SESSION['role'] === 'admin' ? 'admin/dashboard' : 'user/dashboard');
            return;
        }
        
        $this->view('auth/login', [
            'title' => 'Login'
        ]);
    }
    
    /**
     * Memproses form login
     * 
     * @return void
     */
    public function doLogin() {
        // Validasi input
        $rules = [
            'email' => 'required|email',
            'password' => 'required'
        ];
        
        $errors = $this->validate($_POST, $rules);
        
        if (!empty($errors)) {
            $this->setFlash('errors', $errors);
            $this->setFlash('old', $_POST);
            $this->redirect('auth/login');
            return;
        }
        
        // Cek kredensial
        $user = $this->userModel->authenticate($_POST['email'], $_POST['password']);
        
        if (!$user) {
            $this->setFlash('error', 'Email atau password salah');
            $this->setFlash('old', $_POST);
            $this->redirect('auth/login');
            return;
        }
        
        // Cek apakah 2FA diaktifkan
        if ($user['two_factor_enabled']) {
            // Simpan data user sementara untuk verifikasi 2FA
            $_SESSION['temp_user'] = $user;
            $this->redirect('auth/verify-2fa');
            return;
        }
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        
        // Set remember token jika "ingat saya" dicentang
        if (isset($_POST['remember']) && $_POST['remember'] === '1') {
            $token = bin2hex(random_bytes(32));
            $this->userModel->update($user['id'], [
                'remember_token' => $token
            ]);
            
            // Set cookie untuk 30 hari
            setcookie('remember_token', $token, time() + (86400 * 30), '/');
        }
        
        // Redirect ke halaman dashboard sesuai role
        $this->redirect($user['role'] === 'admin' ? 'admin/dashboard' : 'user/dashboard');
    }
    
    /**
     * Menampilkan form verifikasi 2FA
     * 
     * @return void
     */
    public function verify2FA() {
        // Cek apakah ada data user sementara
        if (!isset($_SESSION['temp_user'])) {
            $this->redirect('auth/login');
            return;
        }
        
        $this->view('auth/verify-2fa', [
            'title' => 'Verifikasi Autentikasi Dua Faktor'
        ]);
    }
    
    /**
     * Memproses verifikasi 2FA
     * 
     * @return void
     */
    public function doVerify2FA() {
        // Cek apakah ada data user sementara
        if (!isset($_SESSION['temp_user'])) {
            $this->redirect('auth/login');
            return;
        }
        
        // Validasi input
        $rules = [
            'code' => 'required|min:6|max:6'
        ];
        
        $errors = $this->validate($_POST, $rules);
        
        if (!empty($errors)) {
            $this->setFlash('errors', $errors);
            $this->redirect('auth/verify-2fa');
            return;
        }
        
        $user = $_SESSION['temp_user'];
        
        // Verifikasi kode 2FA
        require_once __DIR__ . '/../../vendor/autoload.php';
        $ga = new \PHPGangsta_GoogleAuthenticator();
        $checkResult = $ga->verifyCode($user['two_factor_secret'], $_POST['code'], 2);
        
        if (!$checkResult) {
            $this->setFlash('error', 'Kode verifikasi tidak valid');
            $this->redirect('auth/verify-2fa');
            return;
        }
        
        // Hapus data user sementara
        unset($_SESSION['temp_user']);
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        
        // Redirect ke halaman dashboard sesuai role
        $this->redirect($user['role'] === 'admin' ? 'admin/dashboard' : 'user/dashboard');
    }
    
    /**
     * Menampilkan form registrasi
     * 
     * @return void
     */
    public function register() {
        // Jika sudah login, redirect ke halaman dashboard
        if (isset($_SESSION['user_id'])) {
            $this->redirect($_SESSION['role'] === 'admin' ? 'admin/dashboard' : 'user/dashboard');
            return;
        }
        
        $this->view('auth/register', [
            'title' => 'Registrasi'
        ]);
    }
    
    /**
     * Memproses form registrasi
     * 
     * @return void
     */
    public function doRegister() {
        // Validasi input
        $rules = [
            'name' => 'required|max:100',
            'email' => 'required|email',
            'password' => 'required|min:8',
            'password_confirmation' => 'required'
        ];
        
        $errors = $this->validate($_POST, $rules);
        
        // Validasi tambahan
        if ($_POST['password'] !== $_POST['password_confirmation']) {
            $errors['password_confirmation'][] = 'Konfirmasi password tidak sesuai';
        }
        
        if (!empty($errors)) {
            $this->setFlash('errors', $errors);
            $this->setFlash('old', $_POST);
            $this->redirect('auth/register');
            return;
        }
        
        // Cek apakah email sudah digunakan
        $existingUser = $this->userModel->findBy('email', $_POST['email']);
        
        if ($existingUser) {
            $this->setFlash('error', 'Email sudah digunakan');
            $this->setFlash('old', $_POST);
            $this->redirect('auth/register');
            return;
        }
        
        // Siapkan data user
        $data = [
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'password' => $_POST['password'],
            'role' => 'user'
        ];
        
        // Buat user baru
        $userId = $this->userModel->register($data);
        
        if (!$userId) {
            $this->setFlash('error', 'Gagal melakukan registrasi');
            $this->setFlash('old', $_POST);
            $this->redirect('auth/register');
            return;
        }
        
        $this->setFlash('success', 'Registrasi berhasil. Silakan login.');
        $this->redirect('auth/login');
    }
    
    /**
     * Menampilkan form lupa password
     * 
     * @return void
     */
    public function forgotPassword() {
        $this->view('auth/forgot-password', [
            'title' => 'Lupa Password'
        ]);
    }
    
    /**
     * Memproses form lupa password
     * 
     * @return void
     */
    public function doForgotPassword() {
        // Validasi input
        $rules = [
            'email' => 'required|email'
        ];
        
        $errors = $this->validate($_POST, $rules);
        
        if (!empty($errors)) {
            $this->setFlash('errors', $errors);
            $this->setFlash('old', $_POST);
            $this->redirect('auth/forgot-password');
            return;
        }
        
        // Cek apakah email terdaftar
        $user = $this->userModel->findBy('email', $_POST['email']);
        
        if (!$user) {
            $this->setFlash('error', 'Email tidak terdaftar');
            $this->setFlash('old', $_POST);
            $this->redirect('auth/forgot-password');
            return;
        }
        
        // Buat token reset password
        $token = $this->userModel->createPasswordResetToken($_POST['email']);
        
        if (!$token) {
            $this->setFlash('error', 'Gagal membuat token reset password');
            $this->redirect('auth/forgot-password');
            return;
        }
        
        // Kirim email reset password (implementasi sebenarnya akan menggunakan library email)
        $resetUrl = BASE_URL . '/auth/reset-password?email=' . urlencode($_POST['email']) . '&token=' . $token;
        
        // TODO: Implementasi pengiriman email
        
        $this->setFlash('success', 'Link reset password telah dikirim ke email Anda');
        $this->redirect('auth/forgot-password');
    }
    
    /**
     * Menampilkan form reset password
     * 
     * @return void
     */
    public function resetPassword() {
        // Validasi token dan email
        $email = $_GET['email'] ?? '';
        $token = $_GET['token'] ?? '';
        
        if (empty($email) || empty($token)) {
            $this->redirect('auth/login');
            return;
        }
        
        $isValid = $this->userModel->validatePasswordResetToken($token, $email);
        
        if (!$isValid) {
            $this->setFlash('error', 'Token reset password tidak valid atau sudah kadaluarsa');
            $this->redirect('auth/login');
            return;
        }
        
        $this->view('auth/reset-password', [
            'title' => 'Reset Password',
            'email' => $email,
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
        $rules = [
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:8',
            'password_confirmation' => 'required'
        ];
        
        $errors = $this->validate($_POST, $rules);
        
        // Validasi tambahan
        if ($_POST['password'] !== $_POST['password_confirmation']) {
            $errors['password_confirmation'][] = 'Konfirmasi password tidak sesuai';
        }
        
        if (!empty($errors)) {
            $this->setFlash('errors', $errors);
            $this->redirect('auth/reset-password?email=' . urlencode($_POST['email']) . '&token=' . $_POST['token']);
            return;
        }
        
        // Reset password
        $result = $this->userModel->resetPassword($_POST['token'], $_POST['email'], $_POST['password']);
        
        if (!$result) {
            $this->setFlash('error', 'Gagal reset password');
            $this->redirect('auth/reset-password?email=' . urlencode($_POST['email']) . '&token=' . $_POST['token']);
            return;
        }
        
        $this->setFlash('success', 'Password berhasil direset. Silakan login dengan password baru.');
        $this->redirect('auth/login');
    }
    
    /**
     * Logout
     * 
     * @return void
     */
    public function logout() {
        // Hapus cookie remember token jika ada
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        // Hapus session
        session_unset();
        session_destroy();
        
        $this->redirect('home');
    }
} 