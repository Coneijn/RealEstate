<?php
require_once __DIR__ . '/../config.php';

class PropertyType {
    public static function getAll() {
        $conn = getDBConnection();
        setCharset($conn);
        
        $query = "SELECT * FROM property_types";
        $result = $conn->query($query);
        $types = $result->fetch_all(MYSQLI_ASSOC);
        
        $conn->close();
        return $types;
    }
}
?>