<?php
namespace App\Models;

/**
 * Model untuk mengelola data donasi
 */
class Donation extends Model {
    protected $table = 'donations';
    protected $fillable = [
        'campaign_id', 'user_id', 'name', 'email', 'phone', 'amount', 
        'payment_method', 'payment_channel', 'transaction_id', 'order_id', 
        'va_number', 'payment_url', 'message', 'is_anonymous', 'status',
        'payment_expiry', 'paid_at', 'settlement_time', 'refund_time', 'notify_success'
    ];
    
    /**
     * Membuat donasi baru
     * 
     * @param array $data Data donasi
     * @return array|false Data donasi dengan info pembayaran atau false jika gagal
     */
    public function createDonation($data) {
        // Validasi jumlah donasi
        if ($data['amount'] < MIN_DONATION_AMOUNT || $data['amount'] > MAX_DONATION_AMOUNT) {
            return false;
        }
        
        // Set status awal
        $data['status'] = 'pending';
        
        // Set waktu pembuatan
        $data['created_at'] = date('Y-m-d H:i:s');
        
        // Generate transaction ID
        $data['transaction_id'] = $this->generateTransactionId();
        
        // Simpan donasi ke database
        $donationId = $this->create($data);
        
        if (!$donationId) {
            return false;
        }
        
        // Dapatkan data donasi lengkap
        $donation = $this->find($donationId);
        
        // Buat pembayaran di Midtrans
        $paymentInfo = $this->createMidtransPayment($donation);
        
        if (!$paymentInfo) {
            // Jika gagal membuat pembayaran, update status donasi
            $this->update($donationId, ['status' => 'failed']);
            return false;
        }
        
        // Update data pembayaran
        $this->update($donationId, [
            'payment_data' => json_encode($paymentInfo),
            'payment_channel' => $paymentInfo['payment_type'] ?? null
        ]);
        
        // Gabungkan data donasi dengan info pembayaran
        $donation['payment_info'] = $paymentInfo;
        
        return $donation;
    }
    
    /**
     * Generate ID transaksi unik
     * 
     * @return string
     */
    private function generateTransactionId() {
        $prefix = 'DH';
        $date = date('Ymd');
        $random = mt_rand(1000, 9999);
        $uniqueId = uniqid();
        
        return $prefix . $date . $random . substr($uniqueId, -4);
    }
    
    /**
     * Membuat pembayaran di Midtrans
     * 
     * @param array $donation Data donasi
     * @return array|false Data pembayaran atau false jika gagal
     */
    private function createMidtransPayment($donation) {
        // Load Midtrans Config
        \Midtrans\Config::$serverKey = MIDTRANS_SERVER_KEY;
        \Midtrans\Config::$isProduction = (MIDTRANS_ENVIRONMENT === 'production');
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;
        
        // Ambil data campaign
        $campaign = (new Campaign())->find($donation['campaign_id']);
        
        if (!$campaign) {
            return false;
        }
        
        // Siapkan parameter untuk Midtrans
        $params = [
            'transaction_details' => [
                'order_id' => $donation['transaction_id'],
                'gross_amount' => (int) $donation['amount'],
            ],
            'customer_details' => [
                'first_name' => $donation['name'],
                'email' => $donation['email'],
                'phone' => $donation['phone'] ?? '',
            ],
            'item_details' => [
                [
                    'id' => 'donation-' . $donation['id'],
                    'price' => (int) $donation['amount'],
                    'quantity' => 1,
                    'name' => 'Donasi untuk ' . $campaign['title'],
                ]
            ],
            'callbacks' => [
                'finish' => BASE_URL . '/donation/finish/' . $donation['id'],
                'error' => BASE_URL . '/donation/error/' . $donation['id'],
                'pending' => BASE_URL . '/donation/pending/' . $donation['id']
            ]
        ];
        
        try {
            // Buat Snap Token
            $snapToken = \Midtrans\Snap::getSnapToken($params);
            
            return [
                'token' => $snapToken,
                'redirect_url' => 'https://app.midtrans.com/snap/v2/vtweb/' . $snapToken,
                'payment_type' => 'snap',
            ];
        } catch (\Exception $e) {
            // Log error
            error_log('Midtrans Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update status donasi berdasarkan notifikasi Midtrans
     * 
     * @param array $notification Data notifikasi dari Midtrans
     * @return bool
     */
    public function handleMidtransNotification($notification) {
        // Verifikasi signature key
        \Midtrans\Config::$serverKey = MIDTRANS_SERVER_KEY;
        \Midtrans\Config::$isProduction = (MIDTRANS_ENVIRONMENT === 'production');
        
        try {
            $notif = new \Midtrans\Notification();
        } catch (\Exception $e) {
            return false;
        }
        
        $transactionId = $notif->order_id;
        $transactionStatus = $notif->transaction_status;
        $fraudStatus = $notif->fraud_status;
        $paymentType = $notif->payment_type;
        
        // Cari donasi berdasarkan transaction_id
        $donation = $this->findBy('transaction_id', $transactionId);
        
        if (!$donation) {
            return false;
        }
        
        // Update status berdasarkan status transaksi Midtrans
        $status = 'pending';
        
        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'challenge') {
                $status = 'challenge';
            } else if ($fraudStatus == 'accept') {
                $status = 'success';
            }
        } else if ($transactionStatus == 'settlement') {
            $status = 'success';
        } else if ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
            $status = 'failed';
        } else if ($transactionStatus == 'pending') {
            $status = 'pending';
        }
        
        // Update donasi
        $this->update($donation['id'], [
            'status' => $status,
            'payment_method' => $paymentType,
            'payment_data' => json_encode($notif)
        ]);
        
        // Jika donasi berhasil, update jumlah donasi pada campaign
        if ($status === 'success') {
            $campaign = new Campaign();
            $campaign->updateAmount($donation['campaign_id'], $donation['amount']);
        }
        
        return true;
    }
    
