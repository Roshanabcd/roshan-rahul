$(document).ready(function() {
    
    // Initialize Star Rating for Display
    $('.star-rating-display').each(function() {
        var rating = $(this).data('rating');
        var $this = $(this);
        $this.empty();
        for (var i = 1; i <= 5; i++) {
            if (i <= Math.round(rating)) {
                $this.append('<i class="fas fa-star active"></i>');
            } else {
                $this.append('<i class="fas fa-star"></i>');
            }
        }
    });
    
    // Initialize RateYo for Rating Input
    if ($('#rateYo').length) {
        $('#rateYo').rateYo({
            rating: 0,
            fullStar: true,
            starWidth: '25px',
            onSet: function(rating) {
                $('#rating_value').val(rating);
            }
        });
    }
    
    // File Input Preview
    $('#noteFile').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        if (fileName) {
            $('#file-name-display').text('Selected: ' + fileName).addClass('text-success');
        } else {
            $('#file-name-display').text('').removeClass('text-success');
        }
    });
    
    // Search Autocomplete
    $('#searchInput').on('keyup', function() {
        var query = $(this).val();
        if (query.length >= 2) {
            // Debounce for better performance
            clearTimeout($(this).data('timeout'));
            $(this).data('timeout', setTimeout(function() {
                // Optional: AJAX live search
            }, 500));
        }
    });
    
    // Confirm Delete
    $('.delete-confirm').on('click', function(e) {
        if (!confirm('Are you sure you want to delete this? This action cannot be undone.')) {
            e.preventDefault();
        }
    });
    
    // Toast Notification Auto-hide
    setTimeout(function() {
        $('.alert-dismissible').fadeOut('slow');
    }, 5000);
    
    // Smooth Scroll
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        var target = $(this.hash);
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 70
            }, 500);
        }
    });
    
    // Back to Top Button
    $(window).scroll(function() {
        if ($(this).scrollTop() > 300) {
            $('#back-to-top').fadeIn();
        } else {
            $('#back-to-top').fadeOut();
        }
    });
});