// Gallery Page Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize gallery
    initGallery();
    
    // Initialize lightbox
    initLightbox();
    
    // Initialize filtering
    initFiltering();
    
    // Initialize lazy loading
    initLazyLoading();
    
    // Initialize video placeholder
    initVideoPlaceholder();
});

function initGallery() {
    // Gallery data
    const galleryItems = [
        {
            id: 0,
            category: 'food',
            image: 'images/gallery/food/shrimp-grits.jpg',
            title: 'Shrimp & Grits',
            description: 'Our signature dish with Weisenberger grits, sautéed shrimp in red-eye gravy'
        },
        {
            id: 1,
            category: 'food',
            image: 'images/gallery/food/steak.jpg',
            title: 'New York Strip',
            description: 'Pan-seared with tallow-poached fingerling potato hash and gorgonzola cream sauce'
        },
        {
            id: 2,
            category: 'interior',
            image: 'images/gallery/interior/dining-room.jpg',
            title: 'Main Dining Room',
            description: 'Elegant Southern dining ambiance with historic charm'
        },
        {
            id: 3,
            category: 'interior',
            image: 'images/gallery/interior/table-setting.jpg',
            title: 'Table Setting',
            description: 'Fine dining presentation with attention to detail'
        },
        {
            id: 4,
            category: 'bar',
            image: 'images/gallery/bar/main-bar.jpg',
            title: 'Original 1933 Bar',
            description: 'Still serving guests after 90+ years of operation'
        },
        {
            id: 5,
            category: 'bar',
            image: 'images/gallery/bar/cocktails.jpg',
            title: 'Craft Cocktails',
            description: 'Our award-winning Old Fashioned, made with premium bourbon'
        },
        {
            id: 6,
            category: 'historic',
            image: 'images/gallery/historic/jack-flossie.jpg',
            title: 'Jack & Flossie Fry',
            description: 'Founders of Jack Fry\'s Restaurant in the 1930s'
        },
        {
            id: 7,
            category: 'historic',
            image: 'images/gallery/historic/old-photos.jpg',
            title: 'Historic Wall',
            description: 'Original photographs from the 1930s-1950s era'
        },
        {
            id: 8,
            category: 'events',
            image: 'images/gallery/events/anniversary-dinner.jpg',
            title: 'Anniversary Celebration',
            description: 'Celebrating special moments with our guests'
        }
    ];
    
    // Store gallery items in global variable
    window.galleryItems = galleryItems;
}

function initLightbox() {
    const lightbox = document.getElementById('lightbox');
    const lightboxImage = document.getElementById('lightbox-image');
    const lightboxTitle = document.getElementById('lightbox-title');
    const lightboxDescription = document.getElementById('lightbox-description');
    const currentImageSpan = document.getElementById('current-image');
    const totalImagesSpan = document.getElementById('total-images');
    
    let currentIndex = 0;
    
    // Set total images count
    totalImagesSpan.textContent = window.galleryItems.length;
    
    window.openLightbox = function(index) {
        currentIndex = index;
        updateLightbox();
        lightbox.classList.add('active');
        document.body.style.overflow = 'hidden';
    };
    
    window.closeLightbox = function() {
        lightbox.classList.remove('active');
        document.body.style.overflow = '';
    };
    
    window.changeImage = function(direction) {
        currentIndex += direction;
        
        // Loop around
        if (currentIndex < 0) {
            currentIndex = window.galleryItems.length - 1;
        } else if (currentIndex >= window.galleryItems.length) {
            currentIndex = 0;
        }
        
        updateLightbox();
    };
    
    function updateLightbox() {
        const item = window.galleryItems[currentIndex];
        
        // Preload image
        const img = new Image();
        img.src = item.image;
        img.onload = function() {
            lightboxImage.src = item.image;
            lightboxImage.alt = item.title;
            lightboxTitle.textContent = item.title;
            lightboxDescription.textContent = item.description;
            currentImageSpan.textContent = currentIndex + 1;
            
            // Add loading animation
            lightboxImage.classList.add('loaded');
            setTimeout(() => {
                lightboxImage.classList.remove('loaded');
            }, 300);
        };
    }
    
    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (!lightbox.classList.contains('active')) return;
        
        switch(e.key) {
            case 'Escape':
                closeLightbox();
                break;
            case 'ArrowLeft':
                changeImage(-1);
                break;
            case 'ArrowRight':
                changeImage(1);
                break;
        }
    });
    
    // Swipe support for touch devices
    let touchStartX = 0;
    let touchEndX = 0;
    
    lightbox.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
    });
    
    lightbox.addEventListener('touchend', function(e) {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    });
    
    function handleSwipe() {
        const swipeThreshold = 50;
        
        if (touchEndX < touchStartX - swipeThreshold) {
            changeImage(1); // Swipe left
        }
        
        if (touchEndX > touchStartX + swipeThreshold) {
            changeImage(-1); // Swipe right
        }
    }
}

function initFiltering() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const galleryItems = document.querySelectorAll('.gallery-item');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Update active button
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            const filter = this.dataset.filter;
            
            // Filter items
            galleryItems.forEach(item => {
                if (filter === 'all' || item.dataset.category === filter) {
                    item.style.display = 'block';
                    setTimeout(() => {
                        item.classList.add('visible');
                    }, 10);
                } else {
                    item.classList.remove('visible');
                    setTimeout(() => {
                        item.style.display = 'none';
                    }, 300);
                }
            });
            
            // Add animation class to visible items
            setTimeout(() => {
                document.querySelectorAll('.gallery-item[style*="block"]').forEach((item, index) => {
                    item.style.animationDelay = `${index * 0.1}s`;
                    item.classList.add('animate-in');
                });
            }, 10);
        });
    });
}

