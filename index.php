<?php
/**
 * DonateHub - Platform Donasi Online
 * Entry point utama aplikasi
 */

// Memulai session
session_start();

// Load autoloader dari Composer
require_once __DIR__ . '/vendor/autoload.php';

// Load konfigurasi
require_once __DIR__ . '/config/config.php';

// Load database connection
require_once __DIR__ . '/config/database.php';

// Parse URL
$url = isset($_GET['url']) ? $_GET['url'] : '';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

// Tentukan controller
$controllerName = !empty($url[0]) ? ucfirst($url[0]) . 'Controller' : 'HomeController';
$controllerFile = __DIR__ . '/app/controllers/' . $controllerName . '.php';

// Tentukan method
$method = isset($url[1]) ? $url[1] : 'index';

// Parameter
$params = array_slice($url, 2);

// Periksa apakah file controller ada
if (!file_exists($controllerFile)) {
    $controllerName = 'ErrorController';
    $controllerFile = __DIR__ . '/app/controllers/' . $controllerName . '.php';
    $method = 'notFound';
    $params = [];
}

// Buat namespace untuk controller
$controllerNamespace = 'App\\Controllers\\' . $controllerName;

// Inisialisasi controller
$controller = new $controllerNamespace();

// Periksa apakah method ada
if (!method_exists($controller, $method)) {
    $controllerName = 'ErrorController';
    $controllerFile = __DIR__ . '/app/controllers/' . $controllerName . '.php';
    $method = 'notFound';
    $params = [];
    
    // Inisialisasi controller error
    $controllerNamespace = 'App\\Controllers\\' . $controllerName;
    $controller = new $controllerNamespace();
}

// Jalankan method dengan parameter
call_user_func_array([$controller, $method], $params); 