/* Shared Scripts */

document.addEventListener('DOMContentLoaded', function() {
    // Sidebar Toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    const closeSidebar = document.getElementById('closeSidebar');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.add('open');
            overlay.classList.add('show');
        });
    }

    if (closeSidebar) {
        closeSidebar.addEventListener('click', function() {
            sidebar.classList.remove('open');
            overlay.classList.remove('show');
        });
    }

    if (overlay) {
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('open');
            overlay.classList.remove('show');
        });
    }

    // Global Search Logic
    const searchInput = document.getElementById('globalSearch');
    const searchResults = document.getElementById('searchResults');
    let searchTimeout = null;

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            clearTimeout(searchTimeout);

            if (query.length < 2) {
                searchResults.style.display = 'none';
                searchResults.innerHTML = '';
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch(`../backend/search_handler.php?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        searchResults.innerHTML = '';
                        if (data.length > 0) {
                            data.forEach(item => {
                                const div = document.createElement('div');
                                div.className = 'search-result-item';
                                div.innerHTML = `<i class="fas fa-user-graduate"></i> ${item.text}`;
                                div.onclick = () => window.location.href = item.url;
                                searchResults.appendChild(div);
                            });
                            searchResults.style.display = 'block';
                        } else {
                            const div = document.createElement('div');
                            div.className = 'search-result-item';
                            div.style.color = '#777';
                            div.style.cursor = 'default';
                            div.textContent = 'No results found.';
                            searchResults.appendChild(div);
                            searchResults.style.display = 'block';
                        }
                    })
                    .catch(err => console.error('Search error:', err));
            }, 300);
        });

        // Close search when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });
    }
});
