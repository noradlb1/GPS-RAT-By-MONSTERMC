<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Use absolute path relative to current directory
$dataFile = __DIR__ . '/admin/locations.json';
$adminDir = __DIR__ . '/admin';

// Create admin directory if not exists
if(!is_dir($adminDir)) {
    if(!mkdir($adminDir, 0777, true)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Cannot create admin directory']);
        exit;
    }
}

// Create file if not exists
if(!file_exists($dataFile)) {
    if(file_put_contents($dataFile, '[]') === false) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Cannot create data file']);
        exit;
    }
    @chmod($dataFile, 0666);
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if($data && isset($data['lat']) && isset($data['lon'])) {
        // Load existing locations
        $locations = json_decode(file_get_contents($dataFile), true);
        if(!is_array($locations)) $locations = [];
        
        // Get client info
        $clientInfo = [
            'id' => uniqid('loc_', true),
            'lat' => floatval($data['lat']),
            'lon' => floatval($data['lon']),
            'accuracy' => floatval($data['accuracy']),
            'timestamp' => $data['timestamp'] ?? time() * 1000,
            'time' => date('Y-m-d H:i:s'),
            
            // Address details
            'address' => $data['address'] ?? '',
            'street' => $data['street'] ?? '',
            'house_number' => $data['house_number'] ?? '',
            'suburb' => $data['suburb'] ?? '',
            'city' => $data['city'] ?? '',
            'state' => $data['state'] ?? '',
            'postcode' => $data['postcode'] ?? '',
            'country' => $data['country'] ?? '',
            
            // Client info
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'referer' => $_SERVER['HTTP_REFERER'] ?? '',
            
            // Browser info
            'browser' => getBrowser(),
            'os' => getOS(),
            'device' => getDevice()
        ];
        
        // Add to beginning
        array_unshift($locations, $clientInfo);
        
        // Keep last 200
        $locations = array_slice($locations, 0, 200);
        
        // Save
        if(file_put_contents($dataFile, json_encode($locations, JSON_PRETTY_PRINT))) {
            echo json_encode(['success' => true, 'id' => $clientInfo['id']]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to save']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}

function getBrowser() {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if(preg_match('/MSIE/i', $ua)) return 'Internet Explorer';
    if(preg_match('/Firefox/i', $ua)) return 'Firefox';
    if(preg_match('/Chrome/i', $ua)) return 'Chrome';
    if(preg_match('/Safari/i', $ua)) return 'Safari';
    if(preg_match('/Opera/i', $ua)) return 'Opera';
    if(preg_match('/Edge/i', $ua)) return 'Edge';
    return 'Unknown';
}

function getOS() {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if(preg_match('/Windows NT 10/i', $ua)) return 'Windows 10/11';
    if(preg_match('/Windows NT 6.3/i', $ua)) return 'Windows 8.1';
    if(preg_match('/Windows NT 6.2/i', $ua)) return 'Windows 8';
    if(preg_match('/Windows NT 6.1/i', $ua)) return 'Windows 7';
    if(preg_match('/Macintosh|Mac OS X/i', $ua)) return 'macOS';
    if(preg_match('/Linux/i', $ua)) return 'Linux';
    if(preg_match('/Android/i', $ua)) return 'Android';
    if(preg_match('/iPhone|iPad|iPod/i', $ua)) return 'iOS';
    return 'Unknown';
}

function getDevice() {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if(preg_match('/Mobile|Android|iPhone|iPad|iPod/i', $ua)) return 'Mobile';
    if(preg_match('/Tablet|iPad/i', $ua)) return 'Tablet';
    return 'Desktop';
}
?>