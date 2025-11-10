<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$file = 'locations.json';

// Auto-create file if not exists
if(!file_exists($file)) {
    file_put_contents($file, '[]');
    chmod($file, 0666);
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add new location
    $data = json_decode(file_get_contents('php://input'), true);
    if($data && isset($data['lat'])) {
        $locations = json_decode(file_get_contents($file), true);
        if(!is_array($locations)) $locations = [];
        
        $data['id'] = uniqid();
        $data['time'] = date('Y-m-d H:i:s');
        array_unshift($locations, $data);
        $locations = array_slice($locations, 0, 100);
        
        file_put_contents($file, json_encode($locations, JSON_PRETTY_PRINT));
        echo json_encode(['success' => true, 'id' => $data['id']]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
    }
    
} elseif($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Delete location(s)
    $data = json_decode(file_get_contents('php://input'), true);
    
    if(isset($data['delete_all']) && $data['delete_all'] === true) {
        // Delete all
        file_put_contents($file, '[]');
        echo json_encode(['success' => true, 'message' => 'All deleted']);
        
    } elseif(isset($data['id'])) {
        // Delete specific ID
        $locations = json_decode(file_get_contents($file), true);
        if(!is_array($locations)) $locations = [];
        
        $locations = array_filter($locations, function($loc) use ($data) {
            return $loc['id'] !== $data['id'];
        });
        
        $locations = array_values($locations);
        file_put_contents($file, json_encode($locations, JSON_PRETTY_PRINT));
        echo json_encode(['success' => true, 'message' => 'Deleted']);
        
    } else {
        echo json_encode(['success' => false, 'error' => 'No ID provided']);
    }
    
} elseif($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get all locations
    $locations = json_decode(file_get_contents($file), true);
    if(!is_array($locations)) $locations = [];
    echo json_encode($locations);
    
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}
?>