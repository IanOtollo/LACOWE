    </main>
    
    <!-- PWA Install Script -->
    <script src="assets/js/pwa-install.js"></script>
    
    <script>
        // Auto-dismiss alerts
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
        
        // Mobile menu toggle
        const menuToggle = document.getElementById('mobileMenuToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobileOverlay');
        
        if (menuToggle) {
            menuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
            });
            
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            });
        }
        
        // Haptic feedback
        document.querySelectorAll('.haptic-feedback, .btn').forEach(el => {
            el.addEventListener('click', () => {
                if (navigator.vibrate) navigator.vibrate(10);
            });
        });
        
        // Offline detection
        const offlineIndicator = document.getElementById('offlineIndicator');
        
        window.addEventListener('online', () => {
            offlineIndicator.classList.remove('show');
        });
        
        window.addEventListener('offline', () => {
            offlineIndicator.classList.add('show');
        });
        
        // Check initial state
        if (!navigator.onLine) {
            offlineIndicator.classList.add('show');
        }
    </script>
</body>
</html>
