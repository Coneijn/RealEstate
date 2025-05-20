<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/models/Property.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $data = [
        'user_id' => 1, // In a real app, this would come from session
        'property_type_id' => intval($_POST['property_type_id']),
        'title' => htmlspecialchars(strip_tags($_POST['title'])),
        'description' => htmlspecialchars(strip_tags($_POST['description'])),
        'address' => htmlspecialchars(strip_tags($_POST['address'])),
        'city' => htmlspecialchars(strip_tags($_POST['city'])),
        'state' => htmlspecialchars(strip_tags($_POST['state'])),
        'zip_code' => htmlspecialchars(strip_tags($_POST['zip_code'])),
        'latitude' => floatval($_POST['latitude']),
        'longitude' => floatval($_POST['longitude']),
        'price' => floatval($_POST['price']),
        'size' => floatval($_POST['size']),
        'bedrooms' => intval($_POST['bedrooms']),
        'bathrooms' => intval($_POST['bathrooms']),
        'construction_year' => !empty($_POST['construction_year']) ? intval($_POST['construction_year']) : null,
        'parking_spaces' => !empty($_POST['parking_spaces']) ? intval($_POST['parking_spaces']) : 0
    ];
    
    // Validate coordinates
    if (!is_numeric($data['latitude']) || !is_numeric($data['longitude'])) {
        header('Location: index.php?error=Invalid coordinates');
        exit();
    }
    
    // Validate image URL
    $imageUrl = filter_var($_POST['image'], FILTER_VALIDATE_URL);
    if (!$imageUrl) {
        header('Location: index.php?error=Invalid image URL');
        exit();
    }
    
    // Create property
    $propertyId = Property::create($data);
    
    if ($propertyId) {
        // Save image URL to database
        $conn = getDBConnection();
        setCharset($conn);
        
        $query = "INSERT INTO property_images (property_id, url, is_main, `order`) 
                 VALUES (?, ?, 1, 0)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('is', $propertyId, $imageUrl);
        $stmt->execute();
        $stmt->close();
        $conn->close();
        
        header('Location: index.php?success=Property added successfully');
        exit();
    } else {
        header('Location: index.php?error=Failed to add property');
        exit();
    }
} else {
    header('Location: index.php');
    exit();
}
?>