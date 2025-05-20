document.addEventListener('DOMContentLoaded', function() {
    // Initialize the map
    const map = L.map('map').setView([35.9382, -77.7905], 12);
    
    // Add base map layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    
    // Custom property icon
    const propertyIcon = L.icon({
        iconUrl: 'https://cdn-icons-png.flaticon.com/512/447/447031.png',
        iconSize: [32, 32],
        iconAnchor: [16, 32],
        popupAnchor: [0, -32]
    });
    
    // Store markers for later reference
    const markers = {};
    
    // Function to add properties to map
    function addPropertiesToMap(properties) {
        // Clear existing markers
        Object.values(markers).forEach(marker => map.removeLayer(marker));
        
        // Add new markers
        properties.forEach(property => {
            const marker = L.marker(
                [property.latitude, property.longitude], 
                { icon: propertyIcon }
            ).addTo(map)
            .bindPopup(`
                <b>${escapeHtml(property.title)}</b><br>
                <i>$${numberWithCommas(property.price)}</i><br>
                ${escapeHtml(property.address)}<br>
                ${property.size} m² | ${property.bedrooms} beds | ${property.bathrooms} baths
            `);
            
            markers[property.id] = marker;
            
            // Center map when clicking on property card
            const card = document.querySelector(`.property-card[data-id="${property.id}"]`);
            if (card) {
                card.addEventListener('click', () => {
                    map.setView([property.latitude, property.longitude], 15);
                    marker.openPopup();
                });
            }
        });
        
        // Fit map to show all markers if there are properties
        if (properties.length > 0) {
            const markerGroup = new L.featureGroup(Object.values(markers));
            map.fitBounds(markerGroup.getBounds().pad(0.2));
        }
    }
    
    // Load properties from server
    function loadProperties(filters = {}) {
        showLoading();
        
        // Convert filters to URL parameters
        const params = new URLSearchParams();
        for (const key in filters) {
            if (filters[key] !== null && filters[key] !== undefined) {
                params.append(key, filters[key]);
            }
        }
        
        fetch(`api/properties.php?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                updatePropertiesGrid(data);
                addPropertiesToMap(data);
                hideLoading();
            })
            .catch(error => {
                console.error('Error loading properties:', error);
                hideLoading();
            });
    }
    
    // Update properties grid
    function updatePropertiesGrid(properties) {
        const propertiesList = document.getElementById('properties-list');
        const propertiesCount = document.getElementById('properties-count');
        
        propertiesCount.textContent = properties.length;
        
        if (properties.length === 0) {
            propertiesList.innerHTML = '<p class="no-results">No properties found matching your criteria.</p>';
            return;
        }
        
        propertiesList.innerHTML = properties.map(property => `
            <div class="property-card" data-id="${property.id}">
                <div class="property-image" style="background-image: url('${property.main_image || 'https://via.placeholder.com/300x200?text=Property'}')">
                    <div class="property-price">$${numberWithCommas(property.price)}</div>
                </div>
                <div class="property-details">
                    <h3 class="property-title">${escapeHtml(property.title)}</h3>
                    <p class="property-address">${escapeHtml(property.address)}</p>
                    <div class="property-features">
                        <span class="feature"><i class="fas fa-ruler-combined"></i> ${property.size} m²</span>
                        <span class="feature"><i class="fas fa-bed"></i> ${property.bedrooms}</span>
                        <span class="feature"><i class="fas fa-bath"></i> ${property.bathrooms}</span>
                    </div>
                </div>
            </div>
        `).join('');
    }
    
    // Helper functions
    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
    
    function numberWithCommas(x) {
        return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
    
    function showLoading() {
        document.getElementById('loading-spinner').style.display = 'flex';
    }
    
    function hideLoading() {
        document.getElementById('loading-spinner').style.display = 'none';
    }
    
    // Initialize filter form
    const filterForm = document.getElementById('filter-form');
    if (filterForm) {
        // Update range value displays
        document.getElementById('min-price').addEventListener('input', function() {
            document.getElementById('min-price-value').textContent = 
                numberWithCommas(this.value);
        });
        
        document.getElementById('max-price').addEventListener('input', function() {
            document.getElementById('max-price-value').textContent = 
                numberWithCommas(this.value);
        });
        
        document.getElementById('min-size').addEventListener('input', function() {
            document.getElementById('min-size-value').textContent = this.value;
        });
        
        document.getElementById('bedrooms').addEventListener('input', function() {
            document.getElementById('bedrooms-value').textContent = 
                this.value === '6' ? '6+' : this.value;
        });
        
        document.getElementById('radius').addEventListener('input', function() {
            document.getElementById('radius-value').textContent = this.value;
        });
        
        // Handle form submission
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(filterForm);
            const filters = {
                type: formData.get('type'),
                min_price: formData.get('min_price'),
                max_price: formData.get('max_price'),
                min_size: formData.get('min_size'),
                bedrooms: formData.get('bedrooms'),
                radius: formData.get('radius'),
                location: formData.get('location')
            };
            
            loadProperties(filters);
        });
    }
    
    // Modal functionality
    document.getElementById('add-property')?.addEventListener('click', function() {
        document.getElementById('add-property-modal').style.display = 'flex';
    });
    
    document.querySelectorAll('.close-modal').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('add-property-modal').style.display = 'none';
        });
    });
    
    // Handle property form submission
    document.getElementById('property-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        showLoading();
        
        const formData = new FormData(this);
        
        fetch('api/add_property.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Property added successfully!');
                document.getElementById('add-property-modal').style.display = 'none';
                this.reset();
                loadProperties(); // Refresh the properties list
            } else {
                alert('Error: ' + (data.message || 'Failed to add property'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while adding the property');
        })
        .finally(() => {
            hideLoading();
        });
    });
    
    // Initial load of properties
    loadProperties();
});

// Add loading spinner to HTML (dynamically if not in HTML)
const loadingSpinner = document.createElement('div');
loadingSpinner.id = 'loading-spinner';
loadingSpinner.className = 'loading-spinner';
loadingSpinner.innerHTML = '<div class="spinner"></div>';
document.body.appendChild(loadingSpinner);