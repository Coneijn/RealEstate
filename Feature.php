<?php
require_once __DIR__ . '/../config.php';

class Feature {
    public static function getAll() {
        $conn = getDBConnection();
        setCharset($conn);
        
        $query = "SELECT * FROM features";
        $result = $conn->query($query);
        $features = $result->fetch_all(MYSQLI_ASSOC);
        
        $conn->close();
        return $features;
    }
}
?>