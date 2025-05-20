<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/Property.php';

header('Content-Type: application/json');

try {
    // Validate input
    $required = ['title', 'property_type_id', 'address', 'price', 'size'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    // Geocode address (simplified - in production use a geocoding service)
    $latitude = $_POST['latitude'] ?? 0;
    $longitude = $_POST['longitude'] ?? 0;
    
    // Create property
    $data = [
        'user_id' => 1, // In real app, get from session
        'property_type_id' => (int)$_POST['property_type_id'],
        'title' => htmlspecialchars(strip_tags($_POST['title'])),
        'description' => htmlspecialchars(strip_tags($_POST['description'] ?? '')),
        'address' => htmlspecialchars(strip_tags($_POST['address'])),
        'latitude' => $latitude,
        'longitude' => $longitude,
        'price' => (float)$_POST['price'],
        'size' => (float)$_POST['size'],
        'bedrooms' => (int)($_POST['bedrooms'] ?? 0),
        'bathrooms' => (int)($_POST['bathrooms'] ?? 0),
        'parking_spaces' => (int)($_POST['parking_spaces'] ?? 0),
        'construction_year' => !empty($_POST['construction_year']) ? (int)$_POST['construction_year'] : null
    ];
    
    $propertyId = Property::create($data);
    
    if (!$propertyId) {
        throw new Exception("Failed to create property");
    }
    
    // Handle image uploads
    $images = [];
    if (!empty($_FILES['images']['name'][0])) {
        $uploadDir = __DIR__ . '/../uploads/properties/' . $propertyId . '/';
        
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                $fileName = uniqid() . '_' . basename($_FILES['images']['name'][$key]);
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($tmpName, $targetPath)) {
                    $images[] = [
                        'url' => '/uploads/properties/' . $propertyId . '/' . $fileName,
                        'is_main' => ($key === 0),
                        'order' => $key
                    ];
                }
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'propertyId' => $propertyId,
        'images' => $images
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}