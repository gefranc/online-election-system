// Theme Management
function toggleDarkMode() {
    const html = document.documentElement;
    const currentTheme = html.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    html.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    
    // Update icon
    const icon = document.querySelector('.dark-mode-toggle i');
    icon.className = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
}

function applyTheme() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    
    // Set correct icon
    const icon = document.querySelector('.dark-mode-toggle i');
    icon.className = savedTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
}

// Profile Dropdown
function toggleProfileDropdown() {
    const dropdown = document.getElementById('profileDropdown');
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('profileDropdown');
    const profileSection = document.querySelector('.profile-section');
    if (!profileSection.contains(event.target)) {
        dropdown.style.display = 'none';
    }
});

// Mobile Menu Toggle
function toggleMobileMenu() {
    document.querySelector('.sidebar').classList.toggle('active');
}

// Initialize on load
document.addEventListener('DOMContentLoaded', function() {
    applyTheme();
    
    // Add mobile menu button if needed
    if (window.innerWidth <= 992) {
        const header = document.querySelector('.header');
        const toggleBtn = document.createElement('button');
        toggleBtn.className = 'mobile-menu-toggle';
        toggleBtn.innerHTML = '<i class="fas fa-bars"></i>';
        toggleBtn.onclick = toggleMobileMenu;
        header.prepend(toggleBtn);
    }
});

// Window resize handler
window.addEventListener('resize', function() {
    if (window.innerWidth > 992) {
        document.querySelector('.sidebar').classList.remove('active');
    }
});