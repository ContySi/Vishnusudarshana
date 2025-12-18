<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mobile Service Platform</title>
    <link rel="icon" type="image/png" href="<?php echo (strpos($_SERVER['PHP_SELF'], '/forms/') === false ? 'assets/images/logo/logo-icon.png' : '../assets/images/logo/logo-icon.png'); ?>">
    <link rel="stylesheet" href="<?php echo (strpos($_SERVER['PHP_SELF'], '/forms/') === false ? 'assets/css/style.css' : '../assets/css/style.css'); ?>">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="header-top">
                <a href="index.php" class="logo-link" aria-label="Vishnusudarshana Home">
                    <img src="assets/images/logo/logomain.png" alt="Vishnusudarshana Logo" class="logo-img" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.style.display='block';">
                    <span class="logo-text" style="display:none;">Vishnusudarshana</span>
                </a>
            </div>
            <nav class="navbar desktop-nav">
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="services.php">Services</a></li>
                    <li><a href="reels.php">Reels</a></li>
                    <li><a href="track.php">Track</a></li>
                </ul>
            </nav>
        </div>
    </header>
