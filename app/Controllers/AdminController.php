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
        
        // Get active campaign count
        $activeCampaignsCount = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM campaigns WHERE status = 'active'"
        );
        
        // Get total campaign count
        $totalCampaignsCount = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM campaigns"
        );
        
        // Kampanye aktif (data untuk daftar)
        $activeCampaignsList = $this->db->fetchAll(
            "SELECT * FROM campaigns WHERE status = 'active' ORDER BY created_at DESC LIMIT 5"
        );
        
        $this->view('admin/dashboard', [
            'title' => 'Dashboard Admin - ' . APP_NAME,
            'donationStats' => $donationStats,
            'recentDonations' => $recentDonations['data'],
            'activeCampaigns' => [
                'list' => $activeCampaignsList,
                'count' => (int)$activeCampaignsCount,
                'total' => (int)$totalCampaignsCount
            ]
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
        
        // Calculate progress percentage for each campaign
        foreach ($campaigns as &$campaign) {
            // Make sure current_amount is set
            if (!isset($campaign['current_amount'])) {
                $campaign['current_amount'] = 0;
            }
            
            // Ensure numeric value
            $campaign['current_amount'] = (float)$campaign['current_amount'];
            $campaign['goal_amount'] = (float)$campaign['goal_amount'];
            
            // Auto-update status to completed if goal is reached
            if ($campaign['current_amount'] >= $campaign['goal_amount'] && $campaign['status'] === 'active') {
                $this->db->query(
                    "UPDATE campaigns SET status = 'completed' WHERE id = ?",
                    [$campaign['id']]
                );
                $campaign['status'] = 'completed';
            }
        }
        
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
     * Alias untuk addCampaign untuk mendukung URL dengan hyphen
     * 
     * @return void
     */
    public function add_campaign() {
        $this->addCampaign();
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
     * Alias untuk createCampaign untuk mendukung URL dengan hyphen
     * 
     * @return void
     */
    public function create_campaign() {
        $this->createCampaign();
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
            'title' => 'Pengaturan Aplikasi'
        ]);
    }
    
    /**
     * Upload file
     * 
     * @param string $fileInputName Nama input file di form
     * @return string Nama file yang diupload
     */
    private function uploadFile($fileInputName) {
        $uploadDir = __DIR__ . '/../../public/uploads/';
        
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

    /**
     * Memproses konfirmasi pembayaran donasi
     * 
     * @return void
     */
    public function confirmPayment() {
        // Validasi input
        if (!isset($_POST['donation_id']) || empty($_POST['donation_id'])) {
            $this->setFlash('error', 'ID donasi tidak valid.');
            $this->redirect('admin/donations');
            return;
        }
        
        $donationId = (int)$_POST['donation_id'];
        $donation = $this->donationModel->find($donationId);
        
        if (!$donation) {
            $this->setFlash('error', 'Donasi tidak ditemukan.');
            $this->redirect('admin/donations');
            return;
        }
        
        // Update status donasi
        $updateData = [
            'status' => 'success',
            'paid_at' => date('Y-m-d H:i:s'),
            'payment_status_updated_at' => date('Y-m-d H:i:s')
        ];
        
        $updated = $this->donationModel->update($donationId, $updateData);
        
        if (!$updated) {
            $this->setFlash('error', 'Gagal mengonfirmasi pembayaran.');
            $this->redirect('admin/donation/' . $donationId);
            return;
        }
        
        // Update jumlah dana terkumpul di kampanye
        $campaign = $this->campaignModel->find($donation['campaign_id']);
        if ($campaign) {
            $this->campaignModel->updateAmount($donation['campaign_id'], $donation['amount']);
        }
        
        // Tambahkan notifikasi
        $this->db->query(
            "INSERT INTO notifications (user_id, donation_id, campaign_id, message, type, created_at) VALUES (?, ?, ?, ?, ?, NOW())",
            [
                $donation['user_id'] ?? null, 
                $donationId, 
                $donation['campaign_id'], 
                'Donasi Anda sebesar Rp ' . number_format($donation['amount'], 0, ',', '.') . ' telah dikonfirmasi.', 
                'success'
            ]
        );
        
        // Kirim notifikasi email
        $donationData = $this->donationModel->findWithCampaign($donationId);
        if ($donationData) {
            $notificationHelper = new \App\Helpers\NotificationHelper();
            $notificationHelper->sendEmailNotification($donationData, $donationData['campaign']);
        }
        
        $this->setFlash('success', 'Pembayaran donasi berhasil dikonfirmasi.');
        $this->redirect('admin/donation/' . $donationId);
    }

    /**
     * Mengirim notifikasi secara manual
     * 
     * @return void
     */
    public function sendNotification() {
        // Validasi input
        if (!isset($_POST['donation_id'], $_POST['message'], $_POST['notification_type'])) {
            $this->setFlash('error', 'Data tidak lengkap.');
            $this->redirect('admin/donations');
            return;
        }
        
        $donationId = (int)$_POST['donation_id'];
        $donation = $this->donationModel->findWithCampaign($donationId);
        
        if (!$donation) {
            $this->setFlash('error', 'Donasi tidak ditemukan.');
            $this->redirect('admin/donations');
            return;
        }
        
        $notificationType = $_POST['notification_type'];
        $subject = $_POST['subject'] ?? 'Notifikasi Donasi #' . $donationId;
        $message = $_POST['message'];
        
        $notificationHelper = new \App\Helpers\NotificationHelper();
        $success = false;
        
        // Kirim email
        if ($notificationType === 'email' || $notificationType === 'both') {
            $success = $notificationHelper->sendCustomEmailNotification(
                $donation['donor_email'],
                $donation['donor_name'],
                $subject,
                $message
            );
        }
        
        // Kirim WhatsApp
        if (($notificationType === 'whatsapp' || $notificationType === 'both') && !empty($donation['phone'])) {
            $success = $notificationHelper->sendCustomWhatsAppNotification(
                $donation['phone'],
                $message
            ) || $success;
        }
        
        // Simpan notifikasi ke database
        $this->db->query(
            "INSERT INTO notifications (user_id, donation_id, campaign_id, message, type, created_at) VALUES (?, ?, ?, ?, ?, NOW())",
            [
                $donation['user_id'] ?? null, 
                $donationId, 
                $donation['campaign_id'], 
                $message, 
                'info'
            ]
        );
        
        if ($success) {
            $this->setFlash('success', 'Notifikasi berhasil dikirim.');
        } else {
            $this->setFlash('warning', 'Notifikasi disimpan, tetapi gagal dikirimkan. Silakan periksa pengaturan email/WhatsApp.');
        }
        
        $this->redirect('admin/donation/' . $donationId);
    }

    /**
     * Menghapus donasi
     * 
     * @return void
     */
    public function deleteDonation() {
        // Get donation ID from POST
        $donationId = isset($_POST['donation_id']) ? (int)$_POST['donation_id'] : null;
        
        if (!$donationId) {
            $this->setFlash('error', 'ID donasi tidak valid.');
            $this->redirect('admin/donations');
            return;
        }
        
        // Check if donation exists
        $donation = $this->donationModel->find($donationId);
        if (!$donation) {
            $this->setFlash('error', 'Donasi tidak ditemukan.');
            $this->redirect('admin/donations');
            return;
        }
        
        // Delete related notifications
        $this->db->query("DELETE FROM notifications WHERE donation_id = ?", [$donationId]);
        
        // Delete donation
        $deleted = $this->donationModel->delete($donationId);
        
        if (!$deleted) {
            $this->setFlash('error', 'Gagal menghapus donasi.');
            $this->redirect('admin/donations');
            return;
        }
        
        // Update campaign funds if donation was successful
        if ($donation['status'] === 'success') {
            $this->campaignModel->updateAmount($donation['campaign_id'], -1 * $donation['amount']);
        }
        
        $this->setFlash('success', 'Donasi berhasil dihapus.');
        $this->redirect('admin/donations');
    }

    /**
     * Mengupdate status kampanye
     * 
     * @return void
     */
    public function updateCampaignStatus() {
        // Validasi input
        if (!isset($_POST['campaign_id'], $_POST['status'])) {
            $this->setFlash('error', 'Data tidak lengkap.');
            $this->redirect('admin/campaigns');
            return;
        }
        
        $campaignId = (int)$_POST['campaign_id'];
        $status = $_POST['status'];
        $note = $_POST['note'] ?? '';
        $sendNotification = isset($_POST['send_notification']) && $_POST['send_notification'] == 1;
        
        $campaign = $this->campaignModel->find($campaignId);
        
        if (!$campaign) {
            $this->setFlash('error', 'Kampanye tidak ditemukan.');
            $this->redirect('admin/campaigns');
            return;
        }
        
        // Validasi status
        $validStatuses = ['active', 'pending', 'ended', 'rejected'];
        if (!in_array($status, $validStatuses)) {
            $this->setFlash('error', 'Status tidak valid.');
            $this->redirect('admin/campaigns');
            return;
        }
        
        // Update status kampanye
        $updated = $this->campaignModel->update($campaignId, [
            'status' => $status,
            'admin_note' => $note,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        if (!$updated) {
            $this->setFlash('error', 'Gagal mengupdate status kampanye.');
            $this->redirect('admin/campaigns');
            return;
        }
        
        // Kirim notifikasi jika diminta
        if ($sendNotification) {
            $campaignWithUser = $this->db->fetchRow(
                "SELECT c.*, u.email, u.name FROM campaigns c JOIN users u ON c.user_id = u.id WHERE c.id = ?",
                [$campaignId]
            );
            
            if ($campaignWithUser) {
                $notificationHelper = new \App\Helpers\NotificationHelper();
                
                $subject = 'Status Kampanye "' . $campaignWithUser['title'] . '" Diperbarui';
                $message = "Halo {$campaignWithUser['name']},\n\n";
                $message .= "Status kampanye \"{$campaignWithUser['title']}\" telah diubah menjadi: " . ucfirst($status) . ".\n\n";
                
                if (!empty($note)) {
                    $message .= "Catatan dari admin: " . $note . "\n\n";
                }
                
                $message .= "Terima kasih,\nTim " . APP_NAME;
                
                $notificationHelper->sendCustomEmailNotification(
                    $campaignWithUser['email'],
                    $campaignWithUser['name'],
                    $subject,
                    $message
                );
                
                // Tambahkan notifikasi
                $this->db->query(
                    "INSERT INTO notifications (user_id, campaign_id, message, type, created_at) VALUES (?, ?, ?, ?, NOW())",
                    [
                        $campaignWithUser['user_id'], 
                        $campaignId, 
                        "Status kampanye \"{$campaignWithUser['title']}\" telah diubah menjadi: " . ucfirst($status) . ".", 
                        $status === 'active' ? 'success' : ($status === 'rejected' ? 'danger' : 'info')
                    ]
                );
            }
        }
        
        $this->setFlash('success', 'Status kampanye berhasil diperbarui.');
        $this->redirect('admin/campaigns');
    }

    /**
     * Menghapus kampanye
     * 
     * @return void
     */
    public function delete_campaign($id = null) {
        // Check admin permissions
        $this->checkAdminAuthentication();
        
        // Get ID from POST if not provided as parameter
        if ($id === null) {
            if (isset($_POST['campaign_id'])) {
                $id = (int)$_POST['campaign_id'];
            } else {
                $_SESSION['error'] = 'ID kampanye tidak valid';
                header('Location: ' . BASE_URL . '/admin/campaigns');
                exit;
            }
        }
        
        // Validate ID
        if (!$id || $id <= 0) {
            $_SESSION['error'] = 'ID kampanye tidak valid';
            header('Location: ' . BASE_URL . '/admin/campaigns');
            exit;
        }
        
        // Get campaign model
        $campaignModel = new \App\Models\Campaign();
        
        // Try to delete campaign
        $result = $campaignModel->delete($id);
        
        if ($result) {
            $_SESSION['success'] = 'Kampanye berhasil dihapus';
        } else {
            $_SESSION['error'] = 'Gagal menghapus kampanye';
        }
        
        // Redirect back to campaigns page
        header('Location: ' . BASE_URL . '/admin/campaigns');
        exit;
    }

    /**
     * Menyimpan pengaturan aplikasi
     * 
     * @return void
     */
    public function saveSettings() {
        // Validasi input
        if (!isset($_POST['settings_type'])) {
            $this->setFlash('error', 'Tipe pengaturan tidak valid.');
            $this->redirect('admin/settings');
            return;
        }
        
        $settingsType = $_POST['settings_type'];
        $configFile = CONFIG_PATH . '/config.php';
        $configContent = file_get_contents($configFile);
        
        switch ($settingsType) {
            case 'general':
                // Proses pengaturan umum
                $this->updateConfigValue($configContent, 'APP_NAME', $_POST['app_name'] ?? APP_NAME);
                $this->updateConfigValue($configContent, 'APP_DESCRIPTION', $_POST['app_description'] ?? '');
                $this->updateConfigValue($configContent, 'CONTACT_EMAIL', $_POST['contact_email'] ?? '');
                $this->updateConfigValue($configContent, 'CONTACT_PHONE', $_POST['contact_phone'] ?? '');
                $this->updateConfigValue($configContent, 'MAINTENANCE_MODE', isset($_POST['maintenance_mode']) ? 'true' : 'false');
                
                // Proses upload logo
                if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                    $logoPath = PUBLIC_PATH . '/assets/img/logo.png';
                    move_uploaded_file($_FILES['logo']['tmp_name'], $logoPath);
                }
                
                // Proses upload favicon
                if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] === UPLOAD_ERR_OK) {
                    $faviconPath = PUBLIC_PATH . '/favicon.ico';
                    move_uploaded_file($_FILES['favicon']['tmp_name'], $faviconPath);
                }
                break;
                
            case 'payment':
                // Proses pengaturan pembayaran
                $this->updateConfigValue($configContent, 'DOKU_CLIENT_ID', $_POST['doku_client_id'] ?? '');
                $this->updateConfigValue($configContent, 'DOKU_SECRET_KEY', $_POST['doku_secret_key'] ?? '');
                $this->updateConfigValue($configContent, 'DOKU_ENVIRONMENT', $_POST['doku_environment'] ?? 'sandbox');
                
                // Metode pembayaran yang diaktifkan
                $enabledMethods = $_POST['payment_methods'] ?? [];
                $this->updateConfigValue($configContent, 'ENABLED_PAYMENT_METHODS', var_export($enabledMethods, true));
                break;
                
            case 'email':
                // Proses pengaturan email
                $this->updateConfigValue($configContent, 'MAIL_HOST', $_POST['mail_host'] ?? '');
                $this->updateConfigValue($configContent, 'MAIL_PORT', $_POST['mail_port'] ?? '');
                $this->updateConfigValue($configContent, 'MAIL_USERNAME', $_POST['mail_username'] ?? '');
                $this->updateConfigValue($configContent, 'MAIL_PASSWORD', $_POST['mail_password'] ?? '');
                $this->updateConfigValue($configContent, 'MAIL_ENCRYPTION', $_POST['mail_encryption'] ?? 'tls');
                $this->updateConfigValue($configContent, 'MAIL_FROM_ADDRESS', $_POST['mail_from_address'] ?? '');
                $this->updateConfigValue($configContent, 'MAIL_FROM_NAME', $_POST['mail_from_name'] ?? '');
                break;
                
            case 'security':
                // Proses pengaturan keamanan
                $this->updateConfigValue($configContent, 'ENABLE_RECAPTCHA', isset($_POST['enable_recaptcha']) ? 'true' : 'false');
                $this->updateConfigValue($configContent, 'RECAPTCHA_SITE_KEY', $_POST['recaptcha_site_key'] ?? '');
                $this->updateConfigValue($configContent, 'RECAPTCHA_SECRET_KEY', $_POST['recaptcha_secret_key'] ?? '');
                $this->updateConfigValue($configContent, 'ENABLE_2FA', isset($_POST['enable_2fa']) ? 'true' : 'false');
                break;
                
            default:
                $this->setFlash('error', 'Tipe pengaturan tidak dikenal.');
                $this->redirect('admin/settings');
                return;
        }
        
        // Tulis kembali file konfigurasi
        file_put_contents($configFile, $configContent);
        
        $this->setFlash('success', 'Pengaturan berhasil disimpan.');
        $this->redirect('admin/settings#' . $settingsType);
    }

    /**
     * Kirim email test
     * 
     * @return void
     */
    public function testEmail() {
        // Validasi input
        if (!isset($_POST['test_email_to']) || !filter_var($_POST['test_email_to'], FILTER_VALIDATE_EMAIL)) {
            $this->setFlash('error', 'Alamat email tidak valid.');
            $this->redirect('admin/settings#email');
            return;
        }
        
        $to = $_POST['test_email_to'];
        $notificationHelper = new \App\Helpers\NotificationHelper();
        
        $subject = 'Email Test dari ' . APP_NAME;
        $message = "Halo,\n\nIni adalah email test dari aplikasi " . APP_NAME . ".\n\n";
        $message .= "Jika Anda menerima email ini, berarti konfigurasi SMTP Anda sudah benar.\n\n";
        $message .= "Pengaturan SMTP yang digunakan:\n";
        $message .= "- Host: " . MAIL_HOST . "\n";
        $message .= "- Port: " . MAIL_PORT . "\n";
        $message .= "- Username: " . MAIL_USERNAME . "\n";
        $message .= "- Encryption: " . MAIL_ENCRYPTION . "\n";
        $message .= "- From Address: " . MAIL_FROM_ADDRESS . "\n";
        $message .= "- From Name: " . MAIL_FROM_NAME . "\n\n";
        $message .= "Terima kasih,\nTim " . APP_NAME;
        
        $success = $notificationHelper->sendCustomEmailNotification(
            $to,
            explode('@', $to)[0],
            $subject,
            $message
        );
        
        if ($success) {
            $this->setFlash('success', 'Email test berhasil dikirim ke ' . $to);
        } else {
            $this->setFlash('error', 'Gagal mengirim email test. Silakan periksa konfigurasi SMTP Anda.');
        }
        
        $this->redirect('admin/settings#email');
    }

    /**
     * Export data donasi ke berbagai format (Excel, PDF, CSV)
     * 
     * @return void
     */
    public function exportDonations() {
        // Parameter filter
        $filters = [
            'campaign_id' => $_GET['campaign_id'] ?? null,
            'status' => $_GET['status'] ?? null,
            'payment_method' => $_GET['payment_method'] ?? null,
            'search' => $_GET['search'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null
        ];
        
        // Format ekspor: excel, pdf, csv
        $format = $_GET['format'] ?? 'excel';
        
        // Mendapatkan semua data donasi dengan filter
        $donations = $this->donationModel->getAllForExport($filters);
        
        switch ($format) {
            case 'pdf':
                // Generate PDF file dan redirect ke file yang dihasilkan
                $pdfUrl = $this->donationModel->exportToPdf($filters);
                if ($pdfUrl) {
                    $this->redirect($pdfUrl);
                } else {
                    $this->setFlash('error', 'Gagal membuat PDF. Pastikan TCPDF library tersedia.');
                    $this->redirect('admin/donations');
                }
                break;
                
            case 'csv':
                // Set header untuk download CSV
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment;filename="donasi_' . date('YmdHis') . '.csv"');
                header('Cache-Control: max-age=0');
                
                // Buat file handle untuk output CSV
                $output = fopen('php://output', 'w');
                
                // Set UTF-8 BOM untuk support karakter non-ASCII
                fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
                
                // Header kolom CSV
                fputcsv($output, [
                    'ID', 'Tanggal', 'Nama Donatur', 'Email', 'Telepon', 'Kampanye', 
                    'Jumlah', 'Metode Pembayaran', 'Status', 'Tanggal Bayar', 'Pesan'
                ]);
                
                // Data rows
                foreach ($donations as $donation) {
                    $donorName = $donation['is_anonymous'] ? 'Anonim' : $donation['name'];
                    
                    fputcsv($output, [
                        $donation['id'],
                        date('Y-m-d H:i:s', strtotime($donation['created_at'])),
                        $donorName,
                        $donation['email'],
                        $donation['phone'] ?? '-',
                        $donation['campaign_title'] ?? '-',
                        $donation['amount'],
                        $donation['payment_method'],
                        $donation['status'],
                        !empty($donation['paid_at']) ? date('Y-m-d H:i:s', strtotime($donation['paid_at'])) : '-',
                        $donation['message'] ?? '-'
                    ]);
                }
                
                fclose($output);
                exit;
                
            default: // excel
                // Set header untuk download Excel
                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="donasi_' . date('YmdHis') . '.xls"');
                header('Cache-Control: max-age=0');
                
                // Judul laporan
                echo '<h2>' . APP_NAME . ' - Laporan Donasi</h2>';
                echo '<p>Tanggal Export: ' . date('d/m/Y H:i') . '</p>';
                
                // Filter info
                echo '<p><strong>Filter:</strong> ';
                if (!empty($filters['campaign_id'])) {
                    $campaign = $this->campaignModel->find($filters['campaign_id']);
                    echo 'Kampanye: ' . ($campaign ? $campaign['title'] : 'ID ' . $filters['campaign_id']) . ', ';
                }
                if (!empty($filters['status'])) {
                    echo 'Status: ' . ucfirst($filters['status']) . ', ';
                }
                if (!empty($filters['payment_method'])) {
                    echo 'Metode: ' . ucfirst($filters['payment_method']) . ', ';
                }
                if (!empty($filters['date_from'])) {
                    echo 'Dari: ' . date('d/m/Y', strtotime($filters['date_from'])) . ', ';
                }
                if (!empty($filters['date_to'])) {
                    echo 'Sampai: ' . date('d/m/Y', strtotime($filters['date_to'])) . ', ';
                }
                echo '</p>';
                
                // Output Excel content
                echo '<table border="1" cellpadding="5" cellspacing="0">';
                echo '<tr bgcolor="#CCCCCC">';
                echo '<th>ID</th>';
                echo '<th>Tanggal</th>';
                echo '<th>Nama Donatur</th>';
                echo '<th>Email</th>';
                echo '<th>Telepon</th>';
                echo '<th>Kampanye</th>';
                echo '<th>Jumlah</th>';
                echo '<th>Metode Pembayaran</th>';
                echo '<th>Status</th>';
                echo '<th>Tanggal Bayar</th>';
                echo '<th>Pesan</th>';
                echo '</tr>';
                
                $totalAmount = 0;
                $totalSuccess = 0;
                
                foreach ($donations as $donation) {
                    $donorName = $donation['is_anonymous'] ? 'Anonim' : $donation['name'];
                    
                    // Format status
                    $status = ucfirst($donation['status']);
                    if ($donation['status'] == 'success') {
                        $status = 'Berhasil';
                        $totalAmount += $donation['amount'];
                        $totalSuccess++;
                    } elseif ($donation['status'] == 'pending') {
                        $status = 'Menunggu';
                    } elseif ($donation['status'] == 'failed') {
                        $status = 'Gagal';
                    }
                    
                    // Set background color based on status
                    $bgColor = '';
                    if ($donation['status'] == 'success') {
                        $bgColor = ' bgcolor="#E6FFE6"'; // Light green
                    } elseif ($donation['status'] == 'pending') {
                        $bgColor = ' bgcolor="#FFFFD9"'; // Light yellow
                    } elseif ($donation['status'] == 'failed') {
                        $bgColor = ' bgcolor="#FFE6E6"'; // Light red
                    }
                    
                    echo '<tr' . $bgColor . '>';
                    echo '<td>' . $donation['id'] . '</td>';
                    echo '<td>' . date('d/m/Y H:i', strtotime($donation['created_at'])) . '</td>';
                    echo '<td>' . htmlspecialchars($donorName) . '</td>';
                    echo '<td>' . htmlspecialchars($donation['email']) . '</td>';
                    echo '<td>' . htmlspecialchars($donation['phone'] ?? '-') . '</td>';
                    echo '<td>' . htmlspecialchars($donation['campaign_title'] ?? '-') . '</td>';
                    echo '<td align="right">Rp ' . number_format($donation['amount'], 0, ',', '.') . '</td>';
                    echo '<td>' . htmlspecialchars(ucfirst($donation['payment_method'])) . '</td>';
                    echo '<td>' . $status . '</td>';
                    echo '<td>' . (!empty($donation['paid_at']) ? date('d/m/Y H:i', strtotime($donation['paid_at'])) : '-') . '</td>';
                    echo '<td>' . htmlspecialchars($donation['message'] ?? '-') . '</td>';
                    echo '</tr>';
                }
                
                echo '</table>';
                
                // Summary
                echo '<br>';
                echo '<table border="0" cellpadding="5">';
                echo '<tr><td><strong>Total Donasi:</strong></td><td>' . count($donations) . '</td></tr>';
                echo '<tr><td><strong>Donasi Berhasil:</strong></td><td>' . $totalSuccess . '</td></tr>';
                echo '<tr><td><strong>Total Terkumpul:</strong></td><td>Rp ' . number_format($totalAmount, 0, ',', '.') . '</td></tr>';
                echo '</table>';
                exit;
        }
    }

    /**
     * Download receipt (bukti donasi) untuk donasi tertentu
     * 
     * @param int $id ID donasi
     * @return void
     */
    public function downloadReceipt($id) {
        // Mendapatkan data donasi dengan detail kampanye
        $donation = $this->donationModel->findWithCampaign($id);
        
        if (!$donation) {
            $this->setFlash('error', 'Donasi tidak ditemukan.');
            $this->redirect('admin/donations');
            return;
        }
        
        // Format yang diminta: excel atau pdf
        $format = $_GET['format'] ?? 'pdf';
        
        if ($format === 'pdf') {
            // Generate receipt PDF
            $this->generatePdfReceipt($donation);
        } else {
            // Generate Excel receipt
            $this->generateExcelReceipt($donation);
        }
    }
    
    /**
     * Generate PDF receipt untuk donasi tertentu
     * 
     * @param array $donation Data donasi
     * @return void
     */
    private function generatePdfReceipt($donation) {
        // Check if we have TCPDF library
        if (!file_exists(BASEPATH . '/vendor/tecnickcom/tcpdf/tcpdf.php')) {
            $this->setFlash('error', 'TCPDF library tidak ditemukan.');
            $this->redirect('admin/donation/' . $donation['id']);
            return;
        }
        
        // Include TCPDF library
        require_once BASEPATH . '/vendor/tecnickcom/tcpdf/tcpdf.php';
        
        // Create new PDF document (Portrait, A4)
        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator(APP_NAME);
        $pdf->SetAuthor(APP_NAME);
        $pdf->SetTitle('Bukti Donasi #' . $donation['id']);
        $pdf->SetSubject('Bukti Donasi untuk ' . $donation['name']);
        
        // Remove header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Set margins
        $pdf->SetMargins(15, 15, 15);
        
        // Set auto page breaks
        $pdf->SetAutoPageBreak(true, 15);
        
        // Add a page
        $pdf->AddPage();
        
        // Add logo
        if (file_exists(PUBLIC_PATH . '/assets/img/logo.png')) {
            $pdf->Image(PUBLIC_PATH . '/assets/img/logo.png', 15, 15, 40, 0, 'PNG');
            $pdf->Ln(20);
        }
        
        // Set font
        $pdf->SetFont('helvetica', 'B', 16);
        
        // Title
        $pdf->Cell(0, 10, 'BUKTI DONASI', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, 'No. Transaksi: ' . $donation['transaction_id'], 0, 1, 'C');
        $pdf->Ln(10);
        
        // Donation details
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'Detail Donasi', 0, 1, 'L');
        $pdf->Ln(2);
        
        $pdf->SetFont('helvetica', '', 10);
        
        // Create details table
        $pdf->SetFillColor(240, 240, 240);
        
        // Detail rows
        $this->addReceiptRow($pdf, 'Tanggal Donasi', date('d/m/Y H:i', strtotime($donation['created_at'])));
        $this->addReceiptRow($pdf, 'Nama Donatur', $donation['is_anonymous'] ? 'Anonim' : $donation['name'], true);
        $this->addReceiptRow($pdf, 'Email', $donation['email']);
        if (!empty($donation['phone'])) {
            $this->addReceiptRow($pdf, 'Telepon', $donation['phone'], true);
        }
        $this->addReceiptRow($pdf, 'Jumlah Donasi', 'Rp ' . number_format($donation['amount'], 0, ',', '.'));
        $this->addReceiptRow($pdf, 'Kampanye', $donation['campaign_title'], true);
        $this->addReceiptRow($pdf, 'Metode Pembayaran', ucfirst($donation['payment_method']));
        
        // Format status
        $status = ucfirst($donation['status']);
        if ($donation['status'] == 'success') {
            $status = 'Berhasil';
        } elseif ($donation['status'] == 'pending') {
            $status = 'Menunggu Pembayaran';
        } elseif ($donation['status'] == 'failed') {
            $status = 'Gagal';
        }
        
        $this->addReceiptRow($pdf, 'Status', $status, true);
        
        if (!empty($donation['paid_at'])) {
            $this->addReceiptRow($pdf, 'Tanggal Pembayaran', date('d/m/Y H:i', strtotime($donation['paid_at'])));
        }
        
        if (!empty($donation['message'])) {
            $pdf->Ln(5);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(0, 8, 'Pesan Donatur:', 0, 1, 'L');
            $pdf->SetFont('helvetica', 'I', 10);
            $pdf->MultiCell(0, 6, $donation['message'], 0, 'L');
        }
        
        $pdf->Ln(10);
        
        // Thank you message
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 8, 'Terima kasih atas donasi Anda!', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->MultiCell(0, 5, 'Donasi ini akan sangat membantu untuk ' . $donation['campaign_title'] . '.', 0, 'C');
        
        $pdf->Ln(15);
        
        // Footer
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(0, 5, APP_NAME . ' | ' . BASE_URL, 0, 1, 'C');
        $pdf->Cell(0, 5, 'Email: ' . CONTACT_EMAIL . ' | ' . date('Y-m-d H:i:s'), 0, 1, 'C');
        
        // Generate unique filename
        $filename = 'bukti_donasi_' . $donation['transaction_id'] . '.pdf';
        
        // Send to browser
        $pdf->Output($filename, 'D');
        exit;
    }
    
    /**
     * Tambahkan baris detail receipt
     * 
     * @param \TCPDF $pdf Objek PDF
     * @param string $label Label
     * @param string $value Nilai
     * @param bool $fill Background terisi
     * @return void
     */
    private function addReceiptRow($pdf, $label, $value, $fill = false) {
        $pdf->SetFillColor(245, 245, 245);
        $pdf->Cell(50, 8, $label, 1, 0, 'L', $fill);
        $pdf->Cell(0, 8, $value, 1, 1, 'L', $fill);
    }
    
    /**
     * Generate Excel receipt untuk donasi tertentu
     * 
     * @param array $donation Data donasi
     * @return void
     */
    private function generateExcelReceipt($donation) {
        // Set header untuk download Excel
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="bukti_donasi_' . $donation['transaction_id'] . '.xls"');
        header('Cache-Control: max-age=0');
        
        echo '<html>';
        echo '<head>';
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
        echo '<style>
                body { font-family: Arial, sans-serif; }
                .header { text-align: center; margin-bottom: 20px; }
                .details { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
                .details th, .details td { border: 1px solid #ddd; padding: 8px; }
                .details th { width: 30%; text-align: left; background-color: #f2f2f2; }
                .message { border: 1px solid #ddd; padding: 10px; margin-bottom: 20px; font-style: italic; }
                .footer { text-align: center; font-size: 12px; margin-top: 30px; }
              </style>';
        echo '</head>';
        echo '<body>';
        
        echo '<div class="header">';
        echo '<h1>' . APP_NAME . '</h1>';
        echo '<h2>BUKTI DONASI</h2>';
        echo '<p>No. Transaksi: ' . $donation['transaction_id'] . '</p>';
        echo '</div>';
        
        echo '<table class="details">';
        echo '<tr><th>Tanggal Donasi</th><td>' . date('d/m/Y H:i', strtotime($donation['created_at'])) . '</td></tr>';
        echo '<tr><th>Nama Donatur</th><td>' . ($donation['is_anonymous'] ? 'Anonim' : htmlspecialchars($donation['name'])) . '</td></tr>';
        echo '<tr><th>Email</th><td>' . htmlspecialchars($donation['email']) . '</td></tr>';
        if (!empty($donation['phone'])) {
            echo '<tr><th>Telepon</th><td>' . htmlspecialchars($donation['phone']) . '</td></tr>';
        }
        echo '<tr><th>Jumlah Donasi</th><td>Rp ' . number_format($donation['amount'], 0, ',', '.') . '</td></tr>';
        echo '<tr><th>Kampanye</th><td>' . htmlspecialchars($donation['campaign_title']) . '</td></tr>';
        echo '<tr><th>Metode Pembayaran</th><td>' . ucfirst($donation['payment_method']) . '</td></tr>';
        
        // Format status
        $status = ucfirst($donation['status']);
        if ($donation['status'] == 'success') {
            $status = 'Berhasil';
        } elseif ($donation['status'] == 'pending') {
            $status = 'Menunggu Pembayaran';
        } elseif ($donation['status'] == 'failed') {
            $status = 'Gagal';
        }
        
        echo '<tr><th>Status</th><td>' . $status . '</td></tr>';
        if (!empty($donation['paid_at'])) {
            echo '<tr><th>Tanggal Pembayaran</th><td>' . date('d/m/Y H:i', strtotime($donation['paid_at'])) . '</td></tr>';
        }
        echo '</table>';
        
        if (!empty($donation['message'])) {
            echo '<h3>Pesan Donatur:</h3>';
            echo '<div class="message">' . nl2br(htmlspecialchars($donation['message'])) . '</div>';
        }
        
        echo '<div class="footer">';
        echo '<p><strong>Terima kasih atas donasi Anda!</strong></p>';
        echo '<p>Donasi ini akan sangat membantu untuk ' . htmlspecialchars($donation['campaign_title']) . '.</p>';
        echo '<p>' . APP_NAME . ' | ' . BASE_URL . '</p>';
        echo '<p>Email: ' . CONTACT_EMAIL . ' | Dicetak pada: ' . date('Y-m-d H:i:s') . '</p>';
        echo '</div>';
        
        echo '</body>';
        echo '</html>';
        exit;
    }

    /**
     * Check if user has admin privileges
     */
    private function checkAdminAuthentication() {
        if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] !== 'admin' && $_SESSION['user']['role'] !== 'super_admin')) {
            $_SESSION['error'] = 'Anda tidak memiliki akses untuk halaman ini.';
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }
    }

    /**
     * Change campaign status
     * 
     * @param int|null $id Campaign ID (optional, can be from POST)
     * @param string|null $status New status (optional, can be from POST)
     * @return void
     */
    public function change_campaign_status($id = null, $status = null) {
        // Check admin permissions
        $this->checkAdminAuthentication();
        
        // Get ID and status from POST if not provided as parameters
        if ($id === null || $status === null) {
            if (isset($_POST['campaign_id'])) {
                $id = (int)$_POST['campaign_id'];
            }
            if (isset($_POST['status'])) {
                $status = $_POST['status'];
            }
        }
        
        // Validate ID and status
        if (!$id || $id <= 0) {
            $_SESSION['error'] = 'ID kampanye tidak valid';
            header('Location: ' . BASE_URL . '/admin/campaigns');
            exit;
        }
        
        // Validate status
        $allowedStatuses = ['active', 'pending', 'inactive', 'ended', 'rejected'];
        if (!in_array($status, $allowedStatuses)) {
            $_SESSION['error'] = 'Status tidak valid';
            header('Location: ' . BASE_URL . '/admin/campaigns');
            exit;
        }
        
        // Get campaign model
        $campaignModel = new \App\Models\Campaign();
        
        // Update status
        $result = $campaignModel->updateStatus($id, $status);
        
        if ($result) {
            $_SESSION['success'] = 'Status kampanye berhasil diubah';
        } else {
            $_SESSION['error'] = 'Gagal mengubah status kampanye';
        }
        
        // Redirect back to campaigns page
        header('Location: ' . BASE_URL . '/admin/campaigns');
        exit;
    }

    /**
     * Delete donation
     * 
     * @param int|null $id Donation ID (optional, can be from POST)
     * @return void
     */
    public function delete_donation($id = null) {
        // Check admin permissions
        $this->checkAdminAuthentication();
        
        // Get ID from POST if not provided as parameter
        if ($id === null) {
            if (isset($_POST['donation_id'])) {
                $id = (int)$_POST['donation_id'];
            } else {
                $_SESSION['error'] = 'ID donasi tidak valid';
                header('Location: ' . BASE_URL . '/admin/donations');
                exit;
            }
        }
        
        // Validate ID
        if (!$id || $id <= 0) {
            $_SESSION['error'] = 'ID donasi tidak valid';
            header('Location: ' . BASE_URL . '/admin/donations');
            exit;
        }
        
        // Get donation model
        $donationModel = new \App\Models\Donation();
        
        // Try to delete donation
        $result = $donationModel->delete($id);
        
        if ($result) {
            $_SESSION['success'] = 'Donasi berhasil dihapus';
        } else {
            $_SESSION['error'] = 'Gagal menghapus donasi';
        }
        
        // Redirect back to donations page
        header('Location: ' . BASE_URL . '/admin/donations');
        exit;
    }

    /**
     * Change donation status
     * 
     * @param int|null $id Donation ID (optional, can be from POST)
     * @param string|null $status New status (optional, can be from POST)
     * @return void
     */
    public function change_donation_status($id = null, $status = null) {
        // Check admin permissions
        $this->checkAdminAuthentication();
        
        // Get ID and status from POST if not provided as parameters
        if ($id === null || $status === null) {
            if (isset($_POST['donation_id'])) {
                $id = (int)$_POST['donation_id'];
            }
            if (isset($_POST['status'])) {
                $status = $_POST['status'];
            }
        }
        
        // Validate ID
        if (!$id || $id <= 0) {
            $_SESSION['error'] = 'ID donasi tidak valid';
            header('Location: ' . BASE_URL . '/admin/donations');
            exit;
        }
        
        // Validate status
        $allowedStatuses = ['pending', 'completed', 'cancelled', 'success', 'failed'];
        if (!in_array($status, $allowedStatuses)) {
            $_SESSION['error'] = 'Status tidak valid';
            header('Location: ' . BASE_URL . '/admin/donations');
            exit;
        }
        
        // Get donation model
        $donationModel = new \App\Models\Donation();
        
        // Update status
        $result = $donationModel->updateStatus($id, $status);
        
        if ($result) {
            $_SESSION['success'] = 'Status donasi berhasil diubah';
        } else {
            $_SESSION['error'] = 'Gagal mengubah status donasi';
        }
        
        // Redirect back to donations page
        header('Location: ' . BASE_URL . '/admin/donations');
        exit;
    }

    /**
     * Handle URL route for delete_donation - this passes to our main method with proper parameter
     * 
     * @param int $id The donation ID
     * @return void
     */
    public function deleteDonation_url($id) {
        return $this->delete_donation($id);
    }
    
    /**
     * Handle URL route for delete_campaign - this passes to our main method with proper parameter
     * 
     * @param int $id The campaign ID
     * @return void
     */
    public function deleteCampaign_url($id) {
        return $this->delete_campaign($id);
    }
    
    /**
     * Admin profile page
     * 
     * @return void
     */
    public function profile() {
        // Check admin authentication
        $this->checkAdminAuthentication();
        
        // Get admin user data
        $userId = $_SESSION['user']['id'];
        $user = $this->userModel->find($userId);
        
        $this->view('admin/profile', [
            'title' => 'Admin Profile',
            'user' => $user
        ]);
    }
    
    /**
     * Export donation reports
     * 
     * @param string $format Format of the report (excel, pdf)
     * @return void
     */
    public function reports($type, $format = 'excel') {
        // Check admin permissions
        $this->checkAdminAuthentication();
        
        // Get format from query string if not provided
        if (isset($_GET['type'])) {
            $format = $_GET['type'];
        }
        
        // Validate format
        if (!in_array($format, ['excel', 'pdf'])) {
            $format = 'excel';
        }
        
        switch ($type) {
            case 'donations':
                $this->exportDonationsReport($format);
                break;
            case 'campaigns':
                $this->exportCampaignsReport($format);
                break;
            case 'summary':
                $this->exportSummaryReport();
                break;
            default:
                $_SESSION['error'] = 'Jenis laporan tidak valid';
                $this->redirect('admin/dashboard');
                break;
        }
    }
    
    /**
     * Export donations report
     * 
     * @param string $format Format of the report (excel, pdf)
     * @return void
     */
    private function exportDonationsReport($format = 'excel') {
        // Get all donations with campaign and donor info
        $donations = $this->donationModel->getAllWithDetails();
        
        if ($format === 'pdf') {
            $this->exportDonationsPdf($donations);
        } else {
            $this->exportDonationsExcel($donations);
        }
    }
    
    /**
     * Export campaigns report
     * 
     * @param string $format Format of the report (excel, pdf)
     * @return void
     */
    private function exportCampaignsReport($format = 'excel') {
        // Get all campaigns with creator and category info
        $campaigns = $this->campaignModel->getAllWithDetails();
        
        if ($format === 'pdf') {
            $this->exportCampaignsPdf($campaigns);
        } else {
            $this->exportCampaignsExcel($campaigns);
        }
    }
    
    /**
     * Export summary report with statistics
     * 
     * @return void
     */
    private function exportSummaryReport() {
        // Get statistics
        $donationStats = $this->donationModel->getStats();
        $campaignStats = $this->campaignModel->getDashboardStats();
        $userStats = $this->userModel->getStats();
        
        // Export summary as PDF
        $this->exportSummaryPdf($donationStats, $campaignStats, $userStats);
    }
    
    /**
     * Export donations to Excel
     * 
     * @param array $donations Donation data
     * @return void
     */
    private function exportDonationsExcel($donations) {
        // Required libraries
        require_once BASEPATH . '/vendor/autoload.php';
        
        // Create new Spreadsheet object
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set document properties
        $spreadsheet->getProperties()
            ->setCreator(APP_NAME)
            ->setLastModifiedBy(APP_NAME)
            ->setTitle('Laporan Donasi')
            ->setSubject('Laporan Donasi ' . date('d-m-Y'))
            ->setDescription('Laporan donasi dari aplikasi ' . APP_NAME);
        
        // Add header row
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Pendonasi');
        $sheet->setCellValue('C1', 'Email');
        $sheet->setCellValue('D1', 'Kampanye');
        $sheet->setCellValue('E1', 'Jumlah');
        $sheet->setCellValue('F1', 'Metode Pembayaran');
        $sheet->setCellValue('G1', 'Status');
        $sheet->setCellValue('H1', 'Tanggal');
        
        // Style the header row
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getStyle('A1:H1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFCCCCCC');
        
        // Add data rows
        $row = 2;
        foreach ($donations as $donation) {
            $sheet->setCellValue('A' . $row, $donation['id']);
            $sheet->setCellValue('B' . $row, $donation['is_anonymous'] ? 'Anonim' : $donation['name']);
            $sheet->setCellValue('C' . $row, $donation['is_anonymous'] ? '-' : $donation['email']);
            $sheet->setCellValue('D' . $row, $donation['campaign_title']);
            $sheet->setCellValue('E' . $row, $donation['amount']);
            $sheet->setCellValue('F' . $row, $donation['payment_method']);
            $sheet->setCellValue('G' . $row, $donation['status']);
            $sheet->setCellValue('H' . $row, $donation['created_at']);
            
            $row++;
        }
        
        // Auto size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Create writer
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Laporan_Donasi_' . date('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');
        
        // Save file to output
        $writer->save('php://output');
        exit;
    }
    
    /**
     * Export campaigns to Excel
     * 
     * @param array $campaigns Campaign data
     * @return void
     */
    private function exportCampaignsExcel($campaigns) {
        // Required libraries
        require_once BASEPATH . '/vendor/autoload.php';
        
        // Create new Spreadsheet object
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set document properties
        $spreadsheet->getProperties()
            ->setCreator(APP_NAME)
            ->setLastModifiedBy(APP_NAME)
            ->setTitle('Laporan Kampanye')
            ->setSubject('Laporan Kampanye ' . date('d-m-Y'))
            ->setDescription('Laporan kampanye dari aplikasi ' . APP_NAME);
        
        // Add header row
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Judul');
        $sheet->setCellValue('C1', 'Kategori');
        $sheet->setCellValue('D1', 'Dibuat Oleh');
        $sheet->setCellValue('E1', 'Target');
        $sheet->setCellValue('F1', 'Terkumpul');
        $sheet->setCellValue('G1', 'Progress');
        $sheet->setCellValue('H1', 'Status');
        $sheet->setCellValue('I1', 'Tanggal Dibuat');
        
        // Style the header row
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);
        $sheet->getStyle('A1:I1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFCCCCCC');
        
        // Add data rows
        $row = 2;
        foreach ($campaigns as $campaign) {
            $progress = $campaign['goal_amount'] > 0 ? 
                        min(100, round(($campaign['current_amount'] / $campaign['goal_amount']) * 100)) : 0;
                        
            $sheet->setCellValue('A' . $row, $campaign['id']);
            $sheet->setCellValue('B' . $row, $campaign['title']);
            $sheet->setCellValue('C' . $row, $campaign['category_name']);
            $sheet->setCellValue('D' . $row, $campaign['creator_name']);
            $sheet->setCellValue('E' . $row, $campaign['goal_amount']);
            $sheet->setCellValue('F' . $row, $campaign['current_amount']);
            $sheet->setCellValue('G' . $row, $progress . '%');
            $sheet->setCellValue('H' . $row, $campaign['status']);
            $sheet->setCellValue('I' . $row, $campaign['created_at']);
            
            $row++;
        }
        
        // Auto size columns
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Create writer
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Laporan_Kampanye_' . date('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');
        
        // Save file to output
        $writer->save('php://output');
        exit;
    }
    
    /**
     * Export donations to PDF
     * 
     * @param array $donations Donation data
     * @return void
     */
    private function exportDonationsPdf($donations) {
        // Required libraries
        require_once BASEPATH . '/vendor/autoload.php';
        
        // HTML content
        $html = '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; }
                h1 { text-align: center; font-size: 18px; margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; }
                th { background-color: #f2f2f2; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .text-center { text-align: center; }
                .text-right { text-align: right; }
            </style>
        </head>
        <body>
            <h1>Laporan Donasi ' . APP_NAME . '</h1>
            <p>Tanggal: ' . date('d-m-Y') . '</p>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pendonasi</th>
                        <th>Kampanye</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ($donations as $donation) {
            $html .= '
                <tr>
                    <td>' . $donation['id'] . '</td>
                    <td>' . ($donation['is_anonymous'] ? 'Anonim' : $donation['name']) . '</td>
                    <td>' . $donation['campaign_title'] . '</td>
                    <td class="text-right">Rp ' . number_format($donation['amount'], 0, ',', '.') . '</td>
                    <td>' . $donation['status'] . '</td>
                    <td>' . date('d-m-Y', strtotime($donation['created_at'])) . '</td>
                </tr>';
        }
        
        $html .= '
                </tbody>
            </table>
        </body>
        </html>';
        
        // Initialize dompdf
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        
        // Output the PDF
        $dompdf->stream('Laporan_Donasi_' . date('Y-m-d') . '.pdf', [
            'Attachment' => true
        ]);
        exit;
    }
    
    /**
     * Export campaigns to PDF
     * 
     * @param array $campaigns Campaign data
     * @return void
     */
    private function exportCampaignsPdf($campaigns) {
        // Required libraries
        require_once BASEPATH . '/vendor/autoload.php';
        
        // HTML content
        $html = '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; }
                h1 { text-align: center; font-size: 18px; margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; }
                th { background-color: #f2f2f2; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .text-center { text-align: center; }
                .text-right { text-align: right; }
            </style>
        </head>
        <body>
            <h1>Laporan Kampanye ' . APP_NAME . '</h1>
            <p>Tanggal: ' . date('d-m-Y') . '</p>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Judul</th>
                        <th>Kategori</th>
                        <th>Dibuat Oleh</th>
                        <th>Target</th>
                        <th>Terkumpul</th>
                        <th>Progress</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ($campaigns as $campaign) {
            $progress = $campaign['goal_amount'] > 0 ? 
                      min(100, round(($campaign['current_amount'] / $campaign['goal_amount']) * 100)) : 0;
                      
            $html .= '
                <tr>
                    <td>' . $campaign['id'] . '</td>
                    <td>' . $campaign['title'] . '</td>
                    <td>' . $campaign['category_name'] . '</td>
                    <td>' . $campaign['creator_name'] . '</td>
                    <td class="text-right">Rp ' . number_format($campaign['goal_amount'], 0, ',', '.') . '</td>
                    <td class="text-right">Rp ' . number_format($campaign['current_amount'], 0, ',', '.') . '</td>
                    <td class="text-center">' . $progress . '%</td>
                    <td>' . $campaign['status'] . '</td>
                </tr>';
        }
        
        $html .= '
                </tbody>
            </table>
        </body>
        </html>';
        
        // Initialize dompdf
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        
        // Output the PDF
        $dompdf->stream('Laporan_Kampanye_' . date('Y-m-d') . '.pdf', [
            'Attachment' => true
        ]);
        exit;
    }
    
    /**
     * Export summary report as PDF
     * 
     * @param array $donationStats Donation statistics
     * @param array $campaignStats Campaign statistics
     * @param array $userStats User statistics
     * @return void
     */
    private function exportSummaryPdf($donationStats, $campaignStats, $userStats) {
        // Required libraries
        require_once BASEPATH . '/vendor/autoload.php';
        
        // HTML content
        $html = '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; }
                h1 { text-align: center; font-size: 18px; margin-bottom: 20px; }
                h2 { font-size: 16px; margin-top: 20px; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                th { background-color: #f2f2f2; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .text-center { text-align: center; }
                .text-right { text-align: right; }
                .stat-card { border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; }
                .stat-title { font-weight: bold; }
                .stat-value { font-size: 18px; margin: 5px 0; }
                .stat-footer { font-size: 10px; color: #666; }
                .row { display: flex; flex-wrap: wrap; margin: 0 -5px; }
                .col { flex: 1; padding: 0 5px; }
            </style>
        </head>
        <body>
            <h1>Ringkasan Statistik ' . APP_NAME . '</h1>
            <p>Tanggal: ' . date('d-m-Y') . '</p>
            
            <h2>Statistik Umum</h2>
            <div class="row">
                <div class="col">
                    <div class="stat-card">
                        <div class="stat-title">Total Donasi</div>
                        <div class="stat-value">Rp ' . number_format($donationStats['total_amount'] ?? 0, 0, ',', '.') . '</div>
                        <div class="stat-footer">' . number_format($donationStats['total_donations'] ?? 0, 0, ',', '.') . ' transaksi</div>
                    </div>
                </div>
                <div class="col">
                    <div class="stat-card">
                        <div class="stat-title">Kampanye Aktif</div>
                        <div class="stat-value">' . number_format($campaignStats['active_count'] ?? 0, 0, ',', '.') . '</div>
                        <div class="stat-footer">Dari total ' . number_format($campaignStats['total_count'] ?? 0, 0, ',', '.') . ' kampanye</div>
                    </div>
                </div>
                <div class="col">
                    <div class="stat-card">
                        <div class="stat-title">Total Donatur</div>
                        <div class="stat-value">' . number_format($donationStats['total_donors'] ?? 0, 0, ',', '.') . '</div>
                        <div class="stat-footer">' . number_format($donationStats['new_donors'] ?? 0, 0, ',', '.') . ' donatur baru bulan ini</div>
                    </div>
                </div>
                <div class="col">
                    <div class="stat-card">
                        <div class="stat-title">Rata-Rata Donasi</div>
                        <div class="stat-value">Rp ' . number_format($donationStats['avg_donation'] ?? 0, 0, ',', '.') . '</div>
                        <div class="stat-footer">Per transaksi</div>
                    </div>
                </div>
            </div>
            
            <h2>Statistik Donasi Bulanan</h2>
            <table>
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th>Jumlah Donasi</th>
                        <th>Total Transaksi</th>
                        <th>Rata-Rata</th>
                    </tr>
                </thead>
                <tbody>';
                
        // Add monthly data if available
        if (!empty($donationStats['monthly_data'])) {
            foreach ($donationStats['monthly_data'] as $month) {
                $html .= '
                    <tr>
                        <td>' . $month['month'] . '</td>
                        <td class="text-right">Rp ' . number_format($month['amount'], 0, ',', '.') . '</td>
                        <td class="text-center">' . $month['count'] . '</td>
                        <td class="text-right">Rp ' . number_format($month['average'], 0, ',', '.') . '</td>
                    </tr>';
            }
        } else {
            $html .= '
                <tr>
                    <td colspan="4" class="text-center">Belum ada data donasi bulanan</td>
                </tr>';
        }
                
        $html .= '
                </tbody>
            </table>
            
            <h2>Kampanye Teratas</h2>
            <table>
                <thead>
                    <tr>
                        <th>Judul Kampanye</th>
                        <th>Target</th>
                        <th>Terkumpul</th>
                        <th>Progress</th>
                        <th>Donatur</th>
                    </tr>
                </thead>
                <tbody>';
                
        // Add top campaigns data if available
        if (!empty($campaignStats['top_campaigns'])) {
            foreach ($campaignStats['top_campaigns'] as $campaign) {
                $progress = $campaign['goal_amount'] > 0 ? 
                          min(100, round(($campaign['current_amount'] / $campaign['goal_amount']) * 100)) : 0;
                          
                $html .= '
                    <tr>
                        <td>' . $campaign['title'] . '</td>
                        <td class="text-right">Rp ' . number_format($campaign['goal_amount'], 0, ',', '.') . '</td>
                        <td class="text-right">Rp ' . number_format($campaign['current_amount'], 0, ',', '.') . '</td>
                        <td class="text-center">' . $progress . '%</td>
                        <td class="text-center">' . $campaign['donor_count'] . '</td>
                    </tr>';
            }
        } else {
            $html .= '
                <tr>
                    <td colspan="5" class="text-center">Belum ada data kampanye teratas</td>
                </tr>';
        }
                
        $html .= '
                </tbody>
            </table>
        </body>
        </html>';
        
        // Initialize dompdf
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Output the PDF
        $dompdf->stream('Ringkasan_Statistik_' . date('Y-m-d') . '.pdf', [
            'Attachment' => true
        ]);
        exit;
    }

    /**
     * Update admin profile
     * 
     * @return void
     */
    public function updateProfile() {
        // Check admin authentication
        $this->checkAdminAuthentication();
        
        $userId = $_SESSION['user']['id'];
        
        // Validate input
        $errors = $this->validate($_POST, [
            'name' => 'required|max:100',
            'phone' => 'max:20',
            'email' => 'required|email|max:100'
        ]);
        
        if (!empty($errors)) {
            $this->setFlash('errors', $errors);
            $this->redirect('admin/profile');
            return;
        }
        
        // Upload profile picture if provided
        $profilePicture = null;
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $profilePicture = $this->uploadFile('profile_picture');
        }
        
        // Update user data
        $userData = [
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'phone' => $_POST['phone'] ?? null,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($profilePicture) {
            $userData['profile_picture'] = $profilePicture;
        }
        
        $this->userModel->update($userId, $userData);
        
        // Update session data
        $_SESSION['user']['name'] = $_POST['name'];
        $_SESSION['user']['email'] = $_POST['email'];
        if ($profilePicture) {
            $_SESSION['user']['profile_picture'] = $profilePicture;
        }
        
        $this->setFlash('success', 'Profil berhasil diperbarui');
        $this->redirect('admin/profile');
    }
}
