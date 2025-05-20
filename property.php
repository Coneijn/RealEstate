<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/models/Property.php';
require_once __DIR__ . '/models/User.php';

// Get property ID from URL
$propertyId = $_GET['id'] ?? null;

if (!$propertyId) {
    header('Location: index.php');
    exit();
}

// Get property details
$property = Property::getById($propertyId);

if (!$property) {
    header('Location: index.php?error=Property not found');
    exit();
}

// Handle offer submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_offer'])) {
    $userId = 1; // In real app, get from session
    $amount = floatval($_POST['amount']);
    $message = htmlspecialchars(strip_tags($_POST['message']));
    
    // Validate offer
    if ($amount <= 0) {
        $error = "Offer amount must be positive";
    } else {
        // Save offer to database
        $conn = getDBConnection();
        $query = "INSERT INTO offers (property_id, user_id, amount, message) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('iids', $propertyId, $userId, $amount, $message);
        
        if ($stmt->execute()) {
            $success = "Your offer has been submitted successfully!";
        } else {
            $error = "Failed to submit offer. Please try again.";
        }
        
        $stmt->close();
        $conn->close();
    }
}

// Include header
include __DIR__ . '/includes/header.php';
?>

<div class="property-detail-container">
    <div class="property-gallery">
        <?php if (!empty($property['images'])): ?>
            <div class="main-image">
                <img src="<?= htmlspecialchars($property['images'][0]['url']) ?>" alt="<?= htmlspecialchars($property['title']) ?>">
            </div>
            <div class="thumbnail-container">
                <?php foreach ($property['images'] as $image): ?>
                    <div class="thumbnail">
                        <img src="<?= htmlspecialchars($image['url']) ?>" alt="<?= htmlspecialchars($property['title']) ?>">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="property-info">
        <h1><?= htmlspecialchars($property['title']) ?></h1>
        <div class="property-meta">
            <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($property['address']) ?></span>
            <span><i class="fas fa-dollar-sign"></i> <?= number_format($property['price']) ?></span>
            <span><i class="fas fa-ruler-combined"></i> <?= number_format($property['size']) ?> sq ft</span>
            <span><i class="fas fa-bed"></i> <?= $property['bedrooms'] ?> beds</span>
            <span><i class="fas fa-bath"></i> <?= $property['bathrooms'] ?> baths</span>
        </div>
        
        <div class="property-description">
            <h2>Description</h2>
            <p><?= nl2br(htmlspecialchars($property['description'])) ?></p>
        </div>
        
        <div class="property-features">
            <h2>Features</h2>
            <ul>
                <?php foreach ($property['features'] as $feature): ?>
                    <li><i class="<?= htmlspecialchars($feature['icon']) ?>"></i> <?= htmlspecialchars($feature['name']) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    
    <div class="offer-section">
        <h2>Make an Offer</h2>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST" action="property.php?id=<?= $propertyId ?>">
            <div class="form-group">
                <label for="amount">Your Offer ($)</label>
                <input type="number" id="amount" name="amount" min="1" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label for="message">Message (Optional)</label>
                <textarea id="message" name="message" rows="4"></textarea>
            </div>
            
            <button type="submit" name="submit_offer" class="btn btn-primary">Submit Offer</button>
        </form>
    </div>
</div>

<?php
// Include footer
include __DIR__ . '/includes/footer.php';
?>