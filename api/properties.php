<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/Property.php';

header('Content-Type: application/json');

try {
    $filters = [
        'type' => $_GET['type'] ?? null,
        'min_price' => $_GET['min_price'] ?? null,
        'max_price' => $_GET['max_price'] ?? null,
        'min_size' => $_GET['min_size'] ?? null,
        'bedrooms' => $_GET['bedrooms'] ?? null,
        'lat' => $_GET['lat'] ?? null,
        'lng' => $_GET['lng'] ?? null,
        'radius' => $_GET['radius'] ?? null
    ];
    
    if ($filters['lat'] && $filters['lng'] && $filters['radius']) {
        $properties = Property::getByRadius($filters['lat'], $filters['lng'], $filters['radius']);
    } else {
        $properties = Property::getAll($filters);
    }
    
    echo json_encode($properties);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
