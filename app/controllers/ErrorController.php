<?php
namespace App\Controllers;

/**
 * Controller untuk menangani error
 */
class ErrorController extends Controller {
    /**
     * Menampilkan halaman 404 Not Found
     * 
     * @return void
     */
    public function notFound() {
        http_response_code(404);
        $this->view('errors/404', [
            'title' => 'Halaman Tidak Ditemukan - ' . APP_NAME
        ]);
    }
    
    /**
     * Menampilkan halaman 403 Forbidden
     * 
     * @return void
     */
    public function forbidden() {
        http_response_code(403);
        $this->view('errors/403', [
            'title' => 'Akses Ditolak - ' . APP_NAME
        ]);
    }
    
    /**
     * Menampilkan halaman 500 Internal Server Error
     * 
     * @param \Exception $exception Exception yang terjadi
     * @return void
     */
    public function serverError($exception = null) {
        http_response_code(500);
        $this->view('errors/500', [
            'title' => 'Terjadi Kesalahan - ' . APP_NAME,
            'exception' => $exception
        ]);
    }
    
    /**
     * Menampilkan halaman maintenance
     * 
     * @return void
     */
    public function maintenance() {
        http_response_code(503);
        $this->view('errors/maintenance', [
            'title' => 'Sedang Dalam Pemeliharaan - ' . APP_NAME
        ]);
    }
} 