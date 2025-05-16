<?php
namespace App\Helpers;

/**
 * Helper untuk integrasi Midtrans
 */
class MidtransHelper {
    private $snap;
    private $apiClient;
    private $db;
    
    public function __construct() {
        require_once BASEPATH . '/vendor/autoload.php';
        
        // Set konfigurasi Midtrans
        \Midtrans\Config::$serverKey = MIDTRANS_SERVER_KEY;
        \Midtrans\Config::$clientKey = MIDTRANS_CLIENT_KEY;
        \Midtrans\Config::$isProduction = MIDTRANS_ENVIRONMENT === 'production';
        \Midtrans\Config::$is3ds = true;
        \Midtrans\Config::$isSanitized = true;
        
        $this->db = \Database::getInstance();
    }
    
    /**
     * Buat transaksi Midtrans untuk donasi
     * 
     * @param array $donation Data donasi
     * @param array $campaign Data kampanye
     * @return array Response dari Midtrans
     */
    public function createTransaction($donation, $campaign) {
        // Buat order ID unik
        $orderId = 'DON-' . time() . '-' . $donation['id'];
        
        // Update order ID di database
        $this->db->query(
            "UPDATE donations SET order_id = ? WHERE id = ?",
            [$orderId, $donation['id']]
        );
        
        // Item details untuk Midtrans
        $itemDetails = [
            [
                'id' => 'DON-' . $campaign['id'],
                'price' => $donation['amount'],
                'quantity' => 1,
                'name' => 'Donasi untuk ' . $campaign['title'],
            ]
        ];
        
        // Data pelanggan
        $customerDetails = [
            'first_name' => $donation['name'],
            'email' => $donation['email'],
            'phone' => $donation['phone'] ?? '',
        ];
        
        // Pengaturan transaksi
        $transactionDetails = [
            'order_id' => $orderId,
            'gross_amount' => $donation['amount'],
        ];
        
        // Callback URL
        $callbackUrl = [
            'finish' => BASE_URL . '/donation/finish/' . $donation['id'],
            'unfinish' => BASE_URL . '/donation/unfinish/' . $donation['id'],
            'error' => BASE_URL . '/donation/error/' . $donation['id'],
        ];
        
        // Set expiry time (24 jam)
        $expiryTime = new \DateTime();
        $expiryTime->add(new \DateInterval('P1D'));
        
        $expiry = [
            'start_time' => date('Y-m-d H:i:s O'),
            'unit' => 'day',
            'duration' => 1,
        ];
        
        // Data transaksi Midtrans
        $transactionData = [
            'transaction_details' => $transactionDetails,
            'item_details' => $itemDetails,
            'customer_details' => $customerDetails,
            'callbacks' => $callbackUrl,
            'expiry' => $expiry,
        ];
        
        try {
            // Buat transaksi Snap
            $snapToken = \Midtrans\Snap::getSnapToken($transactionData);
            $snapUrl = \Midtrans\Snap::getSnapUrl($transactionData);
            
            // Update token dan URL di database
            $this->db->query(
                "UPDATE donations SET payment_url = ?, payment_expiry = ? WHERE id = ?",
                [$snapUrl, $expiryTime->format('Y-m-d H:i:s'), $donation['id']]
            );
            
            return [
                'success' => true,
                'token' => $snapToken,
                'redirect_url' => $snapUrl,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Handle notifikasi dari Midtrans
     * 
     * @param array $notificationData Data notifikasi dari Midtrans
     * @return array Status pemrosesan notifikasi
     */
    public function handleNotification($notificationData) {
        // Log notifikasi untuk keperluan debugging
        $payload = json_encode($notificationData);
        $orderId = $notificationData['order_id'] ?? null;
        $transactionId = $notificationData['transaction_id'] ?? null;
        $paymentType = $notificationData['payment_type'] ?? null;
        $status = $notificationData['transaction_status'] ?? null;
        
        $this->db->query(
            "INSERT INTO payment_logs (order_id, transaction_id, payment_type, payload, status) VALUES (?, ?, ?, ?, ?)",
            [$orderId, $transactionId, $paymentType, $payload, $status]
        );
        
        if (!$orderId) {
            return [
                'success' => false,
                'message' => 'Order ID tidak ditemukan',
            ];
        }
        
        // Cari donasi berdasarkan order ID
        $donation = $this->db->fetch(
            "SELECT * FROM donations WHERE order_id = ?",
            [$orderId]
        );
        
        if (!$donation) {
            return [
                'success' => false,
                'message' => 'Donasi tidak ditemukan',
            ];
        }
        
        // Update log dengan ID donasi
        $this->db->query(
            "UPDATE payment_logs SET donation_id = ? WHERE order_id = ? ORDER BY id DESC LIMIT 1",
            [$donation['id'], $orderId]
        );
        
        // Proses status transaksi
        switch ($status) {
            case 'capture':
            case 'settlement':
                // Donasi berhasil, update status
                $this->db->query(
                    "UPDATE donations SET 
                    status = 'success', 
                    transaction_id = ?, 
                    payment_method = ?, 
                    payment_channel = ?, 
                    va_number = ?, 
                    paid_at = NOW(), 
                    settlement_time = ? 
                    WHERE id = ?",
                    [
                        $transactionId,
                        $paymentType,
                        $notificationData['va_numbers'][0]['bank'] ?? $notificationData['payment_type'],
                        $notificationData['va_numbers'][0]['va_number'] ?? null,
                        date('Y-m-d H:i:s'),
                        $donation['id'],
                    ]
                );
                
                // Update jumlah terkumpul pada kampanye
                $this->db->query(
                    "UPDATE campaigns SET 
                    current_amount = current_amount + ? 
                    WHERE id = ?",
                    [$donation['amount'], $donation['campaign_id']]
                );
                
                // Ambil data kampanye untuk notifikasi
                $campaign = $this->db->fetch(
                    "SELECT * FROM campaigns WHERE id = ?",
                    [$donation['campaign_id']]
                );
                
                // Kirim notifikasi email dan WhatsApp ke donatur
                if ($campaign) {
                    $notifHelper = new \App\Helpers\NotificationHelper();
                    $notifHelper->sendEmailNotification($donation, $campaign);
                    
                    if (!empty($donation['phone'])) {
                        $notifHelper->sendWhatsAppNotification($donation, $campaign);
                    }
                }
                
                return [
                    'success' => true,
                    'message' => 'Donasi berhasil',
                ];
                break;

            case 'pending':
                // Donasi masih pending, update info VA
                $paymentChannel = null;
                $vaNumber = null;
                
                if (isset($notificationData['va_numbers'])) {
                    $paymentChannel = $notificationData['va_numbers'][0]['bank'] ?? null;
                    $vaNumber = $notificationData['va_numbers'][0]['va_number'] ?? null;
                } elseif (isset($notificationData['permata_va_number'])) {
                    $paymentChannel = 'permata';
                    $vaNumber = $notificationData['permata_va_number'];
                }
                
                $this->db->query(
                    "UPDATE donations SET 
                    payment_method = ?, 
                    payment_channel = ?, 
                    va_number = ?, 
                    transaction_id = ? 
                    WHERE id = ?",
                    [
                        $paymentType,
                        $paymentChannel,
                        $vaNumber,
                        $transactionId,
                        $donation['id'],
                    ]
                );
                
                return [
                    'success' => true,
                    'message' => 'Status donasi diperbarui: pending',
                ];
                break;

            case 'deny':
            case 'cancel':
            case 'failure':
                // Donasi gagal atau dibatalkan
                $status = ($status === 'cancel') ? 'canceled' : 'failed';
                
                $this->db->query(
                    "UPDATE donations SET status = ? WHERE id = ?",
                    [$status, $donation['id']]
                );
                
                return [
                    'success' => true,
                    'message' => 'Status donasi diperbarui: ' . $status,
                ];
                break;

            case 'expire':
                // Donasi kedaluwarsa
                $this->db->query(
                    "UPDATE donations SET status = 'expired' WHERE id = ?",
                    [$donation['id']]
                );
                
                return [
                    'success' => true,
                    'message' => 'Status donasi diperbarui: expired',
                ];
                break;

            case 'refund':
                // Donasi dikembalikan
                $this->db->query(
                    "UPDATE donations SET status = 'refunded', refund_time = NOW() WHERE id = ?",
                    [$donation['id']]
                );
                
                // Kurangi jumlah terkumpul pada kampanye
                $this->db->query(
                    "UPDATE campaigns SET 
                    current_amount = current_amount - ? 
                    WHERE id = ?",
                    [$donation['amount'], $donation['campaign_id']]
                );
                
                return [
                    'success' => true,
                    'message' => 'Status donasi diperbarui: refunded',
                ];
                break;

            default:
                return [
                    'success' => false,
                    'message' => 'Status transaksi tidak dikenali: ' . $status,
                ];
        }
    }
    
    /**
     * Cek status transaksi di Midtrans
     * 
     * @param string $orderId Order ID transaksi
     * @return array Status transaksi
     */
    public function checkTransaction($orderId) {
        try {
            $response = \Midtrans\Transaction::status($orderId);
            
            return [
                'success' => true,
                'data' => $response,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Refund transaksi di Midtrans
     * 
     * @param array $donation Data donasi
     * @param string $reason Alasan refund
     * @return array Status refund
     */
    public function refundTransaction($donation, $reason = 'Refund requested by admin') {
        try {
            $params = [
                'refund_key' => 'refund-' . time(),
                'amount' => $donation['amount'],
                'reason' => $reason,
            ];
            
            $response = \Midtrans\Transaction::refund($donation['order_id'], $params);
            
            if ($response && isset($response->status_code) && $response->status_code == 200) {
                // Update status donasi
                $this->db->query(
                    "UPDATE donations SET status = 'refunded', refund_time = NOW() WHERE id = ?",
                    [$donation['id']]
                );
                
                // Kurangi jumlah terkumpul pada kampanye
                $this->db->query(
                    "UPDATE campaigns SET 
                    current_amount = current_amount - ? 
                    WHERE id = ?",
                    [$donation['amount'], $donation['campaign_id']]
                );
                
                return [
                    'success' => true,
                    'message' => 'Refund berhasil',
                    'data' => $response,
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Refund gagal',
                    'data' => $response,
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Batalkan transaksi di Midtrans
     * 
     * @param array $donation Data donasi
     * @return array Status pembatalan
     */
    public function cancelTransaction($donation) {
        try {
            $response = \Midtrans\Transaction::cancel($donation['order_id']);
            
            if ($response && isset($response->status_code) && $response->status_code == 200) {
                // Update status donasi
                $this->db->query(
                    "UPDATE donations SET status = 'canceled' WHERE id = ?",
                    [$donation['id']]
                );
                
                return [
                    'success' => true,
                    'message' => 'Pembatalan berhasil',
                    'data' => $response,
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Pembatalan gagal',
                    'data' => $response,
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
} 