    /**
     * Mendapatkan donasi dengan detail campaign
     * 
     * @param int $id ID donasi
     * @return array|false
     */
    public function getWithDetails($id) {
        return $this->db->fetch(
            "SELECT d.*, c.title as campaign_title, c.slug as campaign_slug 
            FROM {$this->table} d
            LEFT JOIN campaigns c ON d.campaign_id = c.id
            WHERE d.id = ?",
            [$id]
        );
    }
    
    /**
     * Mendapatkan donasi berdasarkan campaign
     * 
     * @param int $campaignId ID campaign
     * @param bool $onlySuccess Hanya donasi yang berhasil
     * @param int $limit Batasan jumlah data
     * @return array
     */
    public function getByCampaign($campaignId, $onlySuccess = true, $limit = null) {
        $sql = "SELECT * FROM {$this->table} WHERE campaign_id = ?";
        $params = [$campaignId];
        
        if ($onlySuccess) {
            $sql .= " AND status = 'success'";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT ?";
            $params[] = $limit;
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Mendapatkan statistik donasi untuk dashboard
     * 
     * @return array
     */
    public function getStats() {
        // Total donasi berhasil (amount)
        $totalAmount = $this->db->fetchColumn(
            "SELECT COALESCE(SUM(amount), 0) FROM {$this->table} WHERE status = 'success'"
        );
        
        // Jumlah transaksi donasi berhasil
        $totalDonations = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->table} WHERE status = 'success'"
        );
        
        // Jumlah donatur unik
        $totalDonors = $this->db->fetchColumn(
            "SELECT COUNT(DISTINCT email) FROM {$this->table} WHERE status = 'success'"
        );
        
        // Rata-rata donasi
        $avgDonation = $totalDonations > 0 ? $totalAmount / $totalDonations : 0;
        
        // Data bulan ini
        $startOfMonth = date('Y-m-01');
        $endOfMonth = date('Y-m-t');
        
        // Total donasi bulan ini
        $thisMonthTotal = $this->db->fetchColumn(
            "SELECT COALESCE(SUM(amount), 0) FROM {$this->table} 
            WHERE status = 'success' AND DATE(created_at) BETWEEN ? AND ?",
            [$startOfMonth, $endOfMonth]
        );
        
        // Donatur baru bulan ini
        $newDonors = $this->db->fetchColumn(
            "SELECT COUNT(DISTINCT email) FROM {$this->table} 
            WHERE status = 'success' AND DATE(created_at) BETWEEN ? AND ?",
            [$startOfMonth, $endOfMonth]
        );
        
        // Data bulan lalu untuk perbandingan
        $startOfLastMonth = date('Y-m-01', strtotime('-1 month'));
        $endOfLastMonth = date('Y-m-t', strtotime('-1 month'));
        
        // Total donasi bulan lalu
        $lastMonthTotal = $this->db->fetchColumn(
            "SELECT COALESCE(SUM(amount), 0) FROM {$this->table} 
            WHERE status = 'success' AND DATE(created_at) BETWEEN ? AND ?",
            [$startOfLastMonth, $endOfLastMonth]
        );
        
        // Donatur bulan lalu
        $lastMonthDonors = $this->db->fetchColumn(
            "SELECT COUNT(DISTINCT email) FROM {$this->table} 
            WHERE status = 'success' AND DATE(created_at) BETWEEN ? AND ?",
            [$startOfLastMonth, $endOfLastMonth]
        );
        
        // Rata-rata donasi bulan lalu
        $lastMonthDonationCount = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->table} 
            WHERE status = 'success' AND DATE(created_at) BETWEEN ? AND ?",
            [$startOfLastMonth, $endOfLastMonth]
        );
        
        $lastMonthAvg = $lastMonthDonationCount > 0 ? 
            $lastMonthTotal / $lastMonthDonationCount : 0;
        
        // Persentase pertumbuhan
        $growthPercentage = $lastMonthTotal > 0 ? 
            (($thisMonthTotal - $lastMonthTotal) / $lastMonthTotal) * 100 : 0;
        
        $donorGrowth = $lastMonthDonors > 0 ? 
            (($newDonors - $lastMonthDonors) / $lastMonthDonors) * 100 : 0;
        
        $avgGrowth = $lastMonthAvg > 0 ? 
            (($avgDonation - $lastMonthAvg) / $lastMonthAvg) * 100 : 0;
        
        // Donasi per metode pembayaran
        $paymentMethods = $this->db->fetchAll(
            "SELECT 
                payment_method, 
                COUNT(*) as count, 
                SUM(amount) as total,
                COUNT(*) * 100.0 / (SELECT COUNT(*) FROM {$this->table} WHERE status = 'success') as percentage
            FROM {$this->table} 
            WHERE status = 'success' 
            GROUP BY payment_method"
        );
        
        // Format payment methods for chart
        $chartPaymentMethods = [];
        $colors = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#5a5c69'];
        
        foreach ($paymentMethods as $index => $method) {
            $chartPaymentMethods[] = [
                'label' => $this->formatPaymentMethodName($method['payment_method']),
                'count' => (int)$method['count'],
                'total' => (float)$method['total'],
                'percentage' => (float)$method['percentage'],
                'color' => $colors[$index % count($colors)]
            ];
        }
        
        // Get monthly donation data for chart
        $monthlyData = $this->getStatistics('monthly');
        
        return [
            'total_amount' => $totalAmount,
            'total_donations' => $totalDonations,
            'growth_percentage' => round($growthPercentage, 1),
            'total_donors' => $totalDonors,
            'new_donors' => $newDonors,
            'donor_growth' => round($donorGrowth, 1),
            'avg_donation' => $avgDonation,
            'avg_growth' => round($avgGrowth, 1),
            'payment_methods' => $chartPaymentMethods,
            'chart_data' => $monthlyData,
            'total_success' => $totalAmount,
            'count_success' => $totalDonations,
            'unique_donors' => $totalDonors,
            'by_payment_method' => $paymentMethods
        ];
    }
    
    /**
     * Format payment method name for display
     * 
     * @param string $method
     * @return string
     */
    private function formatPaymentMethodName($method) {
        $methodNames = [
            'bank_transfer' => 'Transfer Bank',
            'credit_card' => 'Kartu Kredit',
            'ewallet' => 'E-Wallet',
            'qris' => 'QRIS',
            'virtual_account' => 'Virtual Account',
        ];
        
        return $methodNames[$method] ?? ucfirst($method);
    }
    
    /**
     * Mendapatkan semua donasi untuk kampanye tertentu
     * 
     * @param int $campaignId ID kampanye
     * @param string $status Status donasi (opsional)
     * @param string $orderBy Kolom untuk pengurutan
     * @param string $order Arah pengurutan (ASC/DESC)
     * @return array
     */
    public function getAllByCampaignId($campaignId, $status = null, $orderBy = 'created_at', $order = 'DESC') {
        $sql = "SELECT * FROM {$this->table} WHERE campaign_id = ?";
        $params = [$campaignId];
        
        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY {$orderBy} {$order}";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Mendapatkan semua donasi untuk pengguna tertentu
     * 
     * @param int $userId ID pengguna
     * @param string $status Status donasi (opsional)
     * @param string $orderBy Kolom untuk pengurutan
     * @param string $order Arah pengurutan (ASC/DESC)
     * @return array
     */
    public function getAllByUserId($userId, $status = null, $orderBy = 'created_at', $order = 'DESC') {
        $sql = "SELECT d.*, c.title as campaign_title, c.slug as campaign_slug 
                FROM {$this->table} d 
                JOIN campaigns c ON d.campaign_id = c.id
                WHERE d.user_id = ?";
        $params = [$userId];
        
        if ($status) {
            $sql .= " AND d.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY d.{$orderBy} {$order}";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Mendapatkan donasi berdasarkan ID dengan data kampanye
     * 
     * @param int $id ID donasi
     * @return array|false
     */
    public function findWithCampaign($id) {
        $sql = "SELECT d.*, c.title as campaign_title, c.slug as campaign_slug 
                FROM {$this->table} d 
                JOIN campaigns c ON d.campaign_id = c.id
                WHERE d.id = ?";
        
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * Mendapatkan donasi berdasarkan order ID
     * 
     * @param string $orderId Order ID
     * @return array|false
     */
    public function findByOrderId($orderId) {
        return $this->findBy('order_id', $orderId);
    }
    
    /**
     * Mendapatkan semua donasi dengan filter
     * 
     * @param array $filters Array filter
     * @param int $page Halaman saat ini
     * @param int $perPage Jumlah data per halaman
     * @param string $orderBy Kolom untuk pengurutan
     * @param string $order Arah pengurutan (ASC/DESC)
     * @return array
     */
    public function getAll($filters = [], $page = 1, $perPage = 10, $orderBy = 'created_at', $order = 'DESC') {
        $offset = ($page - 1) * $perPage;
        $conditions = [];
        $params = [];
        
        // Building SQL conditions based on filters
        if (!empty($filters)) {
            foreach ($filters as $key => $value) {
                if ($value !== '' && $value !== null) {
                    switch ($key) {
                        case 'campaign_id':
                            $conditions[] = "d.campaign_id = ?";
                            $params[] = $value;
                            break;
                        case 'status':
                            $conditions[] = "d.status = ?";
                            $params[] = $value;
                            break;
                        case 'payment_method':
                            $conditions[] = "d.payment_method = ?";
                            $params[] = $value;
                            break;
                        case 'payment_channel':
                            $conditions[] = "d.payment_channel = ?";
                            $params[] = $value;
                            break;
                        case 'date_from':
                            $conditions[] = "DATE(d.created_at) >= ?";
                            $params[] = $value;
                            break;
                        case 'date_to':
                            $conditions[] = "DATE(d.created_at) <= ?";
                            $params[] = $value;
                            break;
                        case 'amount_min':
                            $conditions[] = "d.amount >= ?";
                            $params[] = $value;
                            break;
                        case 'amount_max':
                            $conditions[] = "d.amount <= ?";
                            $params[] = $value;
                            break;
                        case 'search':
                            $conditions[] = "(d.name LIKE ? OR d.email LIKE ? OR d.transaction_id LIKE ? OR d.order_id LIKE ?)";
                            $searchTerm = '%' . $value . '%';
                            $params[] = $searchTerm;
                            $params[] = $searchTerm;
                            $params[] = $searchTerm;
                            $params[] = $searchTerm;
                            break;
                    }
                }
            }
        }
        
        // Base SQL query
        $sql = "SELECT d.*, c.title as campaign_title, c.slug as campaign_slug 
                FROM {$this->table} d 
                JOIN campaigns c ON d.campaign_id = c.id";
        
        // Add conditions if any
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        // Add ordering
        $sql .= " ORDER BY d.{$orderBy} {$order}";
        
        // Add pagination
        $sql .= " LIMIT {$perPage} OFFSET {$offset}";
        
        // Get data
        $donations = $this->db->fetchAll($sql, $params);
        
        // Count total records for pagination
        $countSql = "SELECT COUNT(*) FROM {$this->table} d JOIN campaigns c ON d.campaign_id = c.id";
        if (!empty($conditions)) {
            $countSql .= " WHERE " . implode(" AND ", $conditions);
        }
        $total = $this->db->fetchColumn($countSql, $params);
        
        // Calculate pagination info
        $lastPage = ceil($total / $perPage);
        
        return [
            'data' => $donations,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => $lastPage
        ];
    }
    
    /**
     * Mendapatkan statistik donasi
     * 
     * @param string $period Periode (daily, monthly, yearly)
     * @param string $dateFrom Tanggal awal
     * @param string $dateTo Tanggal akhir
     * @return array
     */
    public function getStatistics($period = 'monthly', $dateFrom = null, $dateTo = null) {
        // Default date range: last 30 days
        if (!$dateFrom) {
            $dateFrom = date('Y-m-d', strtotime('-30 days'));
        }
        
        if (!$dateTo) {
            $dateTo = date('Y-m-d');
        }
        
        // Format untuk SQL GROUP BY
        $dateFormat = '';
        $labelFormat = '';
        
        switch ($period) {
            case 'daily':
                $dateFormat = '%Y-%m-%d';
                $labelFormat = 'd M';
                break;
            case 'monthly':
                $dateFormat = '%Y-%m';
                $labelFormat = 'M Y';
                break;
            case 'yearly':
                $dateFormat = '%Y';
                $labelFormat = 'Y';
                break;
            default:
                $dateFormat = '%Y-%m-%d';
                $labelFormat = 'd M';
        }
        
        // SQL query untuk donasi sukses berdasarkan periode
        $sql = "SELECT 
                DATE_FORMAT(created_at, '{$dateFormat}') as period,
                COUNT(*) as total_donations,
                SUM(amount) as total_amount,
                COUNT(DISTINCT email) as unique_donors
                FROM {$this->table}
                WHERE status = 'success'
                AND DATE(created_at) BETWEEN ? AND ?
                GROUP BY period
                ORDER BY period";
        
        $statistics = $this->db->fetchAll($sql, [$dateFrom, $dateTo]);
        
        // Format untuk chart.js
        $labels = [];
        $amounts = [];
        $counts = [];
        $donors = [];
        
        foreach ($statistics as $stat) {
            $formattedDate = $stat['period'];
            $labels[] = $formattedDate;
            $amounts[] = (float)$stat['total_amount'];
            $counts[] = (int)$stat['total_donations'];
            $donors[] = (int)$stat['unique_donors'];
        }
        
        // Get payment method statistics
        $paymentMethodSql = "SELECT 
                payment_method,
                COUNT(*) as total_donations,
                SUM(amount) as total_amount
                FROM {$this->table}
                WHERE status = 'success'
                AND DATE(created_at) BETWEEN ? AND ?
                GROUP BY payment_method
                ORDER BY total_amount DESC";
                
        $paymentStats = $this->db->fetchAll($paymentMethodSql, [$dateFrom, $dateTo]);
        
        // Calculate totals for summary
        $totalAmount = array_sum($amounts);
        $totalDonations = array_sum($counts);
        $avgDonation = $totalDonations > 0 ? $totalAmount / $totalDonations : 0;
        
        return [
            'labels' => $labels,
            'amounts' => $amounts,
            'counts' => $counts,
            'donors' => $donors,
            'payment_methods' => $paymentStats,
            'summary' => [
                'total_amount' => $totalAmount,
                'total_donations' => $totalDonations,
                'avg_donation' => $avgDonation
            ]
        ];
    }
    
    /**
     * Mendapatkan semua donasi untuk diekspor
     * 
     * @param array $filters Filter untuk data donasi
     * @return array Data donasi yang telah difilter
     */
    public function getAllForExport($filters) {
        $conditions = [];
        $params = [];
        
        // Building SQL conditions based on filters
        if (!empty($filters)) {
            foreach ($filters as $key => $value) {
                if ($value !== '' && $value !== null) {
                    switch ($key) {
                        case 'campaign_id':
                            $conditions[] = "d.campaign_id = ?";
                            $params[] = $value;
                            break;
                        case 'status':
                            $conditions[] = "d.status = ?";
                            $params[] = $value;
                            break;
                        case 'payment_method':
                            $conditions[] = "d.payment_method = ?";
                            $params[] = $value;
                            break;
                        case 'date_from':
                            $conditions[] = "DATE(d.created_at) >= ?";
                            $params[] = $value;
                            break;
                        case 'date_to':
                            $conditions[] = "DATE(d.created_at) <= ?";
                            $params[] = $value;
                            break;
                        case 'search':
                            $conditions[] = "(d.name LIKE ? OR d.email LIKE ? OR d.transaction_id LIKE ? OR d.order_id LIKE ?)";
                            $searchTerm = '%' . $value . '%';
                            $params[] = $searchTerm;
                            $params[] = $searchTerm;
                            $params[] = $searchTerm;
                            $params[] = $searchTerm;
                            break;
                    }
                }
            }
        }
        
        // Base SQL query with joins for export - include more data
        $sql = "SELECT d.*, c.title as campaign_title, c.slug as campaign_slug,
                u.name as organizer_name
                FROM {$this->table} d 
                LEFT JOIN campaigns c ON d.campaign_id = c.id
                LEFT JOIN users u ON c.user_id = u.id";
        
        // Add conditions if any
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        // Add ordering
        $sql .= " ORDER BY d.created_at DESC";
        
        // Get all data (no pagination for export)
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Export data donasi ke PDF
     * 
     * @param array $filters Filter untuk data
     * @return string Path ke file PDF yang dihasilkan
     */
    public function exportToPdf($filters) {
        // Get filtered donations
        $donations = $this->getAllForExport($filters);
        
        // Check if we have TCPDF library
        if (!file_exists(BASEPATH . '/vendor/tecnickcom/tcpdf/tcpdf.php')) {
            return false;
        }
        
        // Include TCPDF library
        require_once BASEPATH . '/vendor/tecnickcom/tcpdf/tcpdf.php';
        
        // Create new PDF document
        $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator(APP_NAME);
        $pdf->SetAuthor(APP_NAME);
        $pdf->SetTitle('Laporan Donasi');
        $pdf->SetSubject('Laporan Donasi ' . date('Y-m-d'));
        
        // Remove header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Set margins
        $pdf->SetMargins(10, 10, 10);
        
        // Set auto page breaks
        $pdf->SetAutoPageBreak(true, 10);
        
        // Add a page
        $pdf->AddPage();
        
        // Set font
        $pdf->SetFont('helvetica', 'B', 16);
        
        // Title
        $pdf->Cell(0, 10, 'Laporan Donasi ' . APP_NAME, 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, 'Tanggal: ' . date('d/m/Y H:i'), 0, 1, 'C');
        $pdf->Ln(5);
        
        // Create table header
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(220, 220, 220);
        
        // Table headers
        $pdf->Cell(10, 7, 'No', 1, 0, 'C', 1);
        $pdf->Cell(30, 7, 'Tanggal', 1, 0, 'C', 1);
        $pdf->Cell(35, 7, 'Nama Donatur', 1, 0, 'C', 1);
        $pdf->Cell(45, 7, 'Email', 1, 0, 'C', 1);
        $pdf->Cell(15, 7, 'Telp', 1, 0, 'C', 1);
        $pdf->Cell(55, 7, 'Kampanye', 1, 0, 'C', 1);
        $pdf->Cell(25, 7, 'Jumlah', 1, 0, 'C', 1);
        $pdf->Cell(30, 7, 'Metode Pembayaran', 1, 0, 'C', 1);
        $pdf->Cell(20, 7, 'Status', 1, 1, 'C', 1);
        
        // Table rows
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetFillColor(245, 245, 245);
        $fill = 0;
        $no = 0;
        $totalAmount = 0;
        
        foreach ($donations as $donation) {
            $no++;
            
            // Format some values
            $donorName = $donation['is_anonymous'] ? 'Anonim' : $donation['name'];
            $amount = number_format($donation['amount'], 0, ',', '.');
            $date = date('d/m/Y H:i', strtotime($donation['created_at']));
            $phone = !empty($donation['phone']) ? $donation['phone'] : '-';
            
            // Format status
            $status = ucfirst($donation['status']);
            if ($donation['status'] == 'success') {
                $status = 'Berhasil';
                $totalAmount += $donation['amount'];
            } elseif ($donation['status'] == 'pending') {
                $status = 'Menunggu';
            } elseif ($donation['status'] == 'failed') {
                $status = 'Gagal';
            }
            
            // Print row
            $pdf->Cell(10, 6, $no, 1, 0, 'C', $fill);
            $pdf->Cell(30, 6, $date, 1, 0, 'L', $fill);
            $pdf->Cell(35, 6, $donorName, 1, 0, 'L', $fill);
            $pdf->Cell(45, 6, $donation['email'], 1, 0, 'L', $fill);
            $pdf->Cell(15, 6, $phone, 1, 0, 'L', $fill);
            $pdf->Cell(55, 6, $donation['campaign_title'], 1, 0, 'L', $fill);
            $pdf->Cell(25, 6, 'Rp ' . $amount, 1, 0, 'R', $fill);
            $pdf->Cell(30, 6, ucfirst($donation['payment_method']), 1, 0, 'L', $fill);
            $pdf->Cell(20, 6, $status, 1, 1, 'C', $fill);
            
            $fill = !$fill;
        }
        
        // Summary
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 7, 'Ringkasan', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(100, 6, 'Total Donasi: ' . count($donations), 0, 0, 'L');
        $pdf->Cell(100, 6, 'Total Terkumpul: Rp ' . number_format($totalAmount, 0, ',', '.'), 0, 1, 'L');
        
        // Create directory for downloads if it doesn't exist
        $downloadDir = PUBLIC_PATH . '/downloads';
        if (!file_exists($downloadDir)) {
            mkdir($downloadDir, 0755, true);
        }
        
        // Generate unique filename
        $filename = 'laporan_donasi_' . date('YmdHis') . '.pdf';
        $filepath = $downloadDir . '/' . $filename;
        
        // Save PDF to file
        $pdf->Output($filepath, 'F');
        
        // Return URL to access the file
        return BASE_URL . '/downloads/' . $filename;
    }
    
    /**
     * Mengupdate status donasi yang kedaluwarsa
     * 
     * @return int Jumlah donasi yang diupdate
     */
    public function updateExpiredDonations() {
        $now = date('Y-m-d H:i:s');
        
        $sql = "UPDATE {$this->table} 
                SET status = 'expired' 
                WHERE status = 'pending' 
                AND payment_expiry IS NOT NULL 
                AND payment_expiry < ?";
        
        $stmt = $this->db->query($sql, [$now]);
        
        return $stmt->rowCount();
    }
    
    /**
     * Mendapatkan donasi terbaru berdasarkan campaign ID
     * 
     * @param int $campaignId ID campaign
     * @param int $limit Batasan jumlah data
     * @return array
     */
    public function getRecentByCampaignId($campaignId, $limit = 5) {
        return $this->db->fetchAll(
            "SELECT d.*, d.name as donor_name, 
            IF(d.is_anonymous = 1, 'Anonim', d.name) as display_name
            FROM {$this->table} d
            WHERE d.campaign_id = ? AND d.status = 'success'
            ORDER BY d.created_at DESC
            LIMIT ?",
            [$campaignId, $limit]
        );
    }

    /**
     * Get all donations with campaign information for reports
     * 
     * @return array
     */
    public function getAllWithDetails() {
        return $this->db->fetchAll(
            "SELECT d.*, 
                c.title as campaign_title, 
                c.slug as campaign_slug,
                cat.name as category_name,
                u.name as creator_name
            FROM {$this->table} d
            LEFT JOIN campaigns c ON d.campaign_id = c.id
            LEFT JOIN categories cat ON c.category_id = cat.id
            LEFT JOIN users u ON c.user_id = u.id
            ORDER BY d.created_at DESC"
        );
    }
} 