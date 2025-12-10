/* Shared Scripts */

// Sidebar Toggle
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    
    if (sidebar.classList.contains('open')) {
        sidebar.classList.remove('open');
        overlay.classList.remove('show');
    } else {
        sidebar.classList.add('open');
        overlay.classList.add('show');
    }
}

// Global Search (AJAX)
let searchTimeout = null;

function handleGlobalSearch(query) {
    clearTimeout(searchTimeout);
    const resultsDiv = document.getElementById('globalSearchResults');
    
    if (query.length < 2) {
        resultsDiv.style.display = 'none';
        resultsDiv.innerHTML = '';
        return;
    }

    // Debounce
    searchTimeout = setTimeout(() => {
        fetch('search_handler.php?q=' + encodeURIComponent(query))
            .then(response => response.json())
            .then(data => {
                resultsDiv.innerHTML = '';
                if (data.length > 0) {
                    data.forEach(item => {
                        const div = document.createElement('div');
                        div.className = 'search-result-item';
                        div.textContent = item.text;
                        div.onclick = () => window.location.href = item.url;
                        resultsDiv.appendChild(div);
                    });
                    resultsDiv.style.display = 'block';
                } else {
                    const div = document.createElement('div');
                    div.className = 'search-result-item';
                    div.style.color = '#777';
                    div.textContent = 'No results found.';
                    resultsDiv.appendChild(div);
                    resultsDiv.style.display = 'block';
                }
            })
            .catch(err => {
                console.error('Search error:', err);
            });
    }, 300);
}

// Close search when clicking outside
document.addEventListener('click', function(e) {
    const searchBox = document.getElementById('headerSearchBox');
    const resultsDiv = document.getElementById('globalSearchResults');
    
    if (searchBox && !searchBox.contains(e.target)) {
        if (resultsDiv) resultsDiv.style.display = 'none';
    }
});

