<header class="main-header">
    <div class="header-left">
        <h1>🐬 Dolphin CRM</h1>
    </div>
    <div class="header-right">
        <span class="user-info">
            Welcome, <?= htmlspecialchars($_SESSION['firstname'] . ' ' . $_SESSION['lastname']) ?> 
            (<?= htmlspecialchars($_SESSION['role']) ?>)
        </span>
    </div>
</header>