function initLazyLoading() {
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    const src = img.dataset.src;
                    
                    if (src) {
                        img.src = src;
                        img.classList.add('loaded');
                        observer.unobserve(img);
                    }
                }
            });
        }, {
            rootMargin: '50px 0px',
            threshold: 0.1
        });
        
        // Observe all gallery images
        document.querySelectorAll('.gallery-image img').forEach(img => {
            // Store original src in data-src
            const originalSrc = img.src;
            img.dataset.src = originalSrc;
            img.src = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1 1"%3E%3C/svg%3E';
            
            imageObserver.observe(img);
        });
    }
}

function initVideoPlaceholder() {
    const videoPlaceholder = document.querySelector('.video-placeholder');
    
    if (videoPlaceholder) {
        videoPlaceholder.addEventListener('click', function() {
            // In a real implementation, this would load/play the video
            this.innerHTML = `
                <div class="video-player">
                    <iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ?autoplay=1" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen>
                    </iframe>
                </div>
            `;
        });
    }
}

// Infinite scroll (if implemented)
function initInfiniteScroll() {
    let isLoading = false;
    let currentPage = 1;
    const itemsPerPage = 9;
    
    window.addEventListener('scroll', function() {
        const { scrollTop, scrollHeight, clientHeight } = document.documentElement;
        
        if (scrollTop + clientHeight >= scrollHeight - 100 && !isLoading) {
            loadMoreItems();
        }
    });
    
    function loadMoreItems() {
        isLoading = true;
        
        // Show loading indicator
        const loadingIndicator = document.createElement('div');
        loadingIndicator.className = 'loading-indicator';
        loadingIndicator.innerHTML = '<div class="loading-spinner"></div><p>Loading more images...</p>';
        document.querySelector('.gallery-grid').appendChild(loadingIndicator);
        
        // Simulate API call
        setTimeout(() => {
            // Add more items (in real app, this would come from server)
            addGalleryItems(currentPage * itemsPerPage, (currentPage + 1) * itemsPerPage);
            currentPage++;
            
            // Remove loading indicator
            loadingIndicator.remove();
            isLoading = false;
            
            // Re-initialize lazy loading for new images
            initLazyLoading();
        }, 1500);
    }
    
    function addGalleryItems(start, end) {
        // This would add new gallery items in a real implementation
        console.log(`Loading items ${start} to ${end}`);
    }
}

// Image download functionality
function initDownloadButtons() {
    document.querySelectorAll('.download-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const imageUrl = this.dataset.image;
            const imageName = this.dataset.filename || 'jack-frys-image.jpg';
            
            // Create temporary link for download
            const link = document.createElement('a');
            link.href = imageUrl;
            link.download = imageName;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Show download confirmation
            showToast('Image downloaded successfully!');
        });
    });
}

function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = message;
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => toast.classList.add('show'), 10);
    
    // Remove after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Share functionality
function initShareButtons() {
    if (navigator.share) {
        document.querySelectorAll('.share-btn').forEach(button => {
            button.style.display = 'block';
            button.addEventListener('click', async function() {
                const imageUrl = this.dataset.image;
                const imageTitle = this.dataset.title;
                
                try {
                    await navigator.share({
                        title: imageTitle,
                        text: `Check out this image from Jack Fry's Restaurant`,
                        url: window.location.origin + imageUrl,
                    });
                } catch (err) {
                    console.log('Error sharing:', err);
                }
            });
        });
    }
}

// Zoom functionality for desktop
function initImageZoom() {
    const galleryImages = document.querySelectorAll('.gallery-image img');
    
    galleryImages.forEach(img => {
        img.addEventListener('mouseenter', function() {
            if (window.innerWidth > 768) { // Desktop only
                this.style.transform = 'scale(1.1)';
            }
        });
        
        img.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
}

// Initialize on load
window.addEventListener('load', function() {
    // Start infinite scroll if implemented
    // initInfiniteScroll();
    
    // Initialize additional features
    initDownloadButtons();
    initShareButtons();
    initImageZoom();
    
    // Add keyboard shortcuts help
    document.addEventListener('keydown', function(e) {
        if (e.key === '?' && e.ctrlKey) {
            showKeyboardShortcuts();
        }
    });
});

function showKeyboardShortcuts() {
    const modal = document.createElement('div');
    modal.className = 'modal active';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>Keyboard Shortcuts</h3>
                <button class="modal-close" onclick="this.parentElement.parentElement.remove()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="shortcuts-list">
                    <div class="shortcut-item">
                        <kbd>←</kbd>
                        <span>Previous image (in lightbox)</span>
                    </div>
                    <div class="shortcut-item">
                        <kbd>→</kbd>
                        <span>Next image (in lightbox)</span>
                    </div>
                    <div class="shortcut-item">
                        <kbd>ESC</kbd>
                        <span>Close lightbox</span>
                    </div>
                    <div class="shortcut-item">
                        <kbd>F</kbd>
                        <span>Filter by category (with number)</span>
                    </div>
                    <div class="shortcut-item">
                        <kbd>Ctrl + /</kbd>
                        <span>Show this help</span>
                    </div>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

// Export functions for global use
window.openLightbox = window.openLightbox || function() {};
window.closeLightbox = window.closeLightbox || function() {};
window.changeImage = window.changeImage || function() {};
