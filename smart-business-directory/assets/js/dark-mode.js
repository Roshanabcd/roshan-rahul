// Dark Mode Toggle Functionality
const darkModeToggle = document.getElementById('darkModeToggle');
const body = document.body;

// Check for saved preference
const darkMode = localStorage.getItem('darkMode');

if (darkMode === 'enabled') {
    body.classList.add('dark-mode');
    if (darkModeToggle) {
        darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
    }
}

if (darkModeToggle) {
    darkModeToggle.addEventListener('click', () => {
        body.classList.toggle('dark-mode');
        
        if (body.classList.contains('dark-mode')) {
            localStorage.setItem('darkMode', 'enabled');
            darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
        } else {
            localStorage.setItem('darkMode', null);
            darkModeToggle.innerHTML = '<i class="fas fa-moon"></i>';
        }
    });
}

// Auto-detect system preference
const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');
if (prefersDarkScheme.matches && !localStorage.getItem('darkMode')) {
    body.classList.add('dark-mode');
    if (darkModeToggle) {
        darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
    }
}