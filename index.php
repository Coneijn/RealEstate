<?php
require_once __DIR__ . '/models/Property.php';
require_once __DIR__ . '/models/PropertyType.php';
require_once __DIR__ . '/models/Feature.php';

// Get property types
$propertyTypes = PropertyType::getAll();

// Get all unique cities
$cities = Property::getAllCities();

// Get all features for filters
$features = Feature::getAll();

// Process filters from GET parameters
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

// Get properties based on filters
if ($filters['lat'] && $filters['lng'] && $filters['radius']) {
    $properties = Property::getByRadius($filters['lat'], $filters['lng'], $filters['radius']);
} else {
    $properties = Property::getAll($filters);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<!-- Your CSS -->
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="logo">TP</div>
        <div class="search-bar" style="display:none">
            <form method="GET" action="">
                <input type="text" name="search" placeholder="Search properties..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            </form>
        </div>
        <div class="user-menu">
            <i class="fas fa-user-circle" style="font-size: 1.5rem;"></i>
        </div>
    </div>
    
    <!-- Main Container -->
    <div class="main-container">
        <!-- Filter Panel -->
        <div class="filter-panel">
            <form method="GET" action="" id="filter-form">
                <div class="filter-section">
                    <h3><i class="fas fa-map-marker-alt"></i> Location</h3>
                    <div class="filter-option">
                        <select id="city-filter" name="city">
                            <option value="">All Cities</option>
                            <?php foreach ($cities as $city): ?>
                                <option value="<?= htmlspecialchars($city) ?>"><?= htmlspecialchars($city) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-option">
                        <label for="radius">Radius: <span id="radius-value"><?= $filters['radius'] ?? 5 ?></span> km</label>
                        <input type="range" id="radius" name="radius" min="1" max="20" value="<?= $filters['radius'] ?? 5 ?>">
                    </div>
                </div>
                
                <div class="filter-section">
                    <h3><i class="fas fa-home"></i> Property Type</h3>
                    <div class="filter-option">
                        <select id="type-filter" name="type">
                            <option value="">All types</option>
                            <?php foreach ($propertyTypes as $type): ?>
                                <option value="<?= $type['id'] ?>" <?= ($filters['type'] == $type['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($type['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="filter-section">
                    <h3><i class="fas fa-dollar-sign"></i> Price Range</h3>
                    <div class="filter-option">
                        <label for="min-price">Min: $<span id="min-price-value"><?= isset($filters['min_price']) ? number_format($filters['min_price']) : '100,000' ?></span></label>
                        <input type="range" id="min-price" name="min_price" min="50000" max="1000000" step="50000" 
                               value="<?= $filters['min_price'] ?? 100000 ?>">
                    </div>
                    <div class="filter-option">
                        <label for="max-price">Max: $<span id="max-price-value"><?= isset($filters['max_price']) ? number_format($filters['max_price']) : '500,000' ?></span></label>
                        <input type="range" id="max-price" name="max_price" min="100000" max="5000000" step="50000" 
                               value="<?= $filters['max_price'] ?? 500000 ?>">
                    </div>
                </div>
                
                <div class="filter-section">
                    <h3><i class="fas fa-ruler-combined"></i> Size</h3>
                    <div class="filter-option">
                        <label for="min-size">Min: <span id="min-size-value">50 </span> 
                        sq ft
                        </label>
                        <input type="range" id="min-size" name="min_size" min="20" max="5000" step="10" value="50">
                    </div>
                    <div class="filter-option">
                        <label for="bedrooms">Bedrooms: <span id="bedrooms-value">1+</span></label>
                        <input type="range" id="bedrooms" name="bedrooms" min="1" max="6" value="1">
                    </div>
                </div>
                
                <button type="submit" class="action-button secondary">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
                
                <button type="button" class="action-button" id="add-property">
                    <i class="fas fa-plus"></i> Add Property
                </button>
            </form>
        </div>
        
        <!-- Content Area (Map + Grid) -->
        <div class="content-area">
            <!-- Map -->
            <div class="map-container">
                <div id="map"></div>
            </div>
            
            <!-- Properties Grid -->
            <div class="properties-grid">
                <h3 class="properties-title">Available Properties (<span id="properties-count"><?= count($properties) ?></span>)</h3>
                <div class="properties-list" id="properties-list">
                    <?php if (empty($properties)): ?>
                        <p>No properties found matching your criteria.</p>
                    <?php else: ?>
                        <?php foreach ($properties as $property): ?>
                            <div class="property-card" data-id="<?= $property['id'] ?>">
                                    <div class="property-image" style="background-image: url('<?= $property['main_image'] ?>')">
                                        <div class="property-price">$<?= number_format($property['price']) ?></div>
                                    </div>
                                    <div class="property-details">
                                        <h3 class="property-title"><?= htmlspecialchars($property['title']) ?></h3>
                                        <p class="property-address"><?= htmlspecialchars($property['address']) ?></p>
                                        
                                        <div class="property-features">
                                            <span class="feature"><i class="fas fa-ruler-combined"></i> <?= $property['size'] ?> sq ft</span>
                                            <span class="feature"><i class="fas fa-bed"></i> <?= $property['bedrooms'] ?></span>
                                            <span class="feature"><i class="fas fa-bath"></i> <?= $property['bathrooms'] ?></span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <!-- Add Property Modal -->
<div class="modal" id="add-property-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add New Property</h2>
            <button class="close-modal">&times;</button>
        </div>
        <form id="property-form" action="add_property.php" method="POST">
            <div class="form-group">
                <label for="property-title">Title*</label>
                <input type="text" id="property-title" name="title" required>
            </div>
            
            <div class="form-group">
                <label for="property-type">Property Type*</label>
                <select id="property-type" name="property_type_id" required>
                    <option value="">Select...</option>
                    <?php foreach ($propertyTypes as $type): ?>
                        <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="property-address">Address*</label>
                <input type="text" id="property-address" name="address" required>
            </div>
            
            <div class="form-group">
                <label for="property-address">City*</label>
                <input type="text" id="property-city" name="city" required>
            </div>
            <div class="form-group">
                <label for="property-address">State*</label>
                <input type="text" id="property-state" name="state" required>
            </div>
            <div class="form-group">
                <label for="property-address">Zip code*</label>
                <input type="text" id="property-zip_code" name="zip_code" required>
            </div>
            <div class="form-group">
                <label for="property-latitude">Latitude*</label>
                <input type="number" id="property-latitude" name="latitude" step="0.000001" required>
            </div>
            
            <div class="form-group">
                <label for="property-longitude">Longitude*</label>
                <input type="number" id="property-longitude" name="longitude" step="0.000001" required>
            </div>
            
            <div class="form-group">
                <label for="property-price">Price (USD)*</label>
                <input type="number" id="property-price" name="price" min="0" required>
            </div>
            
            <div class="form-group">
                <label for="property-size">Size (sqft)*</label>
                <input type="number" id="property-size" name="size" min="1" required>
            </div>
            
            <div class="form-group">
                <label for="property-bedrooms">Bedrooms</label>
                <input type="number" id="property-bedrooms" name="bedrooms" min="0" value="1">
            </div>
            
            <div class="form-group">
                <label for="property-bathrooms">Bathrooms</label>
                <input type="number" id="property-bathrooms" name="bathrooms" min="0" value="1">
            </div>
            
            <div class="form-group">
                <label for="property-image">Image URL*</label>
                <input type="url" id="property-image" name="image" required>
                <small class="form-hint">Must be a full URL (e.g., https://example.com/image.jpg)</small>
            </div>
            
            <div class="form-group">
                <label for="property-description">Description</label>
                <textarea id="property-description" name="description"></textarea>
            </div>
            
            <div class="form-actions">
                <button type="button" class="action-button secondary close-modal">Cancel</button>
                <button type="submit" class="action-button">Save Property</button>
            </div>
        </form>
    </div>
</div>
    <!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- Your JavaScript -->
<script src="assets/main.js"></script>
    
</body>
</html>