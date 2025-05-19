<?php
namespace App\Controllers;

use App\Models\User;
use App\Models\Donation;
use App\Models\Campaign;

/**
 * Controller untuk fitur user biasa
 */
class UserController extends Controller {
    private $userModel;
    private $donationModel;
    private $campaignModel;
    
    public function __construct() {
        parent::__construct();
        
        // Cek apakah user sudah login
        if (!isset($_SESSION['user'])) {
            $this->redirect('auth/login');
            exit;
        }
        
        $this->userModel = new User();
        $this->donationModel = new Donation();
        $this->campaignModel = new Campaign();
    }
    
    /**
     * Halaman dashboard user
     * 
     * @return void
     */
    public function dashboard() {
        // Donasi user
        $userId = $_SESSION['user']['id'];
        $myDonations = $this->donationModel->getByUserId($userId, 5);
        
        // Total donasi
        $totalDonated = $this->db->fetchColumn(
            "SELECT COALESCE(SUM(amount), 0) FROM donations WHERE user_id = ? AND status = 'success'",
            [$userId]
        );
        
        // Kampanye yang dibuat oleh user
        $myCampaigns = $this->db->fetchAll(
            "SELECT * FROM campaigns WHERE user_id = ? ORDER BY created_at DESC LIMIT 5",
            [$userId]
        );
        
        $this->view('user/dashboard', [
            'title' => 'Dashboard - ' . APP_NAME,
            'myDonations' => $myDonations,
            'totalDonated' => $totalDonated,
            'myCampaigns' => $myCampaigns
        ]);
    }
    
    /**
     * Halaman profil user
     * 
     * @return void
     */
    public function profile() {
        $userId = $_SESSION['user']['id'];
        $user = $this->userModel->find($userId);
        
        $this->view('user/profile', [
            'title' => 'Profil Saya - ' . APP_NAME,
            'user' => $user
        ]);
    }
    
    /**
     * Memproses update profil
     * 
     * @return void
     */
    public function updateProfile() {
        $userId = $_SESSION['user']['id'];
        
        // Validasi input
        $errors = $this->validate($_POST, [
            'name' => 'required|max:100',
            'phone' => 'max:20',
            'address' => 'max:255'
        ]);
        
        if (!empty($errors)) {
            $this->setFlash('errors', $errors);
            $this->redirect('user/profile');
            return;
        }
        
        // Upload profile picture jika ada
        $profilePicture = null;
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxSize = 2 * 1024 * 1024; // 2MB
            
            if (!in_array($_FILES['profile_picture']['type'], $allowedTypes)) {
                $this->setFlash('error', 'Format file tidak didukung. Gunakan JPEG, PNG, atau GIF');
                $this->redirect('user/profile');
                return;
            }
            
            if ($_FILES['profile_picture']['size'] > $maxSize) {
                $this->setFlash('error', 'Ukuran file terlalu besar. Maksimal 2MB');
                $this->redirect('user/profile');
                return;
            }
            
            $fileName = uniqid('profile_') . '_' . time() . '.' . pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $targetPath = __DIR__ . '/../../public/uploads/' . $fileName;
            
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetPath)) {
                $profilePicture = $fileName;
            }
        }
        
        // Update data user
        $userData = [
            'name' => $_POST['name'],
            'phone' => $_POST['phone'] ?? null,
            'address' => $_POST['address'] ?? null,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($profilePicture) {
            $userData['profile_picture'] = $profilePicture;
        }
        
        $this->userModel->update($userId, $userData);
        
        // Update session data
        $_SESSION['user']['name'] = $_POST['name'];
        if ($profilePicture) {
            $_SESSION['user']['profile_picture'] = $profilePicture;
        }
        
        $this->setFlash('success', 'Profil berhasil diperbarui');
        $this->redirect('user/profile');
    }
    
    /**
     * Halaman ubah password
     * 
     * @return void
     */
    public function changePassword() {
        $this->view('user/change_password', [
            'title' => 'Ubah Password - ' . APP_NAME
        ]);
    }
    
    /**
     * Memproses ubah password
     * 
     * @return void
     */
    public function updatePassword() {
        $userId = $_SESSION['user']['id'];
        
        // Validasi input
        $errors = $this->validate($_POST, [
            'current_password' => 'required',
            'new_password' => 'required|min:8',
            'confirm_password' => 'required'
        ]);
        
        // Cek konfirmasi password
        if ($_POST['new_password'] !== $_POST['confirm_password']) {
            $errors['confirm_password'][] = 'Konfirmasi password tidak sesuai';
        }
        
        // Cek password lama
        $user = $this->userModel->find($userId);
        if (!password_verify($_POST['current_password'], $user['password'])) {
            $errors['current_password'][] = 'Password saat ini tidak sesuai';
        }
        
        if (!empty($errors)) {
            $this->setFlash('errors', $errors);
            $this->redirect('user/changePassword');
            return;
        }
        
        // Update password
        $this->userModel->updatePassword($userId, $_POST['new_password']);
        
        $this->setFlash('success', 'Password berhasil diubah');
        $this->redirect('user/profile');
    }
    
    /**
     * Halaman daftar donasi user
     * 
     * @return void
     */
    public function donations() {
        $userId = $_SESSION['user']['id'];
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 10;
        
        $donations = $this->donationModel->getPaginatedByUserId($userId, $page, $perPage);
        
        $this->view('user/donations', [
            'title' => 'Donasi Saya - ' . APP_NAME,
            'donations' => $donations['data'],
            'currentPage' => $page,
            'totalPages' => $donations['last_page']
        ]);
    }
    
    /**
     * Halaman detail donasi
     * 
     * @param int $id ID donasi
     * @return void
     */
    public function donationDetail($id) {
        $userId = $_SESSION['user']['id'];
        $donation = $this->donationModel->getDetail($id);
        
        // Cek apakah donasi milik user yang login
        if (!$donation || $donation['user_id'] !== $userId) {
            $this->redirect('user/donations');
            return;
        }
        
        $this->view('user/donation_detail', [
            'title' => 'Detail Donasi - ' . APP_NAME,
            'donation' => $donation
        ]);
    }
    
    /**
     * Halaman daftar kampanye user
     * 
     * @return void
     */
    public function campaigns() {
        $userId = $_SESSION['user']['id'];
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 10;
        
        $campaigns = $this->campaignModel->getPaginatedByUserId($userId, $page, $perPage);
        
        $this->view('user/campaigns', [
            'title' => 'Kampanye Saya - ' . APP_NAME,
            'campaigns' => $campaigns['data'],
            'currentPage' => $page,
            'totalPages' => $campaigns['last_page']
        ]);
    }
} 