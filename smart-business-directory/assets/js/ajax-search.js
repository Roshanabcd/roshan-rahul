// AJAX Live Search
let searchTimeout;
let searchInput = document.getElementById('searchInput');
let resultsDiv = document.getElementById('searchResults');

if (searchInput) {
    searchInput.addEventListener('keyup', function() {
        clearTimeout(searchTimeout);
        let query = this.value.trim();
        
        if (query.length < 2) {
            if (resultsDiv) {
                resultsDiv.innerHTML = '';
                resultsDiv.style.display = 'none';
            }
            return;
        }
        
        searchTimeout = setTimeout(function() {
            fetch(`ajax/search.php?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    if (resultsDiv) {
                        if (data.length > 0) {
                            let html = '<div class="list-group list-group-flush shadow-sm rounded">';
                            data.forEach(item => {
                                html += `<a href="business-detail.php?id=${item.biz_id}" class="list-group-item list-group-item-action">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>${escapeHtml(item.biz_name)}</strong>
                                                    <br>
                                                    <small class="text-muted">${escapeHtml(item.cat_name)}</small>
                                                </div>
                                                <small><i class="fas fa-map-marker-alt"></i> ${escapeHtml(item.city)}</small>
                                            </div>
                                        </a>`;
                            });
                            html += '</div>';
                            resultsDiv.innerHTML = html;
                            resultsDiv.style.display = 'block';
                        } else {
                            resultsDiv.innerHTML = '<div class="list-group-item text-muted">No results found</div>';
                            resultsDiv.style.display = 'block';
                        }
                    }
                })
                .catch(error => console.error('Error:', error));
        }, 300);
    });
    
    // Hide results when clicking outside
    document.addEventListener('click', function(e) {
        if (resultsDiv && searchInput && !searchInput.contains(e.target) && !resultsDiv.contains(e.target)) {
            resultsDiv.style.display = 'none';
        }
    });
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Category filter with AJAX
function filterByCategory(categorySlug) {
    window.location.href = `businesses.php?category=${categorySlug}`;
}

// Location based search
function searchNearby() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            window.location.href = `businesses.php?lat=${lat}&lng=${lng}&nearby=1`;
        }, function(error) {
            alert('Unable to get your location. Please enter location manually.');
        });
    } else {
        alert('Geolocation is not supported by your browser.');
    }
}