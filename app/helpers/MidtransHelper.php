<?php
namespace App\Helpers;

/**
 * Helper untuk integrasi dengan Midtrans
 */
class MidtransHelper {
    /**
     * Inisialisasi konfigurasi Midtrans
     * 
     * @return void
     */
    public static function init() {
        // Load Midtrans Config
        \Midtrans\Config::$serverKey = MIDTRANS_SERVER_KEY;
        \Midtrans\Config::$isProduction = (MIDTRANS_ENVIRONMENT === 'production');
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;
    }
    
    /**
     * Membuat transaksi Snap
     * 
     * @param array $params Parameter untuk Snap
     * @return array|false Data transaksi atau false jika gagal
     */
    public static function createTransaction($params) {
        self::init();
        
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
     * Mendapatkan status transaksi
     * 
     * @param string $orderId ID order
     * @return array|false Data status transaksi atau false jika gagal
     */
    public static function getStatus($orderId) {
        self::init();
        
        try {
            $status = \Midtrans\Transaction::status($orderId);
            return $status;
        } catch (\Exception $e) {
            // Log error
            error_log('Midtrans Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Membatalkan transaksi
     * 
     * @param string $orderId ID order
     * @return array|false Data hasil pembatalan atau false jika gagal
     */
    public static function cancelTransaction($orderId) {
        self::init();
        
        try {
            $cancel = \Midtrans\Transaction::cancel($orderId);
            return $cancel;
        } catch (\Exception $e) {
            // Log error
            error_log('Midtrans Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mengembalikan dana transaksi
     * 
     * @param string $orderId ID order
     * @param array $params Parameter untuk refund
     * @return array|false Data hasil refund atau false jika gagal
     */
    public static function refundTransaction($orderId, $params) {
        self::init();
        
        try {
            $refund = \Midtrans\Transaction::refund($orderId, $params);
            return $refund;
        } catch (\Exception $e) {
            // Log error
            error_log('Midtrans Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Membuat parameter untuk transaksi Midtrans
     * 
     * @param array $donation Data donasi
     * @param array $campaign Data campaign
     * @return array Parameter untuk Midtrans
     */
    public static function createParams($donation, $campaign) {
        return [
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
    }
    
    /**
     * Mendapatkan panduan pembayaran berdasarkan metode pembayaran
     * 
     * @param string $paymentType Jenis pembayaran
     * @return array Data panduan pembayaran
     */
    public static function getPaymentGuide($paymentType) {
        $guides = [
            'bank_transfer' => [
                'title' => 'Panduan Transfer Bank',
                'steps' => [
                    'Catat nomor rekening virtual account yang diberikan',
                    'Login ke m-banking, i-banking, atau ATM bank Anda',
                    'Pilih menu Transfer atau Pembayaran',
                    'Masukkan nomor virtual account sebagai tujuan transfer',
                    'Masukkan jumlah donasi sesuai yang tertera',
                    'Konfirmasi dan selesaikan pembayaran',
                    'Simpan bukti pembayaran'
                ],
                'note' => 'Pembayaran akan diverifikasi secara otomatis dalam 5-10 menit.'
            ],
            'gopay' => [
                'title' => 'Panduan Pembayaran GoPay',
                'steps' => [
                    'Buka aplikasi Gojek di smartphone Anda',
                    'Scan QR code yang ditampilkan',
                    'Periksa detail pembayaran',
                    'Masukkan PIN GoPay Anda',
                    'Pembayaran selesai'
                ],
                'note' => 'Pembayaran akan diverifikasi secara otomatis setelah pembayaran berhasil.'
            ],
            'shopeepay' => [
                'title' => 'Panduan Pembayaran ShopeePay',
                'steps' => [
                    'Buka aplikasi Shopee di smartphone Anda',
                    'Pilih ShopeePay',
                    'Scan QR code yang ditampilkan',
                    'Periksa detail pembayaran',
                    'Konfirmasi pembayaran',
                    'Masukkan PIN ShopeePay Anda'
                ],
                'note' => 'Pembayaran akan diverifikasi secara otomatis setelah pembayaran berhasil.'
            ],
            'credit_card' => [
                'title' => 'Panduan Pembayaran Kartu Kredit',
                'steps' => [
                    'Isi detail kartu kredit Anda',
                    'Masukkan nomor kartu, tanggal kadaluarsa, dan CVV',
                    'Untuk keamanan, Anda akan diarahkan ke halaman 3D Secure',
                    'Masukkan kode OTP yang dikirimkan ke nomor handphone Anda',
                    'Pembayaran selesai setelah verifikasi berhasil'
                ],
                'note' => 'Pembayaran akan diverifikasi secara otomatis setelah pembayaran berhasil.'
            ]
        ];
        
        return $guides[$paymentType] ?? [
            'title' => 'Panduan Pembayaran',
            'steps' => [
                'Ikuti instruksi pembayaran yang diberikan',
                'Selesaikan pembayaran sesuai dengan metode yang dipilih',
                'Simpan bukti pembayaran'
            ],
            'note' => 'Pembayaran akan diverifikasi secara otomatis setelah pembayaran berhasil.'
        ];
    }
} 