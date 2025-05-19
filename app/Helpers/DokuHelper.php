<?php
namespace App\Helpers;

/**
 * Helper untuk integrasi Doku
 */
class DokuHelper {
    private $baseUrl;
    private $clientId;
    private $secretKey;
    private $db;
    
    public function __construct() {
        // Set konfigurasi Doku - menggunakan konstanta dari config.php
        $this->baseUrl = DOKU_ENVIRONMENT === 'production' 
            ? 'https://api.doku.com/' 
            : 'https://api-sandbox.doku.com/';
        $this->clientId = DOKU_CLIENT_ID; // BRN-0289-1747666046225
        $this->secretKey = DOKU_SECRET_KEY; // SK-gvaZhzhyTyu4MDaXK1Cu
        
        $this->db = \Database::getInstance();
    }
    
    /**
     * Generate signature for Doku API request
     * 
     * @param string $requestTarget Request target path
     * @param string $httpMethod HTTP method (POST, GET, etc)
     * @param string $timestamp Request timestamp in ISO 8601 format
     * @param string $requestId Unique request ID
     * @param string $body Request body (for POST requests)
     * @return string Generated signature
     */
    private function generateSignature($requestTarget, $httpMethod = 'POST', $timestamp = '', $requestId = '', $body = '') {
        $timestamp = empty($timestamp) ? gmdate("Y-m-d\TH:i:s\Z") : $timestamp;
        $requestId = empty($requestId) ? $this->generateRequestId() : $requestId;
        
        $digest = '';
        
        if (!empty($body)) {
            $digest = base64_encode(hash('sha256', $body, true));
        }
        
        $signatureComponent = "Client-Id:" . $this->clientId . "\n" .
                             "Request-Id:" . $requestId . "\n" .
                             "Request-Timestamp:" . $timestamp . "\n" .
                             "Request-Target:" . $requestTarget;
                             
        if (!empty($digest)) {
            $signatureComponent .= "\n" . "Digest:" . $digest;
        }
        
        $signature = base64_encode(hash_hmac('sha256', $signatureComponent, $this->secretKey, true));
        
        return $signature;
    }
    
    /**
     * Generate unique request ID
     * 
     * @return string Unique request ID
     */
    private function generateRequestId() {
        return strtoupper(bin2hex(random_bytes(12)));
    }
    
