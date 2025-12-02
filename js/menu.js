// Menu Tab Functionality
document.addEventListener('DOMContentLoaded', function() {
    const menuTabs = document.querySelectorAll('.menu-tab');
    const menuSections = document.querySelectorAll('.menu-section');
    
    menuTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // Update active tab
            menuTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Show corresponding section
            menuSections.forEach(section => {
                section.classList.remove('active');
                if (section.id === tabId) {
                    section.classList.add('active');
                }
            });
            
            // Scroll to menu
            document.querySelector('.menu-nav').scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
    
    // Dietary filter toggle (if implemented)
    const dietaryFilters = document.querySelectorAll('.dietary-filter');
    if (dietaryFilters.length > 0) {
        dietaryFilters.forEach(filter => {
            filter.addEventListener('change', function() {
                filterMenuItems();
            });
        });
    }
    
    function filterMenuItems() {
        const activeFilters = Array.from(dietaryFilters)
            .filter(f => f.checked)
            .map(f => f.value);
        
        const menuItems = document.querySelectorAll('.menu-item');
        
        menuItems.forEach(item => {
            if (activeFilters.length === 0) {
                item.style.display = 'block';
                return;
            }
            
            const itemDietary = Array.from(item.querySelectorAll('.dietary'))
                .map(d => d.textContent.trim().toLowerCase());
            
            const hasFilter = activeFilters.some(filter => 
                itemDietary.some(diet => diet.includes(filter))
            );
            
            item.style.display = hasFilter ? 'block' : 'none';
        });
    }
    
    // Menu item animation on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animated');
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.menu-item').forEach(item => {
        observer.observe(item);
    });
});
