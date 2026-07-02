<?php
$unread_count = 0;
if (isLoggedIn()) {
    $unread_count = getUnreadCount($_SESSION['user_id']);
}
$site_name = getSetting('site_name');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="description" content="<?php echo SITE_DESC; ?>">
    <title><?php echo $site_name ?: SITE_NAME; ?> - Smart Local Business Directory</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg site-navbar sticky-top shadow-sm" style="background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(255,255,255,0.1);">
    <div class="container">
        <a class="navbar-brand text-white fw-bold d-flex align-items-center gap-2" href="<?php echo SITE_URL; ?>">
            <span class="brand-mark bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="fas fa-store"></i></span>
            <span style="font-family: 'Outfit', sans-serif; letter-spacing: -0.5px;"><?php echo $site_name ?: 'LocalConnect'; ?></span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <i class="fas fa-bars text-white"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-2 ms-lg-4">
                <li class="nav-item">
                    <a class="nav-link text-white-50 hover-white transition" href="<?php echo SITE_URL; ?>">
                        <i class="fas fa-home me-1"></i>Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white-50 hover-white transition" href="<?php echo SITE_URL; ?>businesses.php">
                        <i class="fas fa-compass me-1"></i>Browse
                    </a>
                </li>
                <?php if (isLoggedIn() && isBusinessOwner()): ?>
                <li class="nav-item">
                    <a class="nav-link text-white-50 hover-white transition" href="<?php echo SITE_URL; ?>dashboard/add-business.php">
                        <i class="fas fa-plus-circle me-1"></i>Add Business
                    </a>
                </li>
                <?php endif; ?>
            </ul>

            <form class="site-search me-3 position-relative" action="<?php echo SITE_URL; ?>search.php" method="GET" id="searchForm" style="min-width: 300px;">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3 text-muted" style="border-color: rgba(255,255,255,0.2);">
                        <i class="fas fa-search"></i>
                    </span>
                    <input class="form-control border-start-0 rounded-end-pill shadow-none" type="search" name="q" placeholder="Search businesses, categories..." aria-label="Search" id="searchInput" style="background: white; border-color: rgba(255,255,255,0.2);">
                </div>
                <div id="searchResults" class="site-search-results position-absolute w-100 mt-2 bg-white rounded-3 shadow-lg border-0 overflow-hidden" style="display: none; z-index: 1050; max-height: 400px; overflow-y: auto;"></div>
            </form>

            <ul class="navbar-nav ms-auto gap-2">
                <?php if(isLoggedIn()): ?>
                    <?php if(isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link text-white-50 hover-white transition" href="<?php echo SITE_URL; ?>admin/">
                            <i class="fas fa-cog me-1"></i>Admin
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white d-flex align-items-center gap-2" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 14px;">
                                <?php echo substr($_SESSION['fullname'], 0, 1); ?>
                            </div>
                            <?php echo $_SESSION['fullname']; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 rounded-3">
                            <li><a class="dropdown-item py-2" href="<?php echo SITE_URL; ?>dashboard/">
                                <i class="fas fa-tachometer-alt me-2 text-muted"></i>Dashboard
                            </a></li>
                            <li><a class="dropdown-item py-2" href="<?php echo SITE_URL; ?>dashboard/my-businesses.php">
                                <i class="fas fa-store me-2 text-muted"></i>My Businesses
                            </a></li>
                            <li><a class="dropdown-item py-2" href="<?php echo SITE_URL; ?>dashboard/favorites.php">
                                <i class="fas fa-heart me-2 text-muted"></i>Favorites
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item py-2 text-danger" href="<?php echo SITE_URL; ?>logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link text-white-50 hover-white transition" href="<?php echo SITE_URL; ?>login.php">
                            <i class="fas fa-sign-in-alt me-1"></i>Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary text-white px-3 rounded-pill" href="<?php echo SITE_URL; ?>register.php">
                            <i class="fas fa-user-plus me-1"></i>Register
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<script>
const searchInput = document.getElementById('searchInput');
const resultsDiv = document.getElementById('searchResults');

if (searchInput && resultsDiv) {
    searchInput.addEventListener('keyup', function () {
        const query = this.value.trim();

        if (query.length < 2) {
            resultsDiv.style.display = 'none';
            resultsDiv.innerHTML = '';
            return;
        }

        fetch('<?php echo SITE_URL; ?>ajax/search.php?q=' + encodeURIComponent(query))
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    let html = '<div class="list-group list-group-flush">';
                    data.forEach(item => {
                        html += `<a href="business-detail.php?id=${item.biz_id}" class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between gap-3">
                                        <strong>${item.biz_name}</strong>
                                        <small class="text-muted">${item.cat_name}</small>
                                    </div>
                                    <small class="text-muted">${item.city}</small>
                                </a>`;
                    });
                    html += '</div>';
                    resultsDiv.innerHTML = html;
                    resultsDiv.style.display = 'block';
                } else {
                    resultsDiv.innerHTML = '<div class="p-3 text-muted">No results found</div>';
                    resultsDiv.style.display = 'block';
                }
            })
            .catch(() => {
                resultsDiv.innerHTML = '<div class="p-3 text-muted">Search is unavailable right now.</div>';
                resultsDiv.style.display = 'block';
            });
    });

    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !resultsDiv.contains(e.target)) {
            resultsDiv.style.display = 'none';
        }
    });
}
</script>