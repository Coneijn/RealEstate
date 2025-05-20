# Property Listing Platform - README

## Overview
This is a real estate property listing platform that allows users to:
- View properties on an interactive map
- Filter properties by various criteria
- Add new property listings
- Search for properties by location, price, and features

## Features

### Core Functionality
- **Interactive Map**: View all properties on a Leaflet.js map
- **Property Grid**: Browse listings in a responsive card grid
- **Advanced Filters**: Filter by:
  - Property type (House, Apartment, etc.)
  - Price range
  - Square footage
  - Number of bedrooms/bathrooms
  - Location (city/state)
- **Search**: Full-text search across property details
- **Add Listings**: Form for adding new properties with:
  - Manual image URL input
  - Direct coordinate entry (latitude/longitude)

### Technical Features
- PHP backend with MySQL database
- Responsive design (works on mobile and desktop)
- Form validation (client and server side)
- Secure file handling
- Spatial search functionality

## Installation

### Requirements
- PHP 7.4+
- MySQL 5.7+ (or MariaDB equivalent)
- Web server (Apache/Nginx)
- Composer (for dependencies)

### Setup Steps

1. **Clone the repository**
 

2. **Set up database**
   - Create a new MySQL database

3. **Configure environment**
   - Copy `.env.example` to `.env`
   - Update with your database credentials:
     ```
     DB_HOST=localhost
     DB_NAME=your_database
     DB_USER=your_username
     DB_PASS=your_password
     ```

4. **Install dependencies**
   ```bash
   composer install
   ```

5. **Configure web server**
   - Point your web server to the `public` directory
   - Ensure mod_rewrite is enabled (for Apache)

6. **Set permissions**
   ```bash
   chmod -R 755 storage
   chmod -R 755 public/uploads
   ```

## Database Structure

Key tables:
- `properties` - Main property listings
- `property_types` - Types of properties (House, Apartment, etc.)
- `property_images` - Property photos
- `features` - Amenities and features
- `property_features` - Junction table for property features
- `users` - User accounts

## API Endpoints

- `GET /api/properties` - List properties with filters
- `POST /api/properties` - Create new property
- `GET /api/property-types` - Get all property types
- `GET /api/cities` - Get all available cities

## Usage

### Adding a New Property
1. Click "Add Property" button
2. Fill in all required fields:
   - Title
   - Property type
   - Address
   - Coordinates (latitude/longitude)
   - Price
   - Square footage
   - Image URL
3. Click "Save Property"

### Searching/Filters
1. Use the search bar to find properties by keywords
2. Adjust filters in the sidebar:
   - Price range slider
   - Size range
   - Bedrooms/bathrooms
   - City/location
3. Results update in real-time on map and grid

## Troubleshooting

### Common Issues

**500 Server Errors**
- Check PHP error logs
- Verify database credentials in `.env`
- Ensure all database tables exist

**Map Not Loading**
- Check Leaflet JS/CSS is loaded
- Verify you have internet connection (for tile layers)
- Ensure properties have valid coordinates

**Image Upload Issues**
- Verify uploads directory has write permissions
- Check PHP file upload settings in `php.ini`
- Ensure image URLs are valid
