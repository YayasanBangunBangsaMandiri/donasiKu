<?php
namespace App\Models;

/**
 * Model untuk mengelola data pengguna
 */
class User extends Model {
    protected $table = 'users';
    protected $fillable = [
        'name', 'email', 'password', 'role', 'phone', 
        'profile_image', 'is_active', 'remember_token', 
        'email_verified_at', 'two_factor_secret', 'two_factor_enabled'
    ];
    
    /**
     * Membuat user baru
     * 
     * @param array $data Data user
     * @return int|false ID user baru atau false jika gagal
     */
    public function register($data) {
        // Pastikan email belum digunakan
        $existingUser = $this->findBy('email', $data['email']);
        if ($existingUser) {
            return false;
        }
        
        // Hash password
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Set role default jika tidak ada
        if (!isset($data['role'])) {
            $data['role'] = 'user';
        }
        
        // Set status aktif
        $data['is_active'] = 1;
        
        // Set waktu pembuatan
        $data['created_at'] = date('Y-m-d H:i:s');
        
        return $this->create($data);
    }
    
    /**
     * Autentikasi user
     * 
     * @param string $email Email user
     * @param string $password Password user
     * @return array|false Data user jika berhasil atau false jika gagal
     */
    public function authenticate($email, $password) {
        $user = $this->findBy('email', $email);
        
        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }
        
        if (!$user['is_active']) {
            return false;
        }
        
        return $user;
    }
    
    /**
     * Update password user
     * 
     * @param int $userId ID user
     * @param string $newPassword Password baru
     * @return bool
     */
    public function updatePassword($userId, $newPassword) {
        return $this->update($userId, [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Buat token untuk reset password
     * 
     * @param string $email Email user
     * @return string|false Token jika berhasil atau false jika gagal
     */
    public function createPasswordResetToken($email) {
        $user = $this->findBy('email', $email);
        
        if (!$user) {
            return false;
        }
        
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $this->db->query(
            "INSERT INTO password_resets (email, token, expires_at, created_at) VALUES (?, ?, ?, NOW())",
            [$email, $token, $expiry]
        );
        
        return $token;
    }
    
    /**
     * Validasi token reset password
     * 
     * @param string $token Token reset password
     * @param string $email Email user
     * @return bool
     */
    public function validatePasswordResetToken($token, $email) {
        $result = $this->db->fetch(
            "SELECT * FROM password_resets WHERE token = ? AND email = ? AND expires_at > NOW()",
            [$token, $email]
        );
        
        return $result ? true : false;
    }
    
    /**
     * Reset password user
     * 
     * @param string $token Token reset password
     * @param string $email Email user
     * @param string $newPassword Password baru
     * @return bool
     */
    public function resetPassword($token, $email, $newPassword) {
        if (!$this->validatePasswordResetToken($token, $email)) {
            return false;
        }
        
        $user = $this->findBy('email', $email);
        
        if (!$user) {
            return false;
        }
        
        $result = $this->updatePassword($user['id'], $newPassword);
        
        if ($result) {
            // Hapus token yang sudah digunakan
            $this->db->query(
                "DELETE FROM password_resets WHERE token = ? AND email = ?",
                [$token, $email]
            );
        }
        
        return $result;
    }
    
    /**
     * Mengaktifkan atau menonaktifkan 2FA
     * 
     * @param int $userId ID user
     * @param bool $enable Status 2FA
     * @param string $secret Secret key 2FA
     * @return bool
     */
    public function toggleTwoFactor($userId, $enable, $secret = null) {
        $data = [
            'two_factor_enabled' => $enable ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($secret) {
            $data['two_factor_secret'] = $secret;
        }
        
        return $this->update($userId, $data);
    }
    
    /**
     * Mendapatkan admin
     * 
     * @return array
     */
    public function getAdmins() {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE role IN ('admin', 'super_admin') ORDER BY name ASC"
        );
    }
} 