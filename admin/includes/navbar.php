<body onload="applyTheme()">
<div class="header">
    <h2>🛡️ Admin Dashboard</h2>
    <div class="profile-section" onclick="toggleProfileDropdown()">
    <img src="uploads/admin_photos/<?= htmlspecialchars($admin['Photo'] ?? 'default.png') ?>" alt="Admin Photo" class="profile-img mb-3"> 
        <div class="dropdown" id="profileDropdown">
            <a href="plugin/admin_profile.php">👤 My Profile</a>
            <a href="plugin/update_admin.php">✏️ Edit Profile</a>
            <a href="plugin/admin_logout.php" style="color: red;">🚪 Logout</a>
        </div>
    </div>
    <button class="dark-mode-toggle" onclick="toggleDarkMode()">🌙</button>
</div>