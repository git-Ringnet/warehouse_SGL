document.addEventListener('DOMContentLoaded', function() {
    // Get current URL path
    const currentPath = window.location.pathname;
    
    // Remove 'active' class from all nav items
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('active');
    });
    
    // Remove 'active' class from all dropdown links
    document.querySelectorAll('.dropdown-content a').forEach(item => {
        item.classList.remove('active');
    });
    
    // Handle direct nav links (like "Tổng Quan")
    document.querySelectorAll('a.nav-item').forEach(item => {
        const link = item.getAttribute('href');
        if (link) {
            const linkUrl = new URL(link, window.location.origin);
            const currentUrl = new URL(window.location.href);
            
            if (currentUrl.pathname === linkUrl.pathname || 
                currentUrl.pathname === linkUrl.pathname + '/' ||
                (linkUrl.pathname === '/' && currentUrl.pathname === '')) {
                item.classList.add('active');
            }
        }
    });
    
    // Handle dropdown items
    document.querySelectorAll('.dropdown-content a').forEach(item => {
        const link = item.getAttribute('href');
        if (link) {
            const linkUrl = new URL(link, window.location.origin);
            const currentUrl = new URL(window.location.href);
            
            // Check if current path matches or starts with the link path
            if (currentUrl.pathname === linkUrl.pathname || 
                currentUrl.pathname.startsWith(linkUrl.pathname + '/')) {
                
                // Add active class to the dropdown item
                item.classList.add('active');
                
                // Show the dropdown
                const dropdownContent = item.closest('.dropdown-content');
                if (dropdownContent) {
                    dropdownContent.classList.add('show');
                    
                    // Also add active class to the parent dropdown button/nav-item
                    const parentDropdown = dropdownContent.closest('.dropdown');
                    if (parentDropdown) {
                        const navButton = parentDropdown.querySelector('.nav-item');
                        if (navButton) {
                            navButton.classList.add('active');
                        }
                    }
                }
            }
        }
    });
    
    // Special case for dropdown parent items that are also links
    document.querySelectorAll('.dropdown > a').forEach(item => {
        const link = item.getAttribute('href');
        if (link) {
            const linkUrl = new URL(link, window.location.origin);
            const currentUrl = new URL(window.location.href);
            
            if (currentUrl.pathname === linkUrl.pathname || 
                currentUrl.pathname.startsWith(linkUrl.pathname + '/')) {
                
                // Find the button inside this link's parent and add active class
                const parentDropdown = item.closest('.dropdown');
                if (parentDropdown) {
                    const navButton = parentDropdown.querySelector('.nav-item');
                    if (navButton) {
                        navButton.classList.add('active');
                    }
                }
            }
        }
    });
}); 