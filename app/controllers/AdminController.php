<?php
namespace App\Controllers;

use App\Models\Campaign;
use App\Models\Donation;
use App\Models\User;

/**
 * Controller untuk fitur admin
 */
class AdminController extends Controller {
    private $campaignModel;
    private $donationModel;
    private $userModel;

    public function __construct() {
        parent::__construct();
        
        // Cek apakah user sudah login dan memiliki akses admin
        if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] !== 'admin' && $_SESSION['user']['role'] !== 'super_admin')) {
            $this->redirect('auth/login');
            exit;
        }
        
        $this->campaignModel = new Campaign();
        $this->donationModel = new Donation();
        $this->userModel = new User();
    }
    
    /**
     * Halaman dashboard admin
     * 
     * @return void
     */
    public function dashboard() {
        // Statistik donasi
        $donationStats = $this->donationModel->getStats();
        
        // Donasi terbaru (10 terakhir)
        $recentDonations = $this->donationModel->getAll(
            ['status' => 'success'], 
            1, 
            10, 
            'created_at', 
            'DESC'
        );
        
        // Kampanye aktif
        $activeCampaigns = $this->db->fetchAll(
            "SELECT * FROM campaigns WHERE status = 'active' ORDER BY created_at DESC LIMIT 5"
        );
        
        $this->view('admin/dashboard', [
            'title' => 'Dashboard Admin - ' . APP_NAME,
            'donationStats' => $donationStats,
            'recentDonations' => $recentDonations['data'],
            'activeCampaigns' => $activeCampaigns
        ]);
    }
    
    /**
     * Halaman daftar kampanye
     * 
     * @return void
     */
    public function campaigns() {
        // Parameter filter dan pagination
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 10;
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        
        // Query untuk mendapatkan daftar kampanye dengan filter
        $query = "SELECT c.*, u.name as organizer_name, cat.name as category_name 
                 FROM campaigns c
                 JOIN users u ON c.user_id = u.id
                 JOIN categories cat ON c.category_id = cat.id";
        
        $params = [];
        
        if (!empty($status)) {
            $query .= " WHERE c.status = ?";
            $params[] = $status;
        }
        
        $query .= " ORDER BY c.created_at DESC";
        
        // Total data untuk pagination
        $countQuery = "SELECT COUNT(*) FROM campaigns";
        if (!empty($status)) {
            $countQuery .= " WHERE status = ?";
        }
        $totalData = $this->db->fetchColumn($countQuery, $params);
        $totalPages = ceil($totalData / $perPage);
        
        // Offset untuk pagination
        $offset = ($page - 1) * $perPage;
        $query .= " LIMIT {$perPage} OFFSET {$offset}";
        
        // Mendapatkan data kampanye
        $campaigns = $this->db->fetchAll($query, $params);
        
        $this->view('admin/campaigns', [
            'title' => 'Kelola Kampanye - ' . APP_NAME,
            'campaigns' => $campaigns,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'status' => $status
        ]);
    }
    
    /**
     * Halaman tambah kampanye baru
     * 
     * @return void
     */
    public function addCampaign() {
        // Mendapatkan daftar kategori
        $categories = $this->db->fetchAll("SELECT * FROM categories ORDER BY name ASC");
        
        $this->view('admin/campaign_form', [
            'title' => 'Tambah Kampanye - ' . APP_NAME,
            'categories' => $categories,
            'campaign' => null,
            'isEdit' => false
        ]);
    }
    
    /**
     * Memproses form tambah kampanye
     * 
     * @return void
     */
    public function createCampaign() {
        // Validasi input
        $errors = $this->validate($_POST, [
            'title' => 'required|max:255',
            'category_id' => 'required|numeric',
            'short_description' => 'required|max:255',
            'description' => 'required',
            'goal_amount' => 'required|numeric|min_value:100000',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'allow_custom_amount' => 'boolean',
            'min_amount' => 'numeric',
            'max_amount' => 'numeric'
        ]);
        
        // Validasi file upload (featured image)
        if (!isset($_FILES['featured_image']) || $_FILES['featured_image']['error'] !== UPLOAD_ERR_OK) {
            $errors['featured_image'][] = 'Gambar utama kampanye wajib diupload';
        } else {
            $allowed = ['image/jpeg', 'image/png', 'image/gif'];
            $fileType = $_FILES['featured_image']['type'];
            $fileSize = $_FILES['featured_image']['size'];
            
            if (!in_array($fileType, $allowed)) {
                $errors['featured_image'][] = 'Format file tidak didukung. Gunakan JPEG, PNG, atau GIF';
            }
            
            if ($fileSize > MAX_UPLOAD_SIZE) {
                $errors['featured_image'][] = 'Ukuran file terlalu besar. Maksimal ' . (MAX_UPLOAD_SIZE / (1024 * 1024)) . 'MB';
            }
        }
        
        if (!empty($errors)) {
            $this->setFlash('errors', $errors);
            $this->setFlash('old', $_POST);
            $this->redirect('admin/add-campaign');
            return;
        }
        
        // Proses upload file
        $featuredImage = $this->uploadFile('featured_image');
        
        // Proses upload banner (opsional)
        $bannerImage = null;
        if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
            $bannerImage = $this->uploadFile('banner_image');
        }
        
        // Generate slug dari title
        $slug = $this->createSlug($_POST['title']);
        
        // Preset donation amounts (opsional)
        $donationAmounts = null;
        if (isset($_POST['preset_amounts']) && !empty($_POST['preset_amounts'])) {
            $amounts = explode(',', $_POST['preset_amounts']);
            $formattedAmounts = [];
            foreach ($amounts as $amount) {
                $amount = (int) trim($amount);
                if ($amount > 0) {
                    $formattedAmounts[$amount] = number_format($amount, 0, ',', '.');
                }
            }
            if (!empty($formattedAmounts)) {
                $donationAmounts = json_encode($formattedAmounts);
            }
        }
        
        // Siapkan data kampanye
        $campaignData = [
            'user_id' => $_SESSION['user']['id'],
            'category_id' => $_POST['category_id'],
            'title' => $_POST['title'],
            'slug' => $slug,
            'short_description' => $_POST['short_description'],
            'description' => $_POST['description'],
            'goal_amount' => $_POST['goal_amount'],
            'featured_image' => $featuredImage,
            'banner_image' => $bannerImage,
            'start_date' => $_POST['start_date'],
            'end_date' => $_POST['end_date'],
            'status' => $_POST['status'] ?? 'pending',
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'allow_custom_amount' => isset($_POST['allow_custom_amount']) ? 1 : 0,
            'donation_amounts' => $donationAmounts,
            'donation_info' => $_POST['donation_info'] ?? null,
            'meta_tags' => $_POST['meta_tags'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Simpan kampanye ke database
        $campaignId = $this->campaignModel->create($campaignData);
        
        if (!$campaignId) {
            $this->setFlash('error', 'Gagal membuat kampanye. Silakan coba lagi.');
            $this->redirect('admin/add-campaign');
            return;
        }
        
        $this->setFlash('success', 'Kampanye berhasil dibuat.');
        $this->redirect('admin/campaigns');
    }
    
    /**
     * Halaman edit kampanye
     * 
     * @param int $id ID kampanye
     * @return void
     */
    public function editCampaign($id) {
        // Mendapatkan data kampanye
        $campaign = $this->campaignModel->find($id);
        
        if (!$campaign) {
            $this->setFlash('error', 'Kampanye tidak ditemukan.');
            $this->redirect('admin/campaigns');
            return;
        }
        
        // Mendapatkan daftar kategori
        $categories = $this->db->fetchAll("SELECT * FROM categories ORDER BY name ASC");
        
        $this->view('admin/campaign_form', [
            'title' => 'Edit Kampanye - ' . APP_NAME,
            'categories' => $categories,
            'campaign' => $campaign,
            'isEdit' => true
        ]);
    }
    
    /**
     * Halaman daftar donasi
     * 
     * @return void
     */
    public function donations() {
        // Parameter filter dan pagination
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 20;
        $filters = [
            'campaign_id' => $_GET['campaign_id'] ?? null,
            'status' => $_GET['status'] ?? null,
            'payment_method' => $_GET['payment_method'] ?? null,
            'search' => $_GET['search'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null
        ];
        
        // Mendapatkan data donasi dengan filter
        $donations = $this->donationModel->getAll($filters, $page, $perPage);
        
        // Mendapatkan daftar kampanye untuk filter
        $campaigns = $this->db->fetchAll("SELECT id, title FROM campaigns ORDER BY title ASC");
        
        $this->view('admin/donations', [
            'title' => 'Kelola Donasi - ' . APP_NAME,
            'donations' => $donations['data'],
            'pagination' => [
                'current_page' => $donations['current_page'],
                'total' => $donations['total'],
                'per_page' => $donations['per_page'],
                'last_page' => $donations['last_page']
            ],
            'filters' => $filters,
            'campaigns' => $campaigns
        ]);
    }
    
    /**
     * Halaman detail donasi
     * 
     * @param int $id ID donasi
     * @return void
     */
    public function donationDetail($id) {
        // Mendapatkan data donasi
        $donation = $this->donationModel->findWithCampaign($id);
        
        if (!$donation) {
            $this->setFlash('error', 'Donasi tidak ditemukan.');
            $this->redirect('admin/donations');
            return;
        }
        
        $this->view('admin/donation_detail', [
            'title' => 'Detail Donasi - ' . APP_NAME,
            'donation' => $donation
        ]);
    }
    
    /**
     * Halaman pengaturan
     * 
     * @return void
     */
    public function settings() {
        $this->view('admin/settings', [
            'title' => 'Pengaturan - ' . APP_NAME
        ]);
    }
    
    /**
     * Upload file
     * 
     * @param string $fileInputName Nama input file di form
     * @return string Nama file yang diupload
     */
    private function uploadFile($fileInputName) {
        $uploadDir = UPLOAD_PATH;
        
        // Pastikan direktori upload ada
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExtension = pathinfo($_FILES[$fileInputName]['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '_' . time() . '.' . $fileExtension;
        $targetFile = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $targetFile)) {
            return $fileName;
        }
        
        return null;
    }
    
    /**
     * Membuat slug dari string
     * 
     * @param string $text Text yang akan dijadikan slug
     * @return string
     */
    private function createSlug($text) {
        // Ganti karakter non-alfanumerik dengan dash
        $text = preg_replace('/[^\p{L}\p{N}]+/u', '-', $text);
        // Tambahkan lowercase dan trim dashes
        $text = strtolower(trim($text, '-'));
        // Tambahkan waktu untuk memastikan keunikan
        return $text . '-' . time();
    }
}
