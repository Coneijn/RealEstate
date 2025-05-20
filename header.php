<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($property) ? htmlspecialchars($property['title']) : 'Property Details' ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <a href="/" class="logo">RealEstate</a>
            <nav class="main-nav">
                <a href="/">Home</a>
                <a href="/properties.php">All Properties</a>
            </nav>
        </div>
    </header>
    <main class="container">