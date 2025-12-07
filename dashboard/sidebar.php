<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <nav>
        <ul>
            <li class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
                <a href="dashboard.php">🏠 Home</a>
            </li>
            <li class="<?= $currentPage === 'add_contact.php' ? 'active' : '' ?>">
                <a href="add_contact.php">➕ New Contact</a>
            </li>
            <?php if (isAdmin()): ?>
                <li class="<?= $currentPage === 'users.php' ? 'active' : '' ?>">
                    <a href="users.php">👥 Users</a>
                </li>
                <li class="<?= $currentPage === 'add_user.php' ? 'active' : '' ?>">
                    <a href="add_user.php">➕ Add User</a>
                </li>
            <?php endif; ?>
            <li>
                <a href="../login/logout.php">🚪 Logout</a>
            </li>
        </ul>
    </nav>
</aside>