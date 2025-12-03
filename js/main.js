// Main Website Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Mobile Menu Toggle
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    const mainNav = document.querySelector('.main-nav');
    
    if (mobileToggle && mainNav) {
        mobileToggle.addEventListener('click', function() {
            mainNav.classList.toggle('active');
            this.classList.toggle('active');
            document.body.style.overflow = mainNav.classList.contains('active') ? 'hidden' : '';
        });
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!mainNav.contains(e.target) && !mobileToggle.contains(e.target)) {
                mainNav.classList.remove('active');
                mobileToggle.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
        
        // Close mobile menu on link click
        document.querySelectorAll('.nav-list a').forEach(link => {
            link.addEventListener('click', () => {
                mainNav.classList.remove('active');
                mobileToggle.classList.remove('active');
                document.body.style.overflow = '';
            });
        });
    }
    
    // Testimonial Slider
    const testimonials = document.querySelectorAll('.testimonial');
    let currentTestimonial = 0;
    
    if (testimonials.length > 1) {
        function showTestimonial(index) {
            testimonials.forEach(testimonial => {
                testimonial.classList.remove('active');
            });
            testimonials[index].classList.add('active');
        }
        
        // Auto-rotate testimonials
        setInterval(() => {
            currentTestimonial = (currentTestimonial + 1) % testimonials.length;
            showTestimonial(currentTestimonial);
        }, 5000);
    }
    
    // Hero Slider (if multiple slides)
    const slides = document.querySelectorAll('.slide');
    if (slides.length > 1) {
        let currentSlide = 0;
        
        function showSlide(index) {
            slides.forEach(slide => slide.classList.remove('active'));
            slides[index].classList.add('active');
        }
        
        setInterval(() => {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }, 8000);
    }
    
    // Form Validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('error');
                    isValid = false;
                } else {
                    field.classList.remove('error');
                }
                
                // Email validation
                if (field.type === 'email' && field.value) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(field.value)) {
                        field.classList.add('error');
                        isValid = false;
                    }
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                form.classList.add('was-validated');
            }
        });
    });
    
    // Lazy Loading Images
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.add('loaded');
                    observer.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
    
    // Smooth Scrolling for Anchor Links
    document.querySelectorAll('a[href^="#"]:not([href="#"])').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                e.preventDefault();
                window.scrollTo({
                    top: targetElement.offsetTop - 100,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Current Year in Footer
    const yearElement = document.querySelector('.current-year');
    if (yearElement) {
        yearElement.textContent = new Date().getFullYear();
    }
    
    // Dynamic Hours Display
    function updateHoursDisplay() {
        const now = new Date();
        const day = now.getDay();
        const hour = now.getHours();
        const minutes = now.getMinutes();
        const currentTime = hour + minutes / 100;
        
        let isOpen = false;
        let nextOpen = '';
        
        // Monday-Friday Lunch
        if (day >= 1 && day <= 5 && currentTime >= 11.0 && currentTime <= 14.3) {
            isOpen = true;
        }
        // Dinner hours
        else if (day >= 0 && day <= 6 && currentTime >= 17.3) {
            if (day === 0 || day <= 4) { // Sun-Thu
                isOpen = currentTime <= 22.0;
            } else if (day === 5 || day === 6) { // Fri-Sat
                isOpen = currentTime <= 23.0;
            }
        }
        
        const openStatus = document.querySelector('.open-status');
        if (openStatus) {
            if (isOpen) {
                openStatus.textContent = 'Open Now';
                openStatus.classList.add('open');
                openStatus.classList.remove('closed');
            } else {
                openStatus.textContent = 'Closed';
                openStatus.classList.add('closed');
                openStatus.classList.remove('open');
            }
        }
    }
    
    updateHoursDisplay();
    setInterval(updateHoursDisplay, 60000); // Update every minute
});
