<?php
namespace App\Models;

/**
 * Model untuk mengelola data kampanye donasi
 */
class Campaign extends Model {
    protected $table = 'campaigns';
    protected $fillable = [
        'title', 'slug', 'description', 'short_description', 
        'goal_amount', 'current_amount', 'start_date', 'end_date',
        'featured_image', 'banner_image', 'status', 'user_id', 'category_id',
        'is_featured', 'allow_custom_amount', 'donation_amounts',
        'donation_info', 'meta_tags', 'created_at', 'updated_at'
    ];
    
    /**
     * Mendapatkan kampanye yang aktif
     * 
     * @return array
     */
    public function getActiveCampaigns() {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE status = 'active' AND start_date <= NOW() AND (end_date >= NOW() OR end_date IS NULL) ORDER BY created_at DESC"
        );
    }
    
    /**
     * Mendapatkan kampanye unggulan
     * 
     * @param int $limit Jumlah data
     * @return array
     */
    public function getFeaturedCampaigns($limit = 6) {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE status = 'active' AND is_featured = 1 AND start_date <= NOW() AND (end_date >= NOW() OR end_date IS NULL) ORDER BY created_at DESC LIMIT ?",
            [$limit]
        );
    }
    
    /**
     * Mendapatkan kampanye berdasarkan slug
     * 
     * @param string $slug Slug kampanye
     * @return array|false
     */
    public function findBySlug($slug) {
        return $this->db->fetch(
            "SELECT c.*, u.name as creator_name, cat.name as category_name 
            FROM {$this->table} c
            LEFT JOIN users u ON c.user_id = u.id
            LEFT JOIN categories cat ON c.category_id = cat.id
            WHERE c.slug = ?",
            [$slug]
        );
    }
    
    /**
     * Mendapatkan kampanye dengan detail
     * 
     * @param int $id ID kampanye
     * @return array|false
     */
    public function getWithDetails($id) {
        return $this->db->fetch(
            "SELECT c.*, u.name as creator_name, cat.name as category_name 
            FROM {$this->table} c
            LEFT JOIN users u ON c.user_id = u.id
            LEFT JOIN categories cat ON c.category_id = cat.id
            WHERE c.id = ?",
            [$id]
        );
    }
    
    /**
     * Mencari kampanye
     * 
     * @param string $keyword Kata kunci pencarian
     * @param int $categoryId ID kategori (opsional)
     * @param string $status Status kampanye (opsional)
     * @return array
     */
    public function search($keyword, $categoryId = null, $status = 'active') {
        $params = [];
        $sql = "SELECT c.*, cat.name as category_name 
                FROM {$this->table} c
                LEFT JOIN categories cat ON c.category_id = cat.id
                WHERE 1=1";
        
        if ($keyword) {
            $sql .= " AND (c.title LIKE ? OR c.description LIKE ?)";
            $params[] = "%{$keyword}%";
            $params[] = "%{$keyword}%";
        }
        
        if ($categoryId) {
            $sql .= " AND c.category_id = ?";
            $params[] = $categoryId;
        }
        
        if ($status) {
            $sql .= " AND c.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY c.created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Update jumlah donasi terkumpul
     * 
     * @param int $campaignId ID kampanye
     * @param float $amount Jumlah donasi
     * @param bool $increment True untuk menambah, false untuk mengurangi
     * @return bool
     */
    public function updateAmount($campaignId, $amount, $increment = true) {
        if ($increment) {
            $sql = "UPDATE {$this->table} SET current_amount = current_amount + ? WHERE id = ?";
        } else {
            $sql = "UPDATE {$this->table} SET current_amount = current_amount - ? WHERE id = ?";
        }
        
        $this->db->query($sql, [$amount, $campaignId]);
        
        return true;
    }
    
    /**
     * Mendapatkan statistik kampanye
     * 
     * @param int $campaignId ID kampanye
     * @return array
     */
    public function getStats($campaignId) {
        $campaign = $this->find($campaignId);
        
        if (!$campaign) {
            return [
                'total_donors' => 0,
                'total_donations' => 0,
                'progress_percentage' => 0
            ];
        }
        
        // Jumlah donatur unik
        $totalDonors = $this->db->fetchColumn(
            "SELECT COUNT(DISTINCT email) FROM donations WHERE campaign_id = ? AND status = 'success'",
            [$campaignId]
        );
        
        // Jumlah donasi
        $totalDonations = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM donations WHERE campaign_id = ? AND status = 'success'",
            [$campaignId]
        );
        
        // Persentase progres
        $progressPercentage = 0;
        if ($campaign['goal_amount'] > 0) {
            $progressPercentage = min(100, round(($campaign['current_amount'] / $campaign['goal_amount']) * 100));
        }
        
        return [
            'total_donors' => $totalDonors,
            'total_donations' => $totalDonations,
            'progress_percentage' => $progressPercentage
        ];
    }
    
    /**
     * Duplikat kampanye
     * 
     * @param int $campaignId ID kampanye
     * @param int $userId ID user yang menduplikasi
     * @return int|false ID kampanye baru atau false jika gagal
     */
    public function duplicate($campaignId, $userId) {
        $campaign = $this->find($campaignId);
        
        if (!$campaign) {
            return false;
        }
        
        // Buat slug baru
        $newSlug = $campaign['slug'] . '-copy-' . time();
        
        // Data kampanye baru
        $newCampaign = [
            'title' => $campaign['title'] . ' (Copy)',
            'slug' => $newSlug,
            'description' => $campaign['description'],
            'short_description' => $campaign['short_description'],
            'goal_amount' => $campaign['goal_amount'],
            'current_amount' => 0,
            'start_date' => date('Y-m-d'),
            'end_date' => $campaign['end_date'],
            'featured_image' => $campaign['featured_image'],
            'status' => 'draft',
            'user_id' => $userId,
            'category_id' => $campaign['category_id'],
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->create($newCampaign);
    }
    
    /**
     * Mendapatkan kampanye terkait berdasarkan kategori
     * 
     * @param int $campaignId ID kampanye saat ini (untuk dikecualikan)
     * @param int $categoryId ID kategori untuk mencari kampanye terkait
     * @param int $limit Jumlah kampanye yang diambil
     * @return array
     */
    public function getRelatedCampaigns($campaignId, $categoryId, $limit = 3) {
        return $this->db->fetchAll(
            "SELECT c.*, cat.name as category_name 
            FROM {$this->table} c
            LEFT JOIN categories cat ON c.category_id = cat.id
            WHERE c.id != ? AND c.category_id = ? AND c.status = 'active'
            ORDER BY c.created_at DESC
            LIMIT ?",
            [$campaignId, $categoryId, $limit]
        );
    }
    
    /**
     * Get campaign statistics for admin dashboard
     * 
     * @return array
     */
    public function getDashboardStats() {
        // Get active campaign count
        $activeCount = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->table} WHERE status = 'active'"
        );
        
        // Get total campaign count
        $totalCount = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->table}"
        );
        
        // Get total donations received
        $totalDonations = $this->db->fetchColumn(
            "SELECT SUM(current_amount) FROM {$this->table}"
        );
        
        // Get top campaigns
        $topCampaigns = $this->db->fetchAll(
            "SELECT c.id, c.title, c.goal_amount, c.current_amount, c.slug,
                (SELECT COUNT(*) FROM donations d WHERE d.campaign_id = c.id AND d.status = 'success') as donor_count
             FROM {$this->table} c
             WHERE c.status = 'active'
             ORDER BY c.current_amount DESC
             LIMIT 5"
        );
        
        // Get campaigns by category
        $campaignsByCategory = $this->db->fetchAll(
            "SELECT cat.name, COUNT(c.id) as campaign_count
             FROM {$this->table} c
             LEFT JOIN categories cat ON c.category_id = cat.id
             GROUP BY cat.name
             ORDER BY campaign_count DESC"
        );
        
        // Calculate monthly growth
        $thisMonth = date('Y-m-01');
        $lastMonth = date('Y-m-01', strtotime('-1 month'));
        
        $thisMonthCount = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->table} WHERE created_at >= ?",
            [$thisMonth]
        );
        
        $lastMonthCount = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->table} WHERE created_at >= ? AND created_at < ?",
            [$lastMonth, $thisMonth]
        );
        
        $growthPercentage = 0;
        if ($lastMonthCount > 0) {
            $growthPercentage = round((($thisMonthCount - $lastMonthCount) / $lastMonthCount) * 100);
        }
        
        return [
            'active_count' => (int)$activeCount,
            'total_count' => (int)$totalCount,
            'total_donations' => (float)$totalDonations,
            'top_campaigns' => $topCampaigns,
            'campaigns_by_category' => $campaignsByCategory,
            'this_month_count' => (int)$thisMonthCount,
            'last_month_count' => (int)$lastMonthCount,
            'growth_percentage' => $growthPercentage
        ];
    }
    
    /**
     * Get all campaigns with creator and category info for reports
     * 
     * @return array
     */
    public function getAllWithDetails() {
        return $this->db->fetchAll(
            "SELECT c.*, 
                u.name as creator_name, 
                cat.name as category_name,
                (SELECT COUNT(*) FROM donations d WHERE d.campaign_id = c.id AND d.status = 'success') as donor_count
             FROM {$this->table} c
             LEFT JOIN users u ON c.user_id = u.id
             LEFT JOIN categories cat ON c.category_id = cat.id
             ORDER BY c.created_at DESC"
        );
    }
    
    /**
     * Mendapatkan kampanye terkait berdasarkan kategori dan ID
     * 
     * @param int $campaignId ID kampanye yang sedang dibuka
     * @param int $categoryId Kategori kampanye
     * @param int $limit Jumlah data
     * @return array
     */
    public function getRelatedCampaignsById($campaignId, $categoryId, $limit = 3) {
        return $this->db->fetchAll(
            "SELECT c.*, cat.name as category_name 
            FROM {$this->table} c
            LEFT JOIN categories cat ON c.category_id = cat.id
            WHERE c.id != ? AND c.category_id = ? AND c.status = 'active'
            ORDER BY c.created_at DESC
            LIMIT ?",
            [$campaignId, $categoryId, $limit]
        );
    }
} 