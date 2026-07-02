// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        document.querySelectorAll('.alert').forEach(function(alert) {
            let bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
});

// Confirm delete function
function confirmDelete(message) {
    return confirm(message || 'Are you sure you want to delete this item?');
}

// Image preview before upload
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        let reader = new FileReader();
        reader.onload = function(e) {
            let preview = document.getElementById(previewId);
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Phone number formatting
function formatPhoneNumber(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length > 10) {
        value = value.slice(0, 10);
    }
    if (value.length > 6) {
        input.value = value.slice(0, 3) + '-' + value.slice(3, 6) + '-' + value.slice(6);
    } else if (value.length > 3) {
        input.value = value.slice(0, 3) + '-' + value.slice(3);
    } else {
        input.value = value;
    }
}

// Toggle password visibility
function togglePassword(inputId, iconId) {
    let input = document.getElementById(inputId);
    let icon = document.getElementById(iconId);
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Copy to clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Copied to clipboard!');
    });
}

// Get user location
function getUserLocation(callback) {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            callback({
                lat: position.coords.latitude,
                lng: position.coords.longitude
            });
        }, function(error) {
            console.error('Geolocation error:', error);
            callback(null);
        });
    } else {
        callback(null);
    }
}

// Load more content (infinite scroll)
let loading = false;
function loadMore(loadMoreUrl, containerId, page) {
    if (loading) return;
    loading = true;
    
    fetch(loadMoreUrl + '?page=' + page)
        .then(response => response.json())
        .then(data => {
            if (data.html) {
                document.getElementById(containerId).insertAdjacentHTML('beforeend', data.html);
            }
            loading = false;
            if (!data.has_more) {
                document.getElementById('loadMoreBtn').style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error loading more:', error);
            loading = false;
        });
}

// Mark notification as read
function markAsRead(notificationId, element) {
    fetch('/api/notifications/mark-read.php?id=' + notificationId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                element.closest('.notification-item').classList.add('opacity-50');
            }
        });
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Handle helpful button clicks
document.querySelectorAll('.helpful-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        let reviewId = this.dataset.review;
        fetch('/ajax/mark-helpful.php?review=' + reviewId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let countSpan = this.querySelector('.helpful-count');
                    countSpan.textContent = parseInt(countSpan.textContent) + 1;
                    this.disabled = true;
                    this.innerHTML = '<i class="fas fa-thumbs-up"></i> Helpful (' + countSpan.textContent + ')';
                }
            });
    });
});