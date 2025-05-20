<?php
require_once __DIR__ . '/../config.php';

class Property {
    // Get all properties with filters
    public static function getAll($filters = []) {
        $conn = getDBConnection();
        setCharset($conn);
        
        $query = "SELECT p.*, pt.name AS property_type, 
                 (SELECT url FROM property_images WHERE property_id = p.id AND is_main = TRUE LIMIT 1) AS main_image
                 FROM properties p
                 JOIN property_types pt ON p.property_type_id = pt.id
                 WHERE p.active = TRUE";
        
        // Add filters
        $params = [];
        $types = '';
        
        if (!empty($filters['type'])) {
            $query .= " AND p.property_type_id = ?";
            $params[] = $filters['type'];
            $types .= 'i';
        }
        
        if (!empty($filters['min_price'])) {
            $query .= " AND p.price >= ?";
            $params[] = $filters['min_price'];
            $types .= 'd';
        }
        
        if (!empty($filters['max_price'])) {
            $query .= " AND p.price <= ?";
            $params[] = $filters['max_price'];
            $types .= 'd';
        }
        
        if (!empty($filters['min_size'])) {
            $query .= " AND p.size >= ?";
            $params[] = $filters['min_size'];
            $types .= 'd';
        }
        
        if (!empty($filters['bedrooms'])) {
            $query .= " AND p.bedrooms >= ?";
            $params[] = $filters['bedrooms'];
            $types .= 'i';
        }
        
        $query .= " ORDER BY p.created_at DESC";
        
        $stmt = $conn->prepare($query);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $properties = $result->fetch_all(MYSQLI_ASSOC);
        
        $stmt->close();
        $conn->close();
        
        return $properties;
    }
    
    // Get property by ID with all details
    public static function getById($id) {
        $conn = getDBConnection();
        setCharset($conn);
        
        // Get property basic info
        $query = "SELECT p.*, pt.name AS property_type 
                 FROM properties p
                 JOIN property_types pt ON p.property_type_id = pt.id
                 WHERE p.id = ? AND p.active = TRUE";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $property = $stmt->get_result()->fetch_assoc();
        
        if (!$property) {
            return null;
        }
        
        // Get images
        $query = "SELECT url, is_main FROM property_images WHERE property_id = ? ORDER BY `order`";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $property['images'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Get features
        $query = "SELECT f.id, f.name, f.icon 
                 FROM property_features pf
                 JOIN features f ON pf.feature_id = f.id
                 WHERE pf.property_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $property['features'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $stmt->close();
        $conn->close();
        
        return $property;
    }
    
    // Get properties within radius (in km)
    public static function getByRadius($lat, $lng, $radius) {
        $conn = getDBConnection();
        setCharset($conn);
        
        $query = "SELECT p.id, p.title, p.price, p.address, p.latitude, p.longitude,
                 (6371 * ACOS(
                     COS(RADIANS(?)) * 
                     COS(RADIANS(p.latitude)) * 
                     COS(RADIANS(p.longitude) - RADIANS(?)) + 
                     SIN(RADIANS(?)) * 
                     SIN(RADIANS(p.latitude))
                 )) AS distance
                 FROM properties p
                 HAVING distance < ?
                 ORDER BY distance";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param('dddd', $lat, $lng, $lat, $radius);
        $stmt->execute();
        $result = $stmt->get_result();
        $properties = $result->fetch_all(MYSQLI_ASSOC);
        
        $stmt->close();
        $conn->close();
        
        return $properties;
    }
    
    // Create new property
    public static function create($data) {
        $conn = getDBConnection();
        setCharset($conn);
        
        $query = "INSERT INTO properties (
                    user_id, property_type_id, title, description, address, 
                    latitude, longitude, price, size, bedrooms, bathrooms, 
                    construction_year, parking_spaces
                 ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param(
            'iisssddddiiii',
            $data['user_id'],
            $data['property_type_id'],
            $data['title'],
            $data['description'],
            $data['address'],
            $data['latitude'],
            $data['longitude'],
            $data['price'],
            $data['size'],
            $data['bedrooms'],
            $data['bathrooms'],
            $data['construction_year'],
            $data['parking_spaces']
        );
        
        $success = $stmt->execute();
        $propertyId = $stmt->insert_id;
        
        $stmt->close();
        $conn->close();
        
        return $success ? $propertyId : false;
    }
    public static function getAllCities() {
    $conn = getDBConnection();
    setCharset($conn);
    
    $query = "SELECT DISTINCT city FROM properties WHERE city IS NOT NULL AND city != '' ORDER BY city ASC";
    $result = $conn->query($query);
    
    $cities = [];
    while ($row = $result->fetch_assoc()) {
        $cities[] = $row['city'];
    }
    
    $conn->close();
    return $cities;
}
}
?>