<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = $pageTitle ?? 'Cowboy Properties';
$homeLink = $homeLink ?? 'index.php';
$navLinks = $navLinks ?? [
    ['label' => 'Dashboard', 'href' => 'index.php'],
    ['label' => 'Logout', 'href' => 'logout.php'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="assets/app.css">
</head>
<body>
<div class="page">
    <header class="navbar">
        <a class="brand" href="<?php echo htmlspecialchars($homeLink); ?>">
            <div class="logo">CP</div>
            <strong>Cowboy Properties</strong>
        </a>

        <nav class="center-nav">
            <?php foreach ($navLinks as $link): ?>
                <a href="<?php echo htmlspecialchars($link['href']); ?>">
                    <?php echo htmlspecialchars($link['label']); ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="nav-spacer"></div>
    </header>

    <main>
        <div class="wrap">