<?php
namespace App\Helpers;

/**
 * Helper untuk mengirim notifikasi
 */
class NotificationHelper {
    private $db;
    
    public function __construct() {
        $this->db = \Database::getInstance();
    }
    
    /**
     * Kirim notifikasi email ke donatur
     * 
     * @param array $donation Data donasi
     * @param array $campaign Data kampanye
     * @return bool
     */
    public function sendEmailNotification($donation, $campaign) {
        // Pastikan donasi memiliki email
        if (empty($donation['email'])) {
            return false;
        }
        
        // Siapkan data untuk template email
        $data = [
            'donation' => $donation,
            'campaign' => $campaign,
            'app_name' => APP_NAME,
            'date' => date('d M Y H:i', strtotime($donation['created_at'])),
            'amount' => 'Rp ' . number_format($donation['amount'], 0, ',', '.'),
            'payment_status' => $this->getStatusLabel($donation['status']),
            'payment_method' => $this->formatPaymentMethod($donation['payment_method'], $donation['payment_channel'] ?? null)
        ];
        
        // Tentukan subject email berdasarkan status donasi
        $subject = '';
        if ($donation['status'] === 'success') {
            $subject = 'Terima Kasih atas Donasi Anda - ' . APP_NAME;
        } elseif ($donation['status'] === 'pending') {
            $subject = 'Menunggu Pembayaran Donasi - ' . APP_NAME;
        } else {
            $subject = 'Update Status Donasi - ' . APP_NAME;
        }
        
        // Mendapatkan template email yang sesuai
        $template = $this->getEmailTemplate($donation['status']);
        
        // Ganti placeholder dengan data
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $template = str_replace('{{' . $key . '}}', $value, $template);
            }
        }
        
        // Mengirim email menggunakan PHPMailer
        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            
            // Konfigurasi server
            $mail->isSMTP();
            $mail->Host = MAIL_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = MAIL_USERNAME;
            $mail->Password = MAIL_PASSWORD;
            $mail->SMTPSecure = MAIL_ENCRYPTION;
            $mail->Port = MAIL_PORT;
            
            // Alamat
            $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
            $mail->addAddress($donation['email'], $donation['name']);
            
            // Konten
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $template;
            $mail->AltBody = strip_tags($template);
            
            $mail->send();
            
            // Update database bahwa notifikasi email telah dikirim
            $this->db->query(
                "UPDATE donations SET notify_success = 1 WHERE id = ?",
                [$donation['id']]
            );
            
            return true;
        } catch (\Exception $e) {
            // Log error
            error_log('Email notification error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Kirim notifikasi WhatsApp ke donatur
     * 
     * @param array $donation Data donasi
     * @param array $campaign Data kampanye
     * @return bool
     */
    public function sendWhatsAppNotification($donation, $campaign) {
        // Pastikan donasi memiliki nomor telepon
        if (empty($donation['phone'])) {
            return false;
        }
        
        // Format nomor telepon
        $phone = $this->formatPhoneNumber($donation['phone']);
        
        // Siapkan pesan WhatsApp
        $message = $this->getWhatsAppTemplate($donation, $campaign);
        
        // Kirim WhatsApp menggunakan layanan pihak ketiga (contoh: Twilio/Chat API)
        // Untuk implementasi sebenarnya, gunakan API WhatsApp Business atau integrasi 3rd party
        
        // Contoh pseudocode untuk integrasi:
        /*
        $client = new WhatsAppClient(WHATSAPP_API_KEY);
        $result = $client->sendMessage([
            'to' => $phone,
            'type' => 'text',
            'text' => [
                'body' => $message
            ]
        ]);
        
        if ($result->success) {
            return true;
        }
        */
        
        // Untuk saat ini, kita hanya log bahwa notifikasi seharusnya dikirim
        error_log("WhatsApp notification would be sent to: {$phone} with message: {$message}");
        
        return true;
    }
    
    /**
     * Format nomor telepon untuk WhatsApp
     * 
     * @param string $phone Nomor telepon
     * @return string
     */
    private function formatPhoneNumber($phone) {
        // Hapus semua karakter non-digit
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Jika diawali dengan 0, ganti dengan 62 (kode negara Indonesia)
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }
        
        // Jika belum ada kode negara, tambahkan 62
        if (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }
        
        return $phone;
    }
    
    /**
     * Mendapatkan template pesan WhatsApp
     * 
     * @param array $donation Data donasi
     * @param array $campaign Data kampanye
     * @return string
     */
    private function getWhatsAppTemplate($donation, $campaign) {
        $statusLabel = $this->getStatusLabel($donation['status']);
        $amount = 'Rp ' . number_format($donation['amount'], 0, ',', '.');
        $paymentMethod = $this->formatPaymentMethod($donation['payment_method'], $donation['payment_channel'] ?? null);
        
        if ($donation['status'] === 'success') {
            return "Terima kasih telah berdonasi di " . APP_NAME . "!\n\n" .
                   "Kampanye: {$campaign['title']}\n" .
                   "Jumlah: {$amount}\n" .
                   "Status: {$statusLabel}\n" .
                   "Pembayaran: {$paymentMethod}\n\n" .
                   "Donasi Anda telah kami terima dan akan digunakan untuk membantu mereka yang membutuhkan. Terima kasih atas kebaikan hati Anda!";
        } elseif ($donation['status'] === 'pending') {
            return "Menunggu Pembayaran - " . APP_NAME . "\n\n" .
                   "Kampanye: {$campaign['title']}\n" .
                   "Jumlah: {$amount}\n" .
                   "Status: {$statusLabel}\n" .
                   "Pembayaran: {$paymentMethod}\n\n" .
                   "Silakan selesaikan pembayaran Anda sesuai instruksi yang telah diberikan. Jika sudah membayar, mohon tunggu beberapa saat untuk verifikasi.";
        } else {
            return "Update Status Donasi - " . APP_NAME . "\n\n" .
                   "Kampanye: {$campaign['title']}\n" .
                   "Jumlah: {$amount}\n" .
                   "Status: {$statusLabel}\n" .
                   "Pembayaran: {$paymentMethod}\n\n" .
                   "Terima kasih atas perhatian Anda terhadap kampanye ini.";
        }
    }
    
    /**
     * Mendapatkan template email berdasarkan status donasi
     * 
     * @param string $status Status donasi
     * @return string
     */
    private function getEmailTemplate($status) {
        if ($status === 'success') {
            return file_get_contents(BASEPATH . '/app/views/emails/donation_success.html');
        } elseif ($status === 'pending') {
            return file_get_contents(BASEPATH . '/app/views/emails/donation_pending.html');
        } else {
            return file_get_contents(BASEPATH . '/app/views/emails/donation_update.html');
        }
    }
    
    /**
     * Mendapatkan label status yang lebih friendly
     * 
     * @param string $status Status donasi
     * @return string
     */
    private function getStatusLabel($status) {
        switch ($status) {
            case 'success':
                return 'Berhasil';
            case 'pending':
                return 'Menunggu Pembayaran';
            case 'failed':
                return 'Gagal';
            case 'canceled':
                return 'Dibatalkan';
            case 'expired':
                return 'Kedaluwarsa';
            case 'refunded':
                return 'Dikembalikan';
            default:
                return ucfirst($status);
        }
    }
    
    /**
     * Format metode pembayaran untuk ditampilkan
     * 
     * @param string $method Metode pembayaran
     * @param string|null $channel Channel pembayaran
     * @return string
     */
    private function formatPaymentMethod($method, $channel = null) {
        if ($method === 'bank_transfer') {
            $label = 'Transfer Bank';
            if (!empty($channel)) {
                $label .= ' ' . strtoupper($channel);
            }
            return $label;
        } elseif ($method === 'e-wallet') {
            $label = 'E-Wallet';
            if (!empty($channel)) {
                $label .= ' ' . strtoupper($channel);
            }
            return $label;
        } else {
            return ucfirst($method);
        }
    }
}
