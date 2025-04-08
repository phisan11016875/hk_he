<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Bangkok');
define('ROOT_PATH', __DIR__);
define('BASE_URL', 'http://localhost:81/hk_hr/');

// ดีบัก: แสดงค่า $_GET
echo "Current Directory: " . __DIR__ . "<br>";
echo "Current File: " . __FILE__ . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "<pre>DEBUG: GET parameters: ";
print_r($_GET);
echo "</pre>";

// เรียกใช้ไฟล์การตั้งค่า
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'config/functions.php';

// ตรวจสอบการเข้าถึงหน้า
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// ดีบัก: แสดงเส้นทางที่ต้องการเข้าถึง
echo "DEBUG: Page requested: " . $page . "<br>";

// โหลด AuthController ก่อน
require_once 'controllers/AuthController.php';

// จัดการการเข้าสู่ระบบและออกจากระบบ
if ($page == 'login') {
    $controller = new AuthController();
    $controller->login();
    exit;
} elseif ($page == 'logout') {
    $controller = new AuthController();
    $controller->logout();
    exit;
} elseif ($page == 'forgot-password') {
    $controller = new AuthController();
    $controller->forgotPassword();
    exit;
} elseif ($page == 'reset-password') {
    $controller = new AuthController();
    $controller->resetPassword();
    exit;
}

// ตรวจสอบว่ามีการล็อกอินหรือไม่ ถ้าไม่ให้ไปที่หน้าล็อกอิน
if (!isset($_SESSION['user_id']) && $page != 'login') {
    header('Location: index.php?page=login');
    exit;
}

// กำหนด Controller และ Action
$controller = '';
$action = '';
$id = null;

// แยกส่วนของ page
$pageParts = explode('/', $page);

// ดีบัก: แสดงค่า pageParts
echo "<pre>DEBUG: Page parts: ";
print_r($pageParts);
echo "</pre>";

// กำหนด controller และ action ตาม page
if (count($pageParts) == 1) {
    // กรณีมีแค่ controller
    $controller = $pageParts[0] . 'Controller';
    $action = 'index';
} elseif (count($pageParts) == 2) {
    // กรณีมี controller และ action
    if (is_numeric($pageParts[1])) {
        // กรณี controller และ id
        $controller = $pageParts[0] . 'Controller';
        $action = 'view';
        $id = $pageParts[1];
    } else {
        // กรณี controller และ action ปกติ
        $controller = $pageParts[0] . 'Controller';
        $action = $pageParts[1];
    }
} elseif (count($pageParts) == 3) {
    // กรณีมี controller, action และ id
    $controller = $pageParts[0] . 'Controller';
    $action = $pageParts[1];
    $id = $pageParts[2];
}

// แปลง controller เป็นชื่อคลาส (camelCase)
$controller = str_replace(' ', '', ucwords(str_replace('-', ' ', $controller)));

// แปลง action เป็นชื่อเมธอด (camelCase)
$action = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $action))));

// ดีบัก: แสดงค่า controller, action และ id
echo "DEBUG: Controller: " . $controller . "<br>";
echo "DEBUG: Action: " . $action . "<br>";
echo "DEBUG: ID: " . $id . "<br>";

// กรณีหน้าหลัก (dashboard)
if ($controller == 'DashboardController') {
    $controller = 'DashboardController';
    
    // เลือกหน้า dashboard ตามบทบาทของผู้ใช้
    if (isset($_SESSION['role'])) {
        switch ($_SESSION['role']) {
            case 'admin':
                $action = 'admin';
                break;
            case 'hr':
                $action = 'hr';
                break;
            case 'manager':
                $action = 'manager';
                break;
            case 'employee':
                $action = 'employee';
                break;
            default:
                $action = 'index';
                break;
        }
    }
}

// โหลด Controller
$controllerFile = 'controllers/' . $controller . '.php';
echo "DEBUG: Looking for controller file: " . $controllerFile . "<br>";
echo "DEBUG: File exists: " . (file_exists($controllerFile) ? 'Yes' : 'No') . "<br>";

if (file_exists($controllerFile)) {
    require_once $controllerFile;
    
    // ตรวจสอบว่าคลาสมีอยู่จริง
    if (class_exists($controller)) {
        // สร้างอินสแตนซ์ของ Controller
        $controllerInstance = new $controller();
        
        // ตรวจสอบว่ามีเมธอดที่ต้องการหรือไม่
        if (method_exists($controllerInstance, $action)) {
            echo "DEBUG: Method " . $action . " exists in " . $controller . "<br>";
            // เรียกใช้เมธอด
            if ($id !== null) {
                // กรณีมี parameter
                $controllerInstance->$action($id);
            } else {
                // กรณีไม่มี parameter
                $controllerInstance->$action();
            }
        } else {
            // ไม่พบเมธอดที่ต้องการ
            echo "DEBUG: Method " . $action . " NOT found in " . $controller . "<br>";
            include 'views/includes/404.php';
        }
    } else {
        // ไม่พบคลาส
        echo "DEBUG: Class " . $controller . " NOT found<br>";
        include 'views/includes/404.php';
    }
} else {
    // ไม่พบ Controller ที่ต้องการ
    echo "DEBUG: Controller file NOT found<br>";
    include 'views/includes/404.php';
}
?>