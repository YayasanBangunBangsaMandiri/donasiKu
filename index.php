<?php
/**
 * DonateHub - Platform Donasi Online
 * Entry point utama aplikasi
 */

// Memulai session
session_start();

// Load autoloader dari Composer
require_once __DIR__ . '/vendor/autoload.php';

// Pastikan config dimuat
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/config/config.php';
    require_once __DIR__ . '/config/database.php';
}

// Parse URL
$url = isset($_GET['url']) ? $_GET['url'] : '';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

// Tentukan controller
$controllerName = !empty($url[0]) ? ucfirst($url[0]) . 'Controller' : 'HomeController';
$controllerFile = __DIR__ . '/app/Controllers/' . $controllerName . '.php';

// Tentukan method
$method = isset($url[1]) ? $url[1] : 'index';

// Parameter
$params = array_slice($url, 2);

// Debug info
if (isset($_GET['debug'])) {
    echo "<pre>";
    echo "URL: " . print_r($url, true) . "\n";
    echo "Controller: $controllerName\n";
    echo "Method: $method\n";
    echo "Params: " . print_r($params, true) . "\n";
    echo "Controller file: $controllerFile\n";
    echo "Exists: " . (file_exists($controllerFile) ? 'Yes' : 'No') . "\n";
    echo "</pre>";
    exit;
}

// Periksa apakah file controller ada
if (!file_exists($controllerFile)) {
    $controllerName = 'ErrorController';
    $controllerFile = __DIR__ . '/app/Controllers/' . $controllerName . '.php';
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
    $controllerFile = __DIR__ . '/app/Controllers/' . $controllerName . '.php';
    $method = 'notFound';
    $params = [];
    
    // Inisialisasi controller error
    $controllerNamespace = 'App\\Controllers\\' . $controllerName;
    $controller = new $controllerNamespace();
}

// Jalankan method dengan parameter
call_user_func_array([$controller, $method], $params); 