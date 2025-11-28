</main>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script>
    // Mobile sidebar toggle
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.querySelector('.sidebar-backdrop');

        if (sidebar && backdrop) {
            sidebar.classList.toggle('show');
            backdrop.classList.toggle('show');
        }
    }

    // Close sidebar when clicking a link on mobile
    document.addEventListener('DOMContentLoaded', function() {
        const sidebarLinks = document.querySelectorAll('.sidebar .nav-link');
        const isMobile = window.innerWidth < 768;

        if (isMobile) {
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function() {
                    toggleSidebar();
                });
            });
        }
    });

    // Theme toggle
    function toggleTheme() {
        const html = document.documentElement;
        const currentTheme = html.getAttribute('data-bs-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-bs-theme', newTheme);
        document.cookie = `theme=${newTheme}; path=/; max-age=31536000`;
        updateThemeIcon(newTheme);
    }

    function updateThemeIcon(theme) {
        const icon = document.getElementById('themeIcon');
        if (icon) {
            icon.className = theme === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars';
        }
    }

    // Initialize theme icon
    document.addEventListener('DOMContentLoaded', function() {
        const theme = document.documentElement.getAttribute('data-bs-theme');
        updateThemeIcon(theme);
    });

    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 3000);

    // Handle window resize
    window.addEventListener('resize', function() {
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.querySelector('.sidebar-backdrop');

        if (window.innerWidth >= 768) {
            // Desktop - ensure sidebar and backdrop are reset
            if (sidebar) sidebar.classList.remove('show');
            if (backdrop) backdrop.classList.remove('show');
        }
    });
</script>
</body>

</html>