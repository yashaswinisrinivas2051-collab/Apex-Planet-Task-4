<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="BlogCRUD - A powerful blog management system. Create, manage, and organize your blog posts with ease. Built with PHP, MySQL, and Bootstrap 5.">
    <meta name="keywords" content="blog, CMS, content management, PHP, MySQL, blog platform">
    <meta name="author" content="BlogCRUD">
    <meta name="theme-color" content="#ea580c">

    <!-- CSRF Token for JavaScript (used by delete confirmation) -->
    <meta name="csrf-token" content="<?php echo get_csrf_token(); ?>">

    <!-- Open Graph / Social Meta -->
    <meta property="og:title" content="BlogCRUD - Blog Management System">
    <meta property="og:description" content="Create, manage, and organize your blog posts with ease.">
    <meta property="og:type" content="website">
    <meta property="og:image" content="/assets/images/og-image.png">

    <title>BlogCRUD - <?php echo htmlspecialchars($pageTitle ?? 'Blog Management System'); ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom Styles (v3.0 - Security Enhanced) -->
    <link rel="stylesheet" href="/assets/css/style.css?v=3.0">

    <!-- Favicon emoji favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📝</text></svg>">
</head>
<body>