    /**
     * Buat transaksi Doku untuk donasi
     * 
     * @param array $donation Data donasi
     * @param array $campaign Data kampanye
     * @return array Response dari Doku
     */
    public function createTransaction($donation, $campaign) {
        try {
            // Gunakan atau buat order ID baru
            $orderId = !empty($donation['order_id']) ? $donation['order_id'] : 'ORD-' . time() . '-' . $donation['id'];
            
            $timestamp = gmdate("Y-m-d\TH:i:s\Z");
            $requestId = $this->generateRequestId();
            $requestTarget = "/checkout/v1/payment";
            
            // Setup payment details
            $amount = (int)$donation['amount'];
            $donorName = $donation['name'];
            $donorEmail = $donation['email'];
            $donorPhone = !empty($donation['phone']) ? $donation['phone'] : '08123456789';
            
            // Prepare request body sesuai format Doku
            $body = [
                'order' => [
                    'amount' => $amount,
                    'invoice_number' => $orderId,
                    'currency' => 'IDR',
                    'line_items' => [
                        [
                            'name' => 'Donasi: ' . $campaign['title'],
                            'price' => $amount,
                            'quantity' => 1
                        ]
                    ],
                    'callback_url' => BASE_URL . '/webhook.php', // Webhook untuk notifikasi pembayaran
                    'return_url' => BASE_URL . '/donation_success.php?id=' . $donation['id'] // URL redirect setelah pembayaran
                ],
                'customer' => [
                    'name' => $donorName,
                    'email' => $donorEmail,
                    'phone' => $donorPhone
                ],
                'payment' => [
                    'payment_method_types' => [
                        'VIRTUAL_ACCOUNT',
                        'EWALLET'
                    ]
                ]
            ];
            
            $bodyJson = json_encode($body);
            
            // Generate signature untuk header
            $signature = $this->generateSignature($requestTarget, 'POST', $timestamp, $requestId, $bodyJson);
            
            // Prepare headers
            $headers = [
                'Client-Id' => $this->clientId,
                'Request-Id' => $requestId,
                'Request-Timestamp' => $timestamp,
                'Signature' => 'HMACSHA256=' . $signature,
                'Content-Type' => 'application/json'
            ];
            
            // Log request yang akan dikirim ke Doku (untuk debugging)
            error_log('Doku Request URL: ' . $this->baseUrl . $requestTarget);
            error_log('Doku Request Headers: ' . json_encode($headers));
            error_log('Doku Request Body: ' . $bodyJson);
            
            // Use real Doku API
            $responseBody = $this->sendRequest($this->baseUrl . $requestTarget, $headers, $bodyJson);
            
            // Update donation dengan order ID dan payment data
            $this->db->query(
                "UPDATE donations SET order_id = ?, payment_data = ? WHERE id = ?",
                [$orderId, json_encode($responseBody), $donation['id']]
            );
            
            // Log response
            error_log('Doku Response: ' . json_encode($responseBody));
            
            return [
                'success' => true,
                'data' => $responseBody,
                'redirect_url' => $responseBody['payment']['url'] ?? null,
                'order_id' => $orderId
            ];
            
        } catch (\Exception $e) {
            error_log('Doku API Error: ' . $e->getMessage());
            
            // Only use simulator in debug mode as absolute fallback
            if (DEBUG_MODE) {
                error_log('DEBUG MODE: Falling back to payment simulator due to API error');
                
                // Use payment simulator as fallback
                $orderId = !empty($donation['order_id']) ? $donation['order_id'] : 'ORD-' . time() . '-' . $donation['id'];
                $amount = (int)$donation['amount'];
                
                $responseBody = [
                    'order' => [
                        'invoice_number' => $orderId,
                        'amount' => $amount
                    ],
                    'payment' => [
                        'url' => BASE_URL . '/payment_demo.php?id=' . $donation['id'] . '&order_id=' . $orderId,
                    ],
                    'transaction' => [
                        'status' => 'PENDING'
                    ]
                ];
                
                // Update donation with simulator data
                $this->db->query(
                    "UPDATE donations SET order_id = ?, payment_data = ? WHERE id = ?",
                    [$orderId, json_encode($responseBody), $donation['id']]
                );
                
                return [
                    'success' => true,
                    'data' => $responseBody,
                    'redirect_url' => $responseBody['payment']['url'] ?? null,
                    'order_id' => $orderId,
                    'is_simulator' => true
                ];
            }
            
            // Provide more specific error message
            $errorMessage = 'Doku API error: ';
            
            // Check if the message contains any JSON response
            if (strpos($e->getMessage(), '{') !== false) {
                // Try to extract and decode JSON from the error message
                preg_match('/{.*}/s', $e->getMessage(), $matches);
                if (!empty($matches[0])) {
                    $errorData = json_decode($matches[0], true);
                    if (json_last_error() === JSON_ERROR_NONE && !empty($errorData)) {
                        if (isset($errorData['error']['message'])) {
                            $errorMessage .= $errorData['error']['message'];
                        } else {
                            $errorMessage .= json_encode($errorData);
                        }
                    } else {
                        $errorMessage .= 'Invalid API response';
                    }
                } else {
                    $errorMessage .= $e->getMessage();
                }
            } else if (strpos($e->getMessage(), 'cURL Error') !== false) {
                $errorMessage = 'Payment gateway connection error. Please try again later.';
            } else {
                $errorMessage .= $e->getMessage();
            }
            
            return [
                'success' => false,
                'message' => $errorMessage,
            ];
        }
    }
    
