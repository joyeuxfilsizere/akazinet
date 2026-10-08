            </main>
        </div>
    </div>

    <script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('-translate-x-full');
    }
    document.querySelectorAll('.admin-nav-link').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 1024) toggleSidebar();
        });
    });
    </script>
</body>
</html>
