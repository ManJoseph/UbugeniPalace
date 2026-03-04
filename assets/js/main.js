/**
 * UbugeniPalace - Main JavaScript
 * Core functionality for the artisan marketplace
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeNavigation();
    initializeMobileMenu();
    initializeSearch();
    initializeDropdowns();
    initializeAnimations();
    initializeNotifications();
    initializeFooter();
    initializeHeaderInteractions(); // Add this new function
});

/**
 * Header interactions and navigation enhancements
 */
function initializeHeaderInteractions() {
    // Set active navigation based on current page
    setActiveNavigation();
    
    // Add smooth scrolling for navigation links
    addSmoothScrolling();
    
    // Enhance dropdown interactions
    enhanceDropdowns();
    
    // Add logo hover effects
    addLogoEffects();
}

/**
 * Set active navigation state
 */
function setActiveNavigation() {
    const currentPage = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href && currentPage.includes(href.replace(SITE_URL, ''))) {
            link.classList.add('active');
        }
    });
}

/**
 * Add smooth scrolling for navigation links
 */
function addSmoothScrolling() {
    const navLinks = document.querySelectorAll('.nav-link[href^="#"]');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

/**
 * Enhance dropdown functionality
 */
function enhanceDropdowns() {
    const dropdowns = document.querySelectorAll('.dropdown');
    
    dropdowns.forEach(dropdown => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        const menu = dropdown.querySelector('.dropdown-menu');
        
        if (toggle && menu) {
            // Add click event for mobile
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                dropdown.classList.toggle('active');
            });
            
            // Add hover effects for desktop
            dropdown.addEventListener('mouseenter', function() {
                if (window.innerWidth > 768) {
                    dropdown.classList.add('active');
                }
            });
            
            dropdown.addEventListener('mouseleave', function() {
                if (window.innerWidth > 768) {
                    dropdown.classList.remove('active');
                }
            });
        }
    });
}

/**
 * Add logo hover effects
 */
function addLogoEffects() {
    const logoContainer = document.querySelector('.logo-container');
    const logoCircle = document.querySelector('.logo-circle');
    
    if (logoContainer && logoCircle) {
        logoContainer.addEventListener('mouseenter', function() {
            logoCircle.style.transform = 'scale(1.05) rotate(5deg)';
        });
        
        logoContainer.addEventListener('mouseleave', function() {
            logoCircle.style.transform = 'scale(1) rotate(0deg)';
        });
    }
}

/**
 * Navigation functionality
 */
function initializeNavigation() {
    const nav = document.getElementById('mainNav');
    const navContainer = document.querySelector('.nav-container');
    
    if (!nav) return;
    
    // Sticky navigation on scroll
    let lastScrollTop = 0;
    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        if (scrollTop > 100) {
            nav.classList.add('scrolled');
        } else {
            nav.classList.remove('scrolled');
        }
        
        // Hide/show navigation on scroll
        if (scrollTop > lastScrollTop && scrollTop > 200) {
            nav.style.transform = 'translateY(-100%)';
        } else {
            nav.style.transform = 'translateY(0)';
        }
        
        lastScrollTop = scrollTop;
    });
    
    // Active navigation highlighting
    const navLinks = document.querySelectorAll('.nav-link');
    const currentPath = window.location.pathname;
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href && currentPath.includes(href.replace(SITE_URL, ''))) {
            link.classList.add('active');
        }
    });
}

/**
 * Mobile menu functionality
 */
function initializeMobileMenu() {
    const mobileToggle = document.getElementById('mobileMenuToggle');
    const mobileMenu = document.getElementById('mobileNavMenu');
    const mobileClose = document.getElementById('mobileMenuClose');
    const categoriesToggle = document.querySelector('.categories-toggle');
    const categoriesList = document.querySelector('.mobile-categories-list');
    
    if (!mobileToggle || !mobileMenu) return;
    
    // Toggle mobile menu
    mobileToggle.addEventListener('click', function() {
        mobileMenu.classList.add('active');
        mobileToggle.classList.add('active');
        document.body.style.overflow = 'hidden';
    });
    
    // Close mobile menu
    function closeMobileMenu() {
        mobileMenu.classList.remove('active');
        mobileToggle.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    mobileClose.addEventListener('click', closeMobileMenu);
    
    // Close on backdrop click
    mobileMenu.addEventListener('click', function(e) {
        if (e.target === mobileMenu) {
            closeMobileMenu();
        }
    });
    
    // Close on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && mobileMenu.classList.contains('active')) {
            closeMobileMenu();
        }
    });
    
    // Categories toggle in mobile menu
    if (categoriesToggle && categoriesList) {
        categoriesToggle.addEventListener('click', function() {
            categoriesToggle.classList.toggle('active');
            categoriesList.classList.toggle('show');
        });
    }
}

