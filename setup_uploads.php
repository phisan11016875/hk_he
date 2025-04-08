<?php
/**
 * สคริปต์สำหรับการสร้างโฟลเดอร์สำหรับการอัปโหลดไฟล์ในระบบ HR โรงพยาบาลห้วยเกิ้ง
 */

// กำหนด base path ให้เป็นไดเรกทอรีปัจจุบัน
$base_path = __DIR__;

// โฟลเดอร์ที่ต้องสร้าง
$directories = [
    'uploads',
    'uploads/employees',
    'uploads/licenses',
    'uploads/certificates',
    'uploads/leaves',
    'uploads/temp'
];

// สร้างโฟลเดอร์
$success_count = 0;
$error_messages = [];

foreach ($directories as $dir) {
    $dir_path = $base_path . '/' . $dir;
    
    // ตรวจสอบว่าโฟลเดอร์มีอยู่แล้วหรือไม่
    if (is_dir($dir_path)) {
        echo "โฟลเดอร์ {$dir} มีอยู่แล้ว\n";
        $success_count++;
        continue;
    }
    
    // สร้างโฟลเดอร์
    if (mkdir($dir_path, 0755, true)) {
        echo "สร้างโฟลเดอร์ {$dir} สำเร็จ\n";
        $success_count++;
        
        // สร้างไฟล์ index.html เพื่อป้องกันการแสดงรายการไฟล์
        $index_content = "<html><head><title>403 Forbidden</title></head><body><h1>403 Forbidden</h1><p>Access to this resource on the server is denied.</p></body></html>";
        file_put_contents($dir_path . '/index.html', $index_content);
        
        // สร้างไฟล์ .htaccess สำหรับโฟลเดอร์ temp เพื่อป้องกันการเข้าถึงโดยตรง
        if ($dir === 'uploads/temp') {
            $htaccess_content = "Order deny,allow\nDeny from all";
            file_put_contents($dir_path . '/.htaccess', $htaccess_content);
        }
    } else {
        echo "ไม่สามารถสร้างโฟลเดอร์ {$dir} ได้\n";
        $error_messages[] = "ไม่สามารถสร้างโฟลเดอร์ {$dir} ได้";
    }
}

// ตรวจสอบสิทธิ์การเขียน
foreach ($directories as $dir) {
    $dir_path = $base_path . '/' . $dir;
    
    if (is_dir($dir_path)) {
        // ทดสอบการเขียนไฟล์
        $test_file = $dir_path . '/test_write.tmp';
        if (file_put_contents($test_file, 'test') !== false) {
            echo "โฟลเดอร์ {$dir} สามารถเขียนได้\n";
            // ลบไฟล์ทดสอบ
            unlink($test_file);
        } else {
            echo "โฟลเดอร์ {$dir} ไม่สามารถเขียนได้ กรุณาตรวจสอบสิทธิ์การเข้าถึง\n";
            $error_messages[] = "โฟลเดอร์ {$dir} ไม่สามารถเขียนได้ กรุณาตรวจสอบสิทธิ์การเข้าถึง";
        }
    }
}

// สรุปผลการสร้างโฟลเดอร์
echo "\n----- สรุปผลการสร้างโฟลเดอร์ -----\n";
echo "สร้างสำเร็จ: {$success_count} จาก " . count($directories) . " โฟลเดอร์\n";

if (!empty($error_messages)) {
    echo "\nข้อผิดพลาดที่พบ:\n";
    foreach ($error_messages as $error) {
        echo "- {$error}\n";
    }
    echo "\nคำแนะนำ: กรุณาตรวจสอบสิทธิ์การเข้าถึงไฟล์และโฟลเดอร์ของเว็บเซิร์ฟเวอร์\n";
    echo "สำหรับ Apache บน Linux: sudo chown -R www-data:www-data " . $base_path . "/uploads\n";
    echo "สำหรับ Apache บน Windows: ตรวจสอบสิทธิ์การเข้าถึงของผู้ใช้ที่เว็บเซิร์ฟเวอร์ใช้\n";
    exit(1);
} else {
    echo "\nการตั้งค่าโฟลเดอร์สำหรับการอัปโหลดไฟล์เสร็จสมบูรณ์\n";
    exit(0);
}