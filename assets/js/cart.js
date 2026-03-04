/**
 * UbugeniPalace - Cart JavaScript
 * Shopping cart functionality for the artisan marketplace
 */

class Cart {
    constructor() {
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.updateCartCount();
        this.updateWishlistCount();
    }
    
    bindEvents() {
        // Add to cart buttons
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('add-to-cart')) {
                e.preventDefault();
                const productId = e.target.dataset.product;
                this.addToCart(productId);
            }
        });
        
        // Wishlist toggle buttons
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('wishlist') || e.target.closest('.wishlist')) {
                e.preventDefault();
                const productId = e.target.dataset.product || e.target.closest('.wishlist').dataset.product;
                this.toggleWishlist(productId);
            }
        });
        
        // Cart quantity updates
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('cart-quantity')) {
                const productId = e.target.dataset.product;
                const quantity = parseInt(e.target.value);
                this.updateQuantity(productId, quantity);
            }
        });
        
        // Remove from cart
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('remove-from-cart')) {
                e.preventDefault();
                const productId = e.target.dataset.product;
                this.removeFromCart(productId);
            }
        });
        
        // Clear cart
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('clear-cart')) {
                e.preventDefault();
                this.clearCart();
            }
        });
    }
    
    /**
     * Add product to cart
     */
    addToCart(productId, quantity = 1) {
        if (!this.isLoggedIn()) {
            this.showLoginPrompt();
            return;
        }
        
        this.showLoadingState(productId);
        
        fetch(`${SITE_URL}/api/cart.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'add',
                product_id: productId,
                quantity: quantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.updateCartCount(data.cart_count);
                this.showSuccessMessage('Product added to cart!');
                this.animateCartIcon();
                this.updateCartItem(productId, data.item);
            } else {
                this.showErrorMessage(data.message || 'Failed to add product to cart');
            }
        })
        .catch(error => {
            console.error('Cart error:', error);
            this.showErrorMessage('Network error. Please try again.');
        })
        .finally(() => {
            this.hideLoadingState(productId);
        });
    }
    
    /**
     * Toggle wishlist
     */
    toggleWishlist(productId) {
        if (!this.isLoggedIn()) {
            this.showLoginPrompt();
            return;
        }
        
        const wishlistBtn = document.querySelector(`[data-product="${productId}"].wishlist`);
        if (wishlistBtn) {
            wishlistBtn.classList.add('loading');
        }
        
        fetch(`${SITE_URL}/api/wishlist.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'toggle',
                product_id: productId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.updateWishlistCount(data.wishlist_count);
                this.updateWishlistButton(productId, data.in_wishlist);
                this.showSuccessMessage(data.in_wishlist ? 'Added to wishlist!' : 'Removed from wishlist');
            } else {
                this.showErrorMessage(data.message || 'Failed to update wishlist');
            }
        })
        .catch(error => {
            console.error('Wishlist error:', error);
            this.showErrorMessage('Network error. Please try again.');
        })
        .finally(() => {
            if (wishlistBtn) {
                wishlistBtn.classList.remove('loading');
            }
        });
    }
    
    /**
     * Update cart quantity
     */
    updateQuantity(productId, quantity) {
        if (quantity < 1) {
            this.removeFromCart(productId);
            return;
        }
        
        fetch(`${SITE_URL}/api/cart.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'update',
                product_id: productId,
                quantity: quantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.updateCartCount(data.cart_count);
                this.updateCartItem(productId, data.item);
                this.updateCartTotal(data.total);
            } else {
                this.showErrorMessage(data.message || 'Failed to update quantity');
            }
        })
        .catch(error => {
            console.error('Update quantity error:', error);
            this.showErrorMessage('Network error. Please try again.');
        });
    }
    
    /**
     * Remove from cart
     */
    removeFromCart(productId) {
        if (!confirm('Are you sure you want to remove this item from your cart?')) {
            return;
        }
        
        const cartItem = document.querySelector(`[data-product="${productId}"].cart-item`);
        if (cartItem) {
            cartItem.style.opacity = '0.5';
        }
        
        fetch(`${SITE_URL}/api/cart.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'remove',
                product_id: productId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.updateCartCount(data.cart_count);
                this.removeCartItem(productId);
                this.updateCartTotal(data.total);
                this.showSuccessMessage('Item removed from cart');
            } else {
                this.showErrorMessage(data.message || 'Failed to remove item');
            }
        })
        .catch(error => {
            console.error('Remove from cart error:', error);
            this.showErrorMessage('Network error. Please try again.');
        });
    }
    
    /**
     * Clear cart
     */
    clearCart() {
        if (!confirm('Are you sure you want to clear your entire cart?')) {
            return;
        }
        
        fetch(`${SITE_URL}/api/cart.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'clear'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.updateCartCount(0);
                this.clearCartItems();
                this.showSuccessMessage('Cart cleared successfully');
            } else {
                this.showErrorMessage(data.message || 'Failed to clear cart');
            }
        })
        .catch(error => {
            console.error('Clear cart error:', error);
            this.showErrorMessage('Network error. Please try again.');
        });
    }
    
    /**
     * Update cart count display
     */
    updateCartCount(count) {
        const cartCountElements = document.querySelectorAll('.cart-count');
        cartCountElements.forEach(element => {
            element.textContent = count;
            element.style.display = count > 0 ? 'block' : 'none';
        });
    }
    
    /**
     * Update wishlist count display
     */
    updateWishlistCount(count) {
        const wishlistCountElements = document.querySelectorAll('.wishlist-count');
        wishlistCountElements.forEach(element => {
            element.textContent = count;
            element.style.display = count > 0 ? 'block' : 'none';
        });
    }
    
    /**
     * Update cart item display
     */
    updateCartItem(productId, item) {
        const cartItem = document.querySelector(`[data-product="${productId}"].cart-item`);
        if (cartItem && item) {
            const quantityElement = cartItem.querySelector('.cart-quantity');
            const priceElement = cartItem.querySelector('.cart-item-price');
            const totalElement = cartItem.querySelector('.cart-item-total');
            
            if (quantityElement) quantityElement.value = item.quantity;
            if (priceElement) priceElement.textContent = this.formatPrice(item.price);
            if (totalElement) totalElement.textContent = this.formatPrice(item.total);
        }
    }
    
    /**
     * Remove cart item from display
     */
    removeCartItem(productId) {
        const cartItem = document.querySelector(`[data-product="${productId}"].cart-item`);
        if (cartItem) {
            cartItem.style.transform = 'translateX(-100%)';
            cartItem.style.opacity = '0';
            setTimeout(() => {
                cartItem.remove();
            }, 300);
        }
    }
    
    /**
     * Clear all cart items from display
     */
    clearCartItems() {
        const cartItems = document.querySelectorAll('.cart-item');
        cartItems.forEach((item, index) => {
            setTimeout(() => {
                item.style.transform = 'translateX(-100%)';
                item.style.opacity = '0';
                setTimeout(() => {
                    item.remove();
                }, 300);
            }, index * 100);
        });
    }
    
    /**
     * Update cart total
     */
    updateCartTotal(total) {
        const totalElements = document.querySelectorAll('.cart-total');
        totalElements.forEach(element => {
            element.textContent = this.formatPrice(total);
        });
    }
    
    /**
     * Update wishlist button state
     */
    updateWishlistButton(productId, inWishlist) {
        const wishlistBtn = document.querySelector(`[data-product="${productId}"].wishlist`);
        if (wishlistBtn) {
            if (inWishlist) {
                wishlistBtn.classList.add('in-wishlist');
                wishlistBtn.querySelector('img').style.filter = 'brightness(0) saturate(100%) invert(27%) sepia(51%) saturate(2878%) hue-rotate(346deg) brightness(104%) contrast(97%)';
            } else {
                wishlistBtn.classList.remove('in-wishlist');
                wishlistBtn.querySelector('img').style.filter = '';
            }
        }
    }
    
    /**
     * Show loading state
     */
    showLoadingState(productId) {
        const button = document.querySelector(`[data-product="${productId}"].add-to-cart`);
        if (button) {
            button.disabled = true;
            button.innerHTML = '<span class="loading-spinner"></span> Adding...';
        }
    }
    
    /**
     * Hide loading state
     */
    hideLoadingState(productId) {
        const button = document.querySelector(`[data-product="${productId}"].add-to-cart`);
        if (button) {
            button.disabled = false;
            button.innerHTML = 'Add to Cart';
        }
    }
    
    /**
     * Animate cart icon
     */
    animateCartIcon() {
        const cartIcon = document.querySelector('.cart-link');
        if (cartIcon) {
            cartIcon.classList.add('bounce');
            setTimeout(() => {
                cartIcon.classList.remove('bounce');
            }, 600);
        }
    }
    
    /**
     * Show success message
     */
    showSuccessMessage(message) {
        if (window.showNotification) {
            window.showNotification(message, 'success', 3000);
        } else {
            alert(message);
        }
    }
    
    /**
     * Show error message
     */
    showErrorMessage(message) {
        if (window.showNotification) {
            window.showNotification(message, 'error', 5000);
        } else {
            alert(message);
        }
    }
    
    /**
     * Show login prompt
     */
    showLoginPrompt() {
        // Try to open the login modal if the function exists
        if (typeof window.openLoginModal === 'function') {
            window.openLoginModal();
        } else {
            alert('Please log in to add items to your cart.');
            window.location.href = `${SITE_URL}/pages/login.php`;
        }
    }
    
    /**
     * Check if user is logged in
     */
    isLoggedIn() {
        // Check if user is logged in by looking for user-specific elements
        return document.querySelector('.user-menu') !== null;
    }
    
    /**
     * Format price
     */
    formatPrice(price) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(price);
    }
}

// Initialize cart when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.cart = new Cart();
});

// Global functions for use in other scripts
window.addToCart = function(productId, quantity = 1) {
    if (window.cart) {
        window.cart.addToCart(productId, quantity);
    }
};

window.toggleWishlist = function(productId) {
    if (window.cart) {
        window.cart.toggleWishlist(productId);
    }
};

window.updateCartQuantity = function(productId, quantity) {
    if (window.cart) {
        window.cart.updateQuantity(productId, quantity);
    }
};

window.removeFromCart = function(productId) {
    if (window.cart) {
        window.cart.removeFromCart(productId);
    }
};

window.clearCart = function() {
    if (window.cart) {
        window.cart.clearCart();
    }
};
