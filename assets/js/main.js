// GYMZONE - Master JavaScript

document.addEventListener('DOMContentLoaded', () => {
    // 1. Mobile Menu Toggle
    const navToggle = document.getElementById('navToggle');
    const navMenu = document.getElementById('navMenu');

    if (navToggle && navMenu) {
        navToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            const icon = navToggle.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-bars');
                icon.classList.toggle('fa-xmark');
            }
        });
    }

    // 2. Favorite Toggle AJAX
    document.querySelectorAll('.fav-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const gymId = this.getAttribute('data-gym-id');
            if (!gymId) return;

            fetch('toggle-favorite.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `gym_id=${encodeURIComponent(gymId)}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    if (data.favorited) {
                        this.classList.add('active');
                        this.innerHTML = '<i class="fas fa-heart text-orange"></i>';
                    } else {
                        this.classList.remove('active');
                        this.innerHTML = '<i class="far fa-heart"></i>';
                    }
                } else if (data.status === 'unauthorized') {
                    alert('Please log in to save favorite gyms.');
                    window.location.href = 'login.php';
                }
            })
            .catch(err => {
                console.error('Favorite error:', err);
            });
        });
    });

    // 3. Gym Gallery Thumbnail Switcher
    const mainGalleryImg = document.getElementById('mainGalleryImg');
    const galleryThumbs = document.querySelectorAll('.gallery-thumb');

    if (mainGalleryImg && galleryThumbs.length > 0) {
        galleryThumbs.forEach(thumb => {
            thumb.addEventListener('click', function() {
                const newSrc = this.getAttribute('src');
                if (newSrc) {
                    mainGalleryImg.src = newSrc;
                }
            });
        });
    }

    // 4. Booking Date Input Min Constraint
    const visitDateInput = document.getElementById('visit_date');
    if (visitDateInput) {
        const today = new Date().toISOString().split('T')[0];
        visitDateInput.setAttribute('min', today);
    }
});
