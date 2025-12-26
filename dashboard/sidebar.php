<?php
//You will probably want to change the users and add_contact links here if you add another file
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <nav>
        <ul>
            <li class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
                <a href="../dashboard/dashboard.php">🏠 Home</a>
            </li>
            <li class="<?= $currentPage === 'addContact.php' ? 'active' : '' ?>">
                <a href="../contacts/addContact.php">➕ New Contact</a><!-- Right here-->
            </li>
            <?php if (isAdmin()): ?>
                <li class="<?= $currentPage === 'users.php' ? 'active' : '' ?>">
                    <a href="users.php">👥 Users</a><!-- Right here-->
                </li>
                <li class="<?= $currentPage === 'addUser.php' ? 'active' : '' ?>">
                    <a href="../users/addUser.php">➕ Add User</a>
                </li>
            <?php endif; ?>
            <li>
                <a href="../login/logout.php">🚪 Logout</a>
            </li>
        </ul>
    </nav>
</aside>