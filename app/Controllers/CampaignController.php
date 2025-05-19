<?php
namespace App\Controllers;

use App\Models\Campaign;
use App\Models\Donation;

/**
 * Controller untuk mengelola kampanye
 */
class CampaignController extends Controller {
    private $campaignModel;
    private $donationModel;
    
    public function __construct() {
        parent::__construct();
        $this->campaignModel = new Campaign();
        $this->donationModel = new Donation();
    }
    
    /**
     * Halaman daftar kampanye
     * 
     * @return void
     */
    public function index() {
        // Parameter filter dan pagination
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 9;
        $categoryId = isset($_GET['category']) ? $_GET['category'] : null;
        $keyword = isset($_GET['search']) ? $_GET['search'] : null;
        
        // Cari kampanye sesuai filter
        $campaigns = $this->campaignModel->search($keyword, $categoryId);
        
        // Dapatkan kategori untuk filter
        $categories = $this->db->fetchAll("SELECT * FROM categories ORDER BY name ASC");
        
        $this->view('campaign/index', [
            'title' => 'Daftar Kampanye - ' . APP_NAME,
            'campaigns' => $campaigns,
            'categories' => $categories,
            'categoryId' => $categoryId,
            'keyword' => $keyword
        ]);
    }
    
    /**
     * Halaman detail kampanye
     * 
     * @param string $slug Slug kampanye
     * @return void
     */
    public function detail($slug) {
        // Dapatkan data kampanye
        $campaign = $this->campaignModel->findBySlug($slug);
        
        // Jika kampanye tidak ditemukan, arahkan ke halaman 404
        if (!$campaign) {
            $this->redirect('error/not-found');
            return;
        }
        
        // Dapatkan statistik kampanye
        $stats = $this->campaignModel->getStats($campaign['id']);
        
        // Dapatkan donasi terbaru untuk kampanye ini
        $recentDonations = $this->donationModel->getRecentByCampaignId($campaign['id'], 5);
        
        // Dapatkan kampanye terkait
        $relatedCampaigns = $this->campaignModel->getRelatedCampaigns($campaign['id'], $campaign['category_id'], 3);
        
        $this->view('campaign/detail', [
            'title' => $campaign['title'] . ' - ' . APP_NAME,
            'campaign' => $campaign,
            'stats' => $stats,
            'recentDonations' => $recentDonations,
            'relatedCampaigns' => $relatedCampaigns
        ]);
    }
    
    /**
     * Halaman form buat kampanye
     * 
     * @return void
     */
    public function create() {
        // Cek apakah user sudah login
        if (!isset($_SESSION['user'])) {
            $this->setFlash('error', 'Silakan login terlebih dahulu untuk membuat kampanye.');
            $this->redirect('auth/login');
            return;
        }
        
        // Dapatkan daftar kategori
        $categories = $this->db->fetchAll("SELECT * FROM categories ORDER BY name ASC");
        
        $this->view('campaign/form', [
            'title' => 'Buat Kampanye - ' . APP_NAME,
            'categories' => $categories,
            'campaign' => null,
            'isEdit' => false
        ]);
    }
    
    /**
     * Halaman form edit kampanye
     * 
     * @param int $id ID kampanye
     * @return void
     */
    public function edit($id) {
        // Cek apakah user sudah login
        if (!isset($_SESSION['user'])) {
            $this->setFlash('error', 'Silakan login terlebih dahulu.');
            $this->redirect('auth/login');
            return;
        }
        
        // Dapatkan data kampanye
        $campaign = $this->campaignModel->find($id);
        
        // Jika kampanye tidak ditemukan
        if (!$campaign) {
            $this->setFlash('error', 'Kampanye tidak ditemukan.');
            $this->redirect('campaign');
            return;
        }
        
        // Cek apakah kampanye milik user yang login atau user adalah admin
        if ($campaign['user_id'] != $_SESSION['user']['id'] && 
            $_SESSION['user']['role'] != 'admin' && 
            $_SESSION['user']['role'] != 'super_admin') {
            $this->setFlash('error', 'Anda tidak memiliki akses untuk mengedit kampanye ini.');
            $this->redirect('campaign');
            return;
        }
        
        // Dapatkan daftar kategori
        $categories = $this->db->fetchAll("SELECT * FROM categories ORDER BY name ASC");
        
        $this->view('campaign/form', [
            'title' => 'Edit Kampanye - ' . APP_NAME,
            'categories' => $categories,
            'campaign' => $campaign,
            'isEdit' => true
        ]);
    }
    
    /**
     * Method untuk membagikan kampanye ke sosial media
     * 
     * @param string $slug Slug kampanye
     * @param string $platform Platform sosial media (facebook, twitter, whatsapp)
     * @return void
     */
    public function share($slug, $platform) {
        // Dapatkan data kampanye
        $campaign = $this->campaignModel->findBySlug($slug);
        
        if (!$campaign) {
            $this->redirect('error/not-found');
            return;
        }
        
        $campaignUrl = BASE_URL . '/campaign/detail/' . $campaign['slug'];
        $shareText = 'Bantu ' . $campaign['title'] . ' melalui DonateHub';
        
        switch ($platform) {
            case 'facebook':
                $shareUrl = 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($campaignUrl);
                break;
            case 'twitter':
                $shareUrl = 'https://twitter.com/intent/tweet?url=' . urlencode($campaignUrl) . '&text=' . urlencode($shareText);
                break;
            case 'whatsapp':
                $shareUrl = 'https://wa.me/?text=' . urlencode($shareText . ' ' . $campaignUrl);
                break;
            default:
                $this->redirect('campaign/detail/' . $slug);
                return;
        }
        
        // Redirect ke URL share
        header('Location: ' . $shareUrl);
        exit;
    }
} 