/**
 * Search functionality
 */
function initializeSearch() {
    const searchForm = document.getElementById('searchForm');
    const searchInput = document.querySelector('.search-input');
    const searchSuggestions = document.getElementById('searchSuggestions');
    
    if (!searchForm || !searchInput) return;
    
    let searchTimeout;
    
    // Live search suggestions
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        clearTimeout(searchTimeout);
        
        if (query.length < 2) {
            hideSearchSuggestions();
            return;
        }
        
        searchTimeout = setTimeout(() => {
            fetchSearchSuggestions(query);
        }, 300);
    });
    
    // Hide suggestions on click outside
    document.addEventListener('click', function(e) {
        if (!searchForm.contains(e.target)) {
            hideSearchSuggestions();
        }
    });
    
    // Hide suggestions on escape
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideSearchSuggestions();
            this.blur();
        }
    });
}

/**
 * Fetch search suggestions
 */
function fetchSearchSuggestions(query) {
    const searchSuggestions = document.getElementById('searchSuggestions');
    
    // Simulate API call - replace with actual endpoint
    fetch(`${SITE_URL}/api/search-suggestions.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            displaySearchSuggestions(data.suggestions);
        })
        .catch(error => {
            console.error('Search suggestions error:', error);
            // Fallback to basic suggestions
            const suggestions = [
                { text: query, type: 'product' },
                { text: query + ' artisan', type: 'artisan' }
            ];
            displaySearchSuggestions(suggestions);
        });
}

/**
 * Display search suggestions
 */
function displaySearchSuggestions(suggestions) {
    const searchSuggestions = document.getElementById('searchSuggestions');
    
    if (!suggestions || suggestions.length === 0) {
        hideSearchSuggestions();
        return;
    }
    
    const html = suggestions.map(suggestion => `
        <div class="suggestion-item" data-type="${suggestion.type}">
            <span class="suggestion-text">${suggestion.text}</span>
            <span class="suggestion-type">${suggestion.type}</span>
        </div>
    `).join('');
    
    searchSuggestions.innerHTML = html;
    searchSuggestions.classList.add('show');
    
    // Add click handlers
    const suggestionItems = searchSuggestions.querySelectorAll('.suggestion-item');
    suggestionItems.forEach(item => {
        item.addEventListener('click', function() {
            const text = this.querySelector('.suggestion-text').textContent;
            document.querySelector('.search-input').value = text;
            hideSearchSuggestions();
            document.getElementById('searchForm').submit();
        });
    });
}

/**
 * Hide search suggestions
 */
function hideSearchSuggestions() {
    const searchSuggestions = document.getElementById('searchSuggestions');
    if (searchSuggestions) {
        searchSuggestions.classList.remove('show');
    }
}

/**
 * Dropdown functionality
 */
function initializeDropdowns() {
    const dropdowns = document.querySelectorAll('.dropdown');
    
    dropdowns.forEach(dropdown => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        const menu = dropdown.querySelector('.dropdown-menu');
        
        if (!toggle || !menu) return;
        
        // Close dropdown on click outside
        document.addEventListener('click', function(e) {
            if (!dropdown.contains(e.target)) {
                menu.style.opacity = '0';
                menu.style.visibility = 'hidden';
                menu.style.transform = 'translateY(-10px)';
            }
        });
        
        // Toggle dropdown on click (for mobile)
        toggle.addEventListener('click', function(e) {
            if (window.innerWidth <= 1024) {
                e.preventDefault();
                const isOpen = menu.style.opacity === '1';
                
                if (isOpen) {
                    menu.style.opacity = '0';
                    menu.style.visibility = 'hidden';
                    menu.style.transform = 'translateY(-10px)';
                } else {
                    menu.style.opacity = '1';
                    menu.style.visibility = 'visible';
                    menu.style.transform = 'translateY(0)';
                }
            }
        });
    });
}

/**
 * Animation functionality
 */
function initializeAnimations() {
    // Intersection Observer for scroll animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, observerOptions);
    
    // Observe elements with animation classes
    const animatedElements = document.querySelectorAll('.animate-fadeIn, .animate-slideInLeft, .animate-slideInRight, .animate-scaleIn');
    animatedElements.forEach(el => observer.observe(el));
    
    // Smooth scroll for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href === '#') return;
            
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

/**
 * Notification system
 */
function initializeNotifications() {
    // Global notification function
    window.showNotification = function(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <span class="notification-message">${message}</span>
                <button class="notification-close">×</button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Show notification
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);
        
        // Auto hide
        const autoHide = setTimeout(() => {
            hideNotification(notification);
        }, duration);
        
        // Manual close
        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.addEventListener('click', () => {
            clearTimeout(autoHide);
            hideNotification(notification);
        });
        
        return notification;
    };
    
    function hideNotification(notification) {
        notification.classList.remove('show');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }
}

/**
 * Utility functions
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

/**
 * Form validation helpers
 */
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validatePassword(password) {
    return password.length >= 8;
}

function validateRequired(value) {
    return value.trim().length > 0;
}

/**
 * Image lazy loading
 */
function initializeLazyLoading() {
    const images = document.querySelectorAll('img[loading="lazy"]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src || img.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        images.forEach(img => imageObserver.observe(img));
    } else {
        // Fallback for older browsers
        images.forEach(img => {
            img.src = img.dataset.src || img.src;
        });
    }
}

/**
 * Initialize lazy loading
 */
document.addEventListener('DOMContentLoaded', initializeLazyLoading);

/**
 * Global error handler
 */
window.addEventListener('error', function(e) {
    console.error('Global error:', e.error);
    // You can add error reporting here
});

/**
 * Performance monitoring
 */
window.addEventListener('load', function() {
    // Log page load performance
    if ('performance' in window) {
        const perfData = performance.getEntriesByType('navigation')[0];
        console.log('Page load time:', perfData.loadEventEnd - perfData.loadEventStart, 'ms');
    }
});

/**
 * Footer functionality
 */
function initializeFooter() {
    // Newsletter form submission
    const footerNewsletterForm = document.getElementById('footerNewsletterForm');
    if (footerNewsletterForm) {
        footerNewsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = this.querySelector('input[type="email"]').value;
            const submitBtn = this.querySelector('.newsletter-submit');
            const originalText = submitBtn.textContent;
            
            // Show loading state
            submitBtn.textContent = 'Subscribing...';
            submitBtn.disabled = true;
            
            // Simulate API call (replace with actual subscription logic)
            setTimeout(() => {
                // Reset form
                this.reset();
                submitBtn.textContent = 'Subscribed!';
                submitBtn.style.backgroundColor = 'var(--accent)';
                
                // Show success message
                if (window.showNotification) {
                    window.showNotification('Thank you for subscribing to our newsletter!', 'success');
                }
                
                // Reset button after 3 seconds
                setTimeout(() => {
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                    submitBtn.style.backgroundColor = '';
                }, 3000);
            }, 1500);
        });
    }
    
    // Language toggle
    const languageToggle = document.getElementById('languageToggle');
    const languageMenu = document.getElementById('languageMenu');
    
    if (languageToggle) {
        languageToggle.addEventListener('click', function(e) {
            e.preventDefault();
            languageMenu.classList.toggle('active');
            this.classList.toggle('active');
        });
        
        // Language selection
        const langOptions = document.querySelectorAll('.lang-option');
        langOptions.forEach(option => {
            option.addEventListener('click', function(e) {
                e.preventDefault();
                
                const selectedLang = this.dataset.lang;
                const langText = this.textContent;
                
                // Update active state
                langOptions.forEach(opt => opt.classList.remove('active'));
                this.classList.add('active');
                
                // Update toggle text
                languageToggle.querySelector('.lang-text').textContent = langText;
                
                // Hide menu
                languageMenu.classList.remove('active');
                languageToggle.classList.remove('active');
                
                // Here you would typically handle language switching
                // For now, just show a notification
                if (window.showNotification) {
                    window.showNotification(`Language changed to ${langText}`, 'info');
                }
            });
        });
    }
    
    // Back to top button
    const backToTop = document.getElementById('backToTop');
    
    if (backToTop) {
        // Show/hide back to top button based on scroll position
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTop.classList.add('visible');
            } else {
                backToTop.classList.remove('visible');
            }
        });
        
        // Smooth scroll to top
        backToTop.addEventListener('click', function(e) {
            e.preventDefault();
            
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    // Social links tracking (optional)
    const socialLinks = document.querySelectorAll('.social-link');
    socialLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Here you could add analytics tracking for social media clicks
            const platform = this.classList[1]; // facebook, instagram, etc.
            console.log(`Social link clicked: ${platform}`);
        });
    });
    
    // Footer links hover effects
    const footerLinks = document.querySelectorAll('.footer-links a');
    footerLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(5px)';
        });
        
        link.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });
    });
    
    // Close language menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.footer-language')) {
            if (languageMenu) {
                languageMenu.classList.remove('active');
            }
            if (languageToggle) {
                languageToggle.classList.remove('active');
            }
        }
    });
}

// Export functions for use in other modules
window.UbugeniPalaceUtils = {
    debounce,
    throttle,
    validateEmail,
    validatePassword,
    validateRequired,
    showNotification: window.showNotification
};
