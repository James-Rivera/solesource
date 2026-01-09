<?php
require_once __DIR__ . '/../asset.php';

$defaultTitle = 'SoleSource | Premium Sneakers';
$pageTitle = isset($title) && trim($title) !== '' ? $title : $defaultTitle;
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
<link rel="icon" type="image/png" href="<?php echo asset('/assets/favicon/favicon-96x96.png'); ?>" sizes="96x96">
<link rel="icon" type="image/svg+xml" href="<?php echo asset('/assets/favicon/favicon.svg'); ?>">
<link rel="shortcut icon" href="<?php echo asset('/assets/favicon/favicon.ico'); ?>">
<link rel="apple-touch-icon" sizes="180x180" href="<?php echo asset('/assets/favicon/apple-touch-icon.png'); ?>">
<meta name="apple-mobile-web-app-title" content="SoleSource">
<link rel="manifest" href="<?php echo asset('/assets/favicon/site.webmanifest'); ?>">
<link rel="stylesheet" href="<?php echo asset('/assets/css/bootstrap-overrides.css'); ?>">
<link rel="stylesheet" href="<?php echo asset('/assets/css/header.css'); ?>">
