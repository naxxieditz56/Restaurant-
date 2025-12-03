// Menu Page Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Tab Navigation
    const menuTabs = document.querySelectorAll('.menu-tab');
    const menuSections = document.querySelectorAll('.menu-section');
    
    menuTabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all tabs and sections
            menuTabs.forEach(t => t.classList.remove('active'));
            menuSections.forEach(s => s.classList.remove('active'));
            
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Show corresponding section
            const targetId = this.getAttribute('href').substring(1);
            const targetSection = document.getElementById(targetId);
            if (targetSection) {
                targetSection.classList.add('active');
                
                // Smooth scroll to section
                targetSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href === '#') return;
            
            e.preventDefault();
            const targetElement = document.querySelector(href);
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Sticky menu navigation
    const menuNav = document.querySelector('.menu-navigation');
    const header = document.querySelector('.main-header');
    
    window.addEventListener('scroll', function() {
        const headerHeight = header.offsetHeight;
        const scrollPosition = window.scrollY;
        
        if (scrollPosition > headerHeight) {
            menuNav.style.top = '0';
        } else {
            menuNav.style.top = `${headerHeight}px`;
        }
    });
    
    // Highlight active section based on scroll
    window.addEventListener('scroll', function() {
        const scrollPosition = window.scrollY + 100;
        
        menuSections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.offsetHeight;
            
            if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                const sectionId = section.getAttribute('id');
                menuTabs.forEach(tab => {
                    tab.classList.remove('active');
                    if (tab.getAttribute('href') === `#${sectionId}`) {
                        tab.classList.add('active');
                    }
                });
            }
        });
    });
});
