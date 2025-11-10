<?php
session_start();
if(!isset($_SESSION['logged_in'])) {
    http_response_code(403);
    die(json_encode(['error' => 'Unauthorized']));
}

header('Content-Type: application/json');

$dataFile = 'locations.json';

if(!file_exists($dataFile)) {
    file_put_contents($dataFile, '[]');
    chmod($dataFile, 0666);
}

if($_SERVER['REQUEST_METHOD'] === 'GET') {
    $locations = json_decode(file_get_contents($dataFile), true);
    if(!is_array($locations)) $locations = [];
    echo json_encode($locations);
    
} elseif($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if(isset($data['delete_all']) && $data['delete_all'] === true) {
        file_put_contents($dataFile, '[]');
        echo json_encode(['success' => true]);
        
    } elseif(isset($data['id'])) {
        $locations = json_decode(file_get_contents($dataFile), true);
        if(!is_array($locations)) $locations = [];
        
        $locations = array_filter($locations, function($loc) use ($data) {
            return $loc['id'] !== $data['id'];
        });
        
        $locations = array_values($locations);
        file_put_contents($dataFile, json_encode($locations, JSON_PRETTY_PRINT));
        echo json_encode(['success' => true]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>