<?php
require_once '../database/config.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Dolphin CRM</title>
    <link rel="stylesheet" href="../styles.css">
    <script src="../ajax.js"></script>
</head>
<body>
    <?php include 'header.php'; ?>    
    <div class="main-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="content">
            <div class="content-header">
                <h2>Dashboard</h2>
                <a href="add_contact.php" class="btn-primary">+ Add Contact</a>
            </div>
            
            <div class="filter-bar">
                <div class="filter-icon">⚙️</div>
                <a href="#" class="filter-btn active" data-filter="all">All Contacts</a>
                <a href="#" class="filter-btn" data-filter="sales">Sales Leads</a>
                <a href="#" class="filter-btn" data-filter="support">Support</a>
                <a href="#" class="filter-btn" data-filter="assigned">Assigned to me</a>
            </div>
            
            <div id="contacts-table">
                <div class="loading">Loading contacts...</div>
            </div>
        </div>
    </div>
</body>
</html>