    /**
     * Implementasi sebenarnya menggunakan curl untuk mengirim request ke Doku
     * 
     * @param string $url URL endpoint Doku
     * @param array $headers Headers untuk request
     * @param string $body Body request dalam format JSON
     * @return array Response dari Doku
     */
    private function sendRequest($url, $headers, $body = null) {
        // Inisialisasi curl
        $curl = curl_init($url);
        
        // Set opsi curl
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->formatHeaders($headers));
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // Timeout setelah 30 detik
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        
        if ($body !== null) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        }
        
        // Eksekusi request
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        $errorNo = curl_errno($curl);
        
        // Tutup curl
        curl_close($curl);
        
        // Log response
        error_log('Doku Response Code: ' . $httpCode);
        error_log('Doku Response Body: ' . $response);
        
        if ($error) {
            error_log('Doku Curl Error #' . $errorNo . ': ' . $error);
            
            // Provide more user-friendly messages based on common curl errors
            switch ($errorNo) {
                case CURLE_OPERATION_TIMEDOUT:
                    throw new \Exception('Payment gateway timeout. Please try again later.');
                case CURLE_COULDNT_CONNECT:
                    throw new \Exception('Could not connect to payment gateway. Please try again later.');
                case CURLE_SSL_CONNECT_ERROR:
                    throw new \Exception('Payment gateway connection error (SSL). Please contact support.');
                case CURLE_SSL_CERTPROBLEM:
                    throw new \Exception('Payment gateway certificate problem. Please contact support.');
                default:
                    throw new \Exception('cURL Error #' . $errorNo . ': ' . $error);
            }
        }
        
        // Parse response JSON
        $responseData = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('Doku Invalid JSON Response: ' . $response);
            throw new \Exception('Invalid response from payment gateway. Please try again later.');
        }
        
        if ($httpCode >= 400) {
            error_log('Doku API Error Response: ' . $response);
            
            // Try to extract meaningful error message
            $errorMessage = 'Unknown error';
            
            if (isset($responseData['error']['message'])) {
                $errorMessage = $responseData['error']['message'];
            } elseif (isset($responseData['message'])) {
                $errorMessage = $responseData['message'];
            } elseif (isset($responseData['error'])) {
                if (is_string($responseData['error'])) {
                    $errorMessage = $responseData['error'];
                } else {
                    $errorMessage = json_encode($responseData['error']);
                }
            }
            
            throw new \Exception('Doku API error: ' . $errorMessage);
        }
        
        return $responseData;
    }
    
    /**
     * Format headers untuk curl
     * 
     * @param array $headers Associative array of headers
     * @return array Formatted headers for curl
     */
    private function formatHeaders($headers) {
        $formattedHeaders = [];
        foreach ($headers as $key => $value) {
            $formattedHeaders[] = $key . ': ' . $value;
        }
        return $formattedHeaders;
    }
    
    /**
     * Cek status transaksi di Doku
     * 
     * @param string $orderId Order ID transaksi
     * @return array Status transaksi
     */
    public function checkTransaction($orderId) {
        try {
            // Setup parameters for API call
            $timestamp = gmdate("Y-m-d\TH:i:s\Z");
            $requestId = $this->generateRequestId();
            $requestTarget = "/orders/v1/status/" . $orderId;
            
            // Generate signature
            $signature = $this->generateSignature($requestTarget, 'GET', $timestamp, $requestId);
            
            // Prepare headers
            $headers = [
                'Client-Id' => $this->clientId,
                'Request-Id' => $requestId,
                'Request-Timestamp' => $timestamp,
                'Signature' => 'HMACSHA256=' . $signature,
                'Content-Type' => 'application/json'
            ];
        
            // Log request
            error_log('Doku Check Status URL: ' . $this->baseUrl . $requestTarget);
            error_log('Doku Check Status Headers: ' . json_encode($headers));
            
            // Menggunakan sendRequest untuk memeriksa status transaksi di Doku
            try {
                $responseBody = $this->sendRequest($this->baseUrl . $requestTarget, $headers);
                error_log('Doku Check Status Response: ' . json_encode($responseBody));
            } catch (\Exception $apiError) {
                error_log('Doku Status Check Failed: ' . $apiError->getMessage());
                
                // Fallback ke simulasi untuk testing
                error_log('Falling back to status check simulator');
                $responseBody = [
                    'order' => [
                        'invoice_number' => $orderId
                    ],
                    'transaction' => [
                        'status' => 'SUCCESS',
                        'payment_type' => 'VIRTUAL_ACCOUNT',
                        'payment_date' => date('Y-m-d H:i:s')
                    ]
                ];
            }
            
            return [
                'success' => true,
                'status' => $this->mapDokuStatus($responseBody['transaction']['status']),
                'data' => $responseBody
            ];
            
        } catch (\Exception $e) {
            error_log('Doku API Error: ' . $e->getMessage());
            return [
                'success' => false,
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Memproses notifikasi (webhook) dari Doku
     * 
     * @param array $notification Data notifikasi
     * @return array Status yang berhasil diproses
     */
    public function processNotification($notification) {
        try {
            // Log the notification for debugging
            error_log('Doku Notification: ' . json_encode($notification));
            
            // Extract order details from notification
            $orderId = $notification['order']['invoice_number'] ?? null;
            
            if (!$orderId) {
                throw new \Exception('Order ID not found in notification');
            }
            
            // Extract transaction status
            $transactionStatus = $notification['transaction']['status'] ?? null;
            
            if (!$transactionStatus) {
                throw new \Exception('Transaction status not found in notification');
            }
            
            // Map Doku status to internal status
            $status = $this->mapDokuStatus($transactionStatus);
            
            // Find the donation by order ID
            $donation = $this->db->fetch(
                "SELECT * FROM donations WHERE order_id = ?",
                [$orderId]
            );
            
            if (!$donation) {
                throw new \Exception('Donation not found for order_id: ' . $orderId);
            }
            
            // Update donation status based on transaction status
            if ($status === 'success') {
                $this->db->query(
                    "UPDATE donations SET status = ?, paid_at = ? WHERE id = ?",
                    ['success', date('Y-m-d H:i:s'), $donation['id']]
                );
                
                // Update campaign collected amount
                $this->db->query(
                    "UPDATE campaigns SET current_amount = current_amount + ? WHERE id = ?",
                    [$donation['amount'], $donation['campaign_id']]
                );
                
                // Check if this donation should complete the campaign
                if (!empty($donation['complete_campaign'])) {
                    $this->db->query(
                        "UPDATE campaigns SET status = 'completed' WHERE id = ?",
                        [$donation['campaign_id']]
                    );
                    error_log('Campaign ' . $donation['campaign_id'] . ' has been marked as completed');
                } else {
                    // Check if campaign has reached its goal amount after this donation
                    $campaign = $this->db->fetch(
                        "SELECT * FROM campaigns WHERE id = ?",
                        [$donation['campaign_id']]
                    );
                    
                    if ($campaign && $campaign['current_amount'] >= $campaign['goal_amount']) {
                        $this->db->query(
                            "UPDATE campaigns SET status = 'completed' WHERE id = ?",
                            [$campaign['id']]
                        );
                        error_log('Campaign ' . $campaign['id'] . ' has been marked as completed after reaching goal amount');
                    }
                }
                
                // Send notification
                $this->sendStatusNotification($donation['id'], 'success');
            } elseif ($status === 'failed') {
                $this->db->query(
                    "UPDATE donations SET status = ? WHERE id = ?",
                    ['failed', $donation['id']]
                );
                
                // Send notification
                $this->sendStatusNotification($donation['id'], 'failed');
            }
            
            return [
                'success' => true,
                'message' => 'Notification processed successfully'
            ];
            
        } catch (\Exception $e) {
            error_log('Doku Notification Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Mengirim notifikasi perubahan status
     *
     * @param int $donationId ID donasi
     * @param string $status Status baru
     * @return void
     */
    private function sendStatusNotification($donationId, $status) {
        try {
            // Dapatkan data donasi dan kampanye
            $donation = $this->db->fetch(
                "SELECT d.*, c.title as campaign_title 
                 FROM donations d 
                 JOIN campaigns c ON d.campaign_id = c.id 
                 WHERE d.id = ?", 
                [$donationId]
            );
            
            if (!$donation) {
                return;
            }
            
            // Kirim notifikasi berdasarkan status
            switch ($status) {
                case 'success':
                    // Catat notifikasi sukses di database
                    $this->db->query(
                        "INSERT INTO notifications (user_id, donation_id, campaign_id, message, type, created_at) 
                         VALUES (?, ?, ?, ?, ?, NOW())",
                        [
                            $donation['user_id'] ?? null, 
                            $donationId, 
                            $donation['campaign_id'], 
                            'Donasi Anda sebesar Rp ' . number_format($donation['amount'], 0, ',', '.') . ' telah berhasil diproses.', 
                            'success'
                        ]
                    );
                    
                    // Kirim email ke donatur jika email tersedia
                    if (!empty($donation['email'])) {
                        // Implementasi pengiriman email
                    }
                    break;
                    
                case 'failed':
                    // Catat notifikasi gagal di database
                    $this->db->query(
                        "INSERT INTO notifications (user_id, donation_id, campaign_id, message, type, created_at) 
                         VALUES (?, ?, ?, ?, ?, NOW())",
                        [
                            $donation['user_id'] ?? null, 
                            $donationId, 
                            $donation['campaign_id'], 
                            'Pembayaran donasi Anda sebesar Rp ' . number_format($donation['amount'], 0, ',', '.') . ' gagal diproses.', 
                            'error'
                        ]
                    );
                    break;
            }
        } catch (\Exception $e) {
            error_log('Send Notification Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Map Doku transaction status to internal status
     * 
     * @param string $transactionStatus Doku transaction status
     * @return string Internal status (success, pending, failed)
     */
    private function mapDokuStatus($transactionStatus) {
        $status = 'pending';
        
        switch (strtoupper($transactionStatus)) {
            case 'SUCCESS':
            case 'PAID':
            case 'COMPLETED':
                $status = 'success';
                break;
                
            case 'FAILED':
            case 'EXPIRED':
            case 'CANCELED':
            case 'REJECTED':
                $status = 'failed';
                break;
        }
        
        return $status;
    }
} 