/**
 * Mobilo Cart JavaScript
 * Handles all cart interactions and dynamic updates
 */

// For dynamic cart
var mainContent = document.querySelector('#mobilo-cart-dynamic');

// Global Toaster Functionality
class MobiloToaster {
    constructor() {
        this.container = null;
        this.toasts = new Map(); // Track active toasts by message+type
        this.init();
    }

    init() {
        // Create toaster container if it doesn't exist
        if (!document.querySelector('.mobilo-toaster-container')) {
            this.container = document.createElement('div');
            this.container.className = 'mobilo-toaster-container';
            this.container.style.cssText = `
                position: fixed;
                top: 20px;
                left: 50%;
                transform: translateX(-50%);
                z-index: 10000;
                pointer-events: none;
            `;
            document.body.appendChild(this.container);
        } else {
            this.container = document.querySelector('.mobilo-toaster-container');
        }
    }

    _getToastKey(message, type) {
        // Use both message and type to distinguish toasts
        return `${type}::${message}`;
    }

    show(message, type = 'info', duration = 3000) {
        const key = this._getToastKey(message, type);

        // If a toast with the same message and type exists, reset its timer
        if (this.toasts.has(key)) {
            const { toast, timeoutId } = this.toasts.get(key);
            // Reset timer
            clearTimeout(timeoutId);
            // Set new timeout
            const newTimeoutId = setTimeout(() => {
                this.remove(toast, key);
            }, duration);
            this.toasts.set(key, { toast, timeoutId: newTimeoutId });
            // Optionally, animate again to indicate activity
            toast.style.transition = 'none';
            toast.style.transform = 'translateY(-20px)';
            toast.style.opacity = '0.7';
            // Force reflow
            void toast.offsetWidth;
            toast.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
            toast.style.transform = 'translateY(0)';
            toast.style.opacity = '1';
            return toast;
        }

        const toast = document.createElement('div');
        toast.className = `mobilo-toast mobilo-toast-${type}`;

        // Toast styles
        const baseStyles = `
            background: white;
            border-radius: 8px;
            padding: 12px 20px;
            margin-bottom: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-left: 4px solid;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            max-width: 400px;
            word-wrap: break-word;
            pointer-events: auto;
            transform: translateY(-100px);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        `;

        // Type-specific styles
        const typeStyles = {
            success: 'border-left-color: #10b981; color: #065f46;',
            error: 'border-left-color: #ef4444; color: #991b1b;',
            warning: 'border-left-color: #f59e0b; color: #92400e;',
            info: 'border-left-color: #3b82f6; color: #1e40af;'
        };

        toast.style.cssText = baseStyles + (typeStyles[type] || typeStyles.info);
        toast.textContent = message;

        // Add icon based on type
        const iconMap = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };

        const icon = document.createElement('span');
        icon.textContent = iconMap[type] || iconMap.info;
        icon.style.cssText = `
            margin-right: 8px;
            font-weight: bold;
        `;
        toast.insertBefore(icon, toast.firstChild);

        this.container.appendChild(toast);

        // Animate in
        requestAnimationFrame(() => {
            toast.style.transform = 'translateY(0)';
            toast.style.opacity = '1';
        });

        // Auto remove
        const timeoutId = setTimeout(() => {
            this.remove(toast, key);
        }, duration);

        // Track this toast
        this.toasts.set(key, { toast, timeoutId });

        // Click to dismiss
        toast.addEventListener('click', () => {
            this.remove(toast, key);
        });

        return toast;
    }

    remove(toast, key = null) {
        // Remove from DOM and clear timer
        if (toast && toast.parentNode) {
            toast.style.transform = 'translateY(-100px)';
            toast.style.opacity = '0';
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }
        // Remove from map and clear timeout
        if (!key) {
            // Try to find the key by value
            for (let [k, v] of this.toasts.entries()) {
                if (v.toast === toast) {
                    key = k;
                    break;
                }
            }
        }
        if (key && this.toasts.has(key)) {
            const { timeoutId } = this.toasts.get(key);
            clearTimeout(timeoutId);
            this.toasts.delete(key);
        }
    }

    success(message, duration) {
        return this.show(message, 'success', duration);
    }

    error(message, duration) {
        return this.show(message, 'error', duration);
    }

    warning(message, duration) {
        return this.show(message, 'warning', duration);
    }

    info(message, duration) {
        return this.show(message, 'info', duration);
    }
}

// Initialize global toaster
window.mobiloToaster = new MobiloToaster();

// Global showMessage function for backward compatibility
window.showMessage = function (message, type = 'info', duration = 3000) {
    return window.mobiloToaster.show(message, type, duration);
};

// Smooth scrolling for better UX
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Add loading states to buttons
document.querySelectorAll('button').forEach(button => {
    button.addEventListener('click', function () {
        if (this.dataset.action === 'checkout' || this.dataset.action === 'add-to-cart') {
            const originalText = this.textContent;
            this.textContent = 'Loading...';
            this.disabled = true;

            setTimeout(() => {
                this.textContent = originalText;
                // this.disabled = false;
            }, 1000);
        }
    });
});


async function fetchCSS(url) {
    const response = await fetch(url);
    return response.text();
}

// For dynamic cart
// check if mainContent is already attached to a shadow dom
if (mainContent && !mainContent.shadowRoot) {
    var mainShadow = mainContent.attachShadow({ mode: 'open' });

    // Show loading state while fetching CSS
    mainShadow.innerHTML = `
        <style>
            .mobilo-cart-loading {
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 400px;
                font-size: 1.25rem;
                color: #181059;
                background: #fff;
            }
            .mobilo-cart-spinner {
                border: 4px solid #f3f3f3;
                border-top: 4px solid #181059;
                border-radius: 50%;
                width: 32px;
                height: 32px;
                animation: mobilo-spin 1s linear infinite;
                margin-right: 12px;
            }
            @keyframes mobilo-spin {
                0% { transform: rotate(0deg);}
                100% { transform: rotate(360deg);}
            }
        </style>
        <div class="mobilo-cart-loading">
            <span class="mobilo-cart-spinner"></span>
            <span>Loading cart...</span>
        </div>
    `;

    fetchCSS(mobiloCart.themeUrl + '/assets/dist/cart.css').then(css => {
        // Only reveal cart after CSS is loaded
        mainShadow.innerHTML = `<style>${css}</style>${mainContent.innerHTML}`;

        // Initialize cart functionality inside shadow DOM
        initializeShadowCart(mainShadow);
    });
}

// Function to initialize cart functionality inside shadow DOM
function initializeShadowCart(shadowRoot) {
    // Wait for shadow DOM content to be ready
    setTimeout(() => {

        // Initialize cart state for shadow DOM
        initializeShadowCartState(shadowRoot);

        const cartData = window.mobiloCart.cart_data;
        // initialize cart data
        renderCartShadow(cartData.cart_data, shadowRoot);
        // Re-bind all event listeners to elements inside shadow DOM
        bindShadowEvents(shadowRoot);
    }, 100);
}

// Function to bind events inside shadow DOM
function bindShadowEvents(shadowRoot) {
    // Material selection
    const materialButtons = shadowRoot.querySelectorAll('[data-material]');

    // Color selection
    const colorButtons = shadowRoot.querySelectorAll('[data-color]');

    // Add to cart buttons
    const addToCartButtons = shadowRoot.querySelectorAll('.mobilo-add-to-cart');

    // Quantity buttons
    const quantityButtons = shadowRoot.querySelectorAll('.mobilo-quantity-btn');

    // Remove item buttons
    const removeButtons = shadowRoot.querySelectorAll('.mobilo-remove-item');

    // Add upsell buttons
    const upsellButtons = shadowRoot.querySelectorAll('.mobilo-add-upsell-all');

    // Checkout button
    const checkoutButtons = shadowRoot.querySelectorAll('.mobilo-checkout-btn');

    // Remove all event listeners from buttons
    [...materialButtons, ...colorButtons, ...addToCartButtons, ...quantityButtons, ...removeButtons, ...upsellButtons, ...checkoutButtons].forEach(button => {
        // Clone the button (deep clone)
        const clonedButton = button.cloneNode(true);
        // Replace the original button with the clone
        // button.replaceWith(clonedButton);
    });

    materialButtons.forEach(button => {
        button.addEventListener('click', function () {
            // check if this button has already had an event listener
            if (button.hasAttribute('data-event-listener')) {
                return;
            }
            const material = this.dataset.material;
            const container = this.closest('.mobilo-product-card');

            // Remove active class from all buttons in this container
            const allMaterialButtons = container.querySelectorAll('.mobilo-material-btn');
            allMaterialButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            // Update add to cart button with selected material
            const addButton = container.querySelector('.mobilo-add-to-cart');
            if (addButton) {
                addButton.dataset.variation = JSON.stringify({ 'attribute_pa_material': material });
            }

            // Update price if available
            const priceElement = container.querySelector('.mobilo-product-price');
            const variationPrice = this.dataset.price;
            if (variationPrice && priceElement) {
                priceElement.textContent = window.mobiloCart.currency_symbol + variationPrice;
            }
            button.setAttribute('data-event-listener', 'true');
        }, { once: true });
    });

    colorButtons.forEach(button => {
        button.addEventListener('click', function () {
            // check if this button has already had an event listener
            if (button.hasAttribute('data-event-listener')) {
                return;
            }
            const color = this.dataset.color;
            const container = this.closest('.mobilo-product-card');

            // Update active state
            const allColorButtons = container.querySelectorAll('.mobilo-card-color');
            allColorButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            // Update add to cart button with selected color
            const addButton = container.querySelector('.mobilo-add-to-cart');
            if (addButton) {
                addButton.dataset.cardColor = color;
            }
            button.setAttribute('data-event-listener', 'true');
        }, { once: true });
    });

    addToCartButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            // check if this button has already had an event listener
            if (button.hasAttribute('data-event-listener')) {
                return;
            }
            e.preventDefault();
            handleAddToCartShadow(e, shadowRoot);
            button.setAttribute('data-event-listener', 'true');
        }, { once: true });
    });

    // Update quantity buttons
    quantityButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            // check if this button has already had an event listener
            if (button.hasAttribute('data-event-listener')) {
                return;
            }
            e.preventDefault();
            handleQuantityUpdateShadow(e, shadowRoot);
            button.setAttribute('data-event-listener', 'true');
        }, { once: true });
    });

    // Remove item buttons
    removeButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            // check if this button has already had an event listener
            if (button.hasAttribute('data-event-listener')) {
                return;
            }
            e.preventDefault();
            handleRemoveItemShadow(e, shadowRoot);
            button.setAttribute('data-event-listener', 'true');
        }, { once: true });
    });

    // Add upsell buttons
    upsellButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            // check if this button has already had an event listener
            if (button.hasAttribute('data-event-listener')) {
                return;
            }
            e.preventDefault();
            handleAddToCartShadow(e, shadowRoot);
            button.setAttribute('data-event-listener', 'true');
        }, { once: true });
    });

    // Checkout button
    // checkoutButtons.forEach(button => {
    //     button.addEventListener('click', function (e) {
    //         e.preventDefault();
    //         const checkoutUrl = this.dataset.checkoutUrl || '/checkout/';
    //         window.location.href = checkoutUrl;
    //     });
    // });
}

// Shadow DOM specific handlers
function handleAddToCartShadow(e, shadowRoot) {
    const button = e.currentTarget;
    const productId = button.dataset.productId;
    const quantity = button.dataset.quantity || 1;
    const variationId = button.dataset.variationId || 0;
    const variation = button.dataset.variation ? JSON.parse(button.dataset.variation) : {};
    const cardColor = button.dataset.cardColor || '';

    if (!productId) {
        window.showMessage('Invalid product', 'error');
        return;
    }

    const data = {
        action: window.mobiloCart.actions.add_to_cart,
        product_id: productId,
        quantity: quantity,
        variation_id: variationId,
        variation: variation,
        card_color: cardColor,
        _ajaxNonce: window.mobiloCart.nonce
    };

    setButtonLoadingShadow(button, true);

    // Use vanilla JavaScript fetch instead of jQuery
    fetch(window.mobiloCart.ajaxUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(data)
    })
        .then(response => response.json())
        .then(response => {
            if (response.success) {
                updateCartDisplayShadow(response.cart_data, shadowRoot);
                window.showMessage(response.message, 'success');
                updateButtonStateShadow(button, true);
            } else {
                window.showMessage(response.data || 'Failed to add to cart', 'error');
            }
        })
        .catch(error => {
            window.showMessage('An error occurred while adding to cart', 'error');
            console.error('Add to cart error:', error);
        })
        .finally(() => {
            setButtonLoadingShadow(button, false);
        });
}

function handleQuantityUpdateShadow(e, shadowRoot) {
    const button = e.currentTarget;
    const item = button.closest('.mobilo-cart-item');
    const cartItemKey = item.dataset.cartItemKey;

    if (!cartItemKey) {
        window.showMessage('Invalid item', 'error');
        return;
    }

    const currentQty = parseInt(item.querySelector('.mobilo-quantity').textContent);
    const isIncrease = button.classList.contains('mobilo-increase');

    let newQty = isIncrease ? currentQty + 1 : currentQty - 1;
    if (newQty < 0) newQty = 0;
    // disable the button if newQty is 0
    button.setAttribute('disabled', true);
    updateCartItemQuantityShadow(cartItemKey, newQty, shadowRoot);
    // enable the button
    button.setAttribute('disabled', false);
}

function handleRemoveItemShadow(e, shadowRoot) {
    const button = e.currentTarget;
    const item = button.closest('.mobilo-cart-item');
    const cartItemKey = item.dataset.cartItemKey;

    if (!cartItemKey) {
        window.showMessage('Invalid item', 'error');
        return;
    }

    if (confirm('Are you sure you want to remove this item?')) {
        removeCartItemShadow(cartItemKey, shadowRoot);
    }
}

function handleAddUpsellAllShadow(e, shadowRoot) {
    const button = e.currentTarget;
    const productId = button.dataset.productId;
    const quantity = button.dataset.quantity || 1;

    setButtonLoadingShadow(button, true);

    const data = {
        action: window.mobiloCart.actions.add_upsell_all,
        product_id: productId,
        quantity: quantity,
        _ajaxNonce: window.mobiloCart.nonce
    };

    fetch(window.mobiloCart.ajaxUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(data)
    })
        .then(response => response.json())
        .then(response => {
            if (response.success) {
                updateCartDisplayShadow(response.cart_data, shadowRoot);
                window.showMessage(response.message, 'success');
            } else {
                window.showMessage(response.data || 'Failed to add products', 'error');
            }
        })
        .catch(error => {
            window.showMessage('An error occurred', 'error');
            console.error('Add upsell error:', error);
        })
        .finally(() => {
            setButtonLoadingShadow(button, false);
        });
}

function updateCartItemQuantityShadow(cartItemKey, quantity, shadowRoot) {
    const data = {
        action: window.mobiloCart.actions.update_cart_quantity,
        cart_item_key: cartItemKey,
        quantity: quantity,
        _ajaxNonce: window.mobiloCart.nonce
    };

    fetch(window.mobiloCart.ajaxUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(data)
    })
        .then(response => response.json())
        .then(response => {
            if (response.success) {
                updateCartDisplayShadow(response.cart_data, shadowRoot);
                window.showMessage(response.message, 'success');
            } else {
                window.showMessage(response.data || 'Failed to update cart', 'error');
            }
        })
        .catch(error => {
            window.showMessage('An error occurred while updating cart', 'error');
            console.error('Update cart error:', error);
        });
}

function removeCartItemShadow(cartItemKey, shadowRoot) {
    const data = {
        action: window.mobiloCart.actions.remove_cart_item,
        cart_item_key: cartItemKey,
        _ajaxNonce: window.mobiloCart.nonce
    };

    fetch(window.mobiloCart.ajaxUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(data)
    })
        .then(response => response.json())
        .then(response => {
            if (response.success) {
                updateCartDisplayShadow(response.cart_data, shadowRoot);
                window.showMessage(response.message, 'success');
            } else {
                window.showMessage(response.data || 'Failed to remove item', 'error');
            }
        })
        .catch(error => {
            window.showMessage('An error occurred while removing item', 'error');
            console.error('Remove item error:', error);
        });
}

function updateCartDisplayShadow(cartData, shadowRoot) {
    if (!cartData) {
        // Fetch current cart data
        const data = {
            action: window.mobiloCart.actions.get_cart_data,
            _ajaxNonce: window.mobiloCart.nonce
        };

        fetch(window.mobiloCart.ajaxUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(data)
        })
            .then(response => response.json())
            .then(response => {
                if (response.success) {
                    renderCartShadow(response.cart_data, shadowRoot);
                }
            })
            .catch(error => {
                console.error('Get cart data error:', error);
            });
    } else {
        renderCartShadow(cartData, shadowRoot);
    }
}

function renderCartShadow(cartData, shadowRoot) {
    // Check if cart is empty and update visibility
    updateCartVisibilityShadow(cartData, shadowRoot);

    // Update cart items
    renderCartItemsShadow(cartData.items, shadowRoot);

    // Update license
    renderCartLicenseShadow(cartData.cart_license, shadowRoot);

    // Update cart totals
    renderCartTotalsShadow(cartData, shadowRoot);

    // Update product states
    updateProductStatesShadow(cartData, shadowRoot);

    // Update cart count
    // updateCartCountShadow(cartData, shadowRoot);
}

// Add new function to handle cart visibility
function updateCartVisibilityShadow(cartData, shadowRoot) {
    const isEmpty = cartData.is_cart_empty ||
        (!cartData.items ||
            (!cartData.items.products || cartData.items.products.length === 0) &&
            (!cartData.items.accessories || cartData.items.accessories.length === 0) &&
            (!cartData.cart_license || cartData.cart_license.length === 0));

    // Get visibility elements
    const emptyCartSection = shadowRoot.querySelector('#mobilo-empty-cart');
    const productsSection = shadowRoot.querySelector('#mobilo-products-section');
    const nonEmptyCart = shadowRoot.querySelector('#mobilo-non-empty-cart');
    const checkoutButton = shadowRoot.querySelector('.mobilo-checkout-btn');
    // const cartNotes = shadowRoot.querySelector('.space-y-2');
    const planCard = shadowRoot.querySelector('.plan-card');
    const cartNotes = shadowRoot.querySelectorAll('.cart-notes');
    if (isEmpty) {
        // Show empty cart message
        if (emptyCartSection) {
            emptyCartSection.classList.remove('hidden');
        }
        // Hide products section and cart items
        if (productsSection) {
            productsSection.classList.add('hidden');
        }
        if (nonEmptyCart) {
            nonEmptyCart.classList.add('hidden');
        }
        // Disable checkout button when cart is empty
        if (checkoutButton) {
            checkoutButton.disabled = true;
            checkoutButton.classList.add('opacity-50', 'cursor-not-allowed');
            checkoutButton.classList.remove('hover:bg-blue-700');
        }
        // Hide cart notes when cart is empty
        // if (cartNotes) {
        //     cartNotes.style.display = 'none';
        // }
        // Hide plan card when cart is empty
        // if (planCard) {
        //     planCard.style.display = 'none';
        // }
    } else {
        // Hide empty cart message
        if (emptyCartSection) {
            emptyCartSection.classList.add('hidden');
        }
        // Show products section and cart items
        if (productsSection) {
            productsSection.classList.remove('hidden');
        }
        if (nonEmptyCart) {
            nonEmptyCart.classList.remove('hidden');
        }
        // Enable checkout button when cart has items
        if (checkoutButton) {
            checkoutButton.disabled = false;
            checkoutButton.classList.remove('opacity-50', 'cursor-not-allowed');
            checkoutButton.classList.add('hover:bg-blue-700');
        }
        // Show cart notes when cart has items
        // if (cartNotes) {
        //     cartNotes.style.display = 'block';
        // }
        // Show plan card when cart has items
        // if (planCard) {
        //     planCard.style.display = 'block';
        // }
    }
}

function renderCartItemsShadow(items, shadowRoot) {
    const cartContainer = shadowRoot.querySelector('.mobilo-cart-items');
    if (!cartContainer) return;

    let html = '';

    // Render products
    if (items.products && items.products.length > 0) {
        items.products.forEach(item => {
            html += renderCartItemShadow(item);
        });
    }

    // Render accessories
    if (items.accessories && items.accessories.length > 0) {
        items.accessories.forEach(item => {
            html += renderCartAccessoriesItemShadow(item);
        });
    }

    cartContainer.innerHTML = html;

    // Re-bind events for new items
    bindShadowEvents(shadowRoot);
}

function renderCartLicenseShadow(items, shadowRoot) {
    let cartContainer = shadowRoot.querySelector('#mobilo-cart-license');
    if (!cartContainer) {
        const divider = shadowRoot.querySelector('.cart-license-divider');
        // add the lisence container after the divider
        const html = `
        <div class="flex justify-between items-center my-3" id="mobilo-cart-license">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center">
                    <img src="${window.mobiloCart.themeUrl}/assets/images/team.svg" alt="plan">
                </div>
                <div>
                    <h4 class="text-xl font-bold text-gray-900">${items[0].name} Plan</h4>
                    <p class="text-sm text-gray-600">${items[0].quantity} members</p>
                    <!-- <p class="text-sm text-gray-600">Per employee, billed annually.</p> -->
                </div>
            </div>
            <div class="flex items-center gap-5">
                <div class="text-right flex flex-col gap-1">
                    <span class="text-base font-bold text-gray-900">${window.mobiloCart.currency_symbol}${items[0].sale_price}</span>
                    <div class="input-quantity" data-quantity-control data-item-id="plan">
                        <button class="mobilo-quantity-btn mobilo-decrease cursor-pointer" data-action="decrease">-</button>
                        <span class="mobilo-quantity mobilo-seat-quantity" data-quantity>${items[0].quantity}</span>
                        <button class="mobilo-quantity-btn mobilo-increase cursor-pointer" data-action="increase">+</button>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button class="text-gray-600 hover:text-gray-900 mobilo-remove-item cursor-pointer" 
                            data-action="remove"
                            data-cart-item-key="${items[0].item_key}">
                        <img src="${window.mobiloCart.themeUrl}/assets/images/delete.svg" alt="Delete" class="w-4 h-4">
                    </button>
                </div>
            </div>
            
        </div>
        `;

        // add html after the divider
        divider.insertAdjacentHTML('afterend', html);

        return;
    }

    // Check if license items exist and are not empty
    if (!items || items.length === 0) {
        cartContainer.innerHTML = '';
        return;
    }

    let html = '';
    html += renderCartLicenseItemShadow(items[0]); // Assuming single license item
    cartContainer.innerHTML = html;

    // Re-bind events for new items
    bindShadowEvents(shadowRoot);
}

function renderCartLicenseItemShadow(item) {
    return `
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center">
                                    <img src="${window.mobiloCart.themeUrl}/assets/images/team.svg" alt="plan">
                                </div>
                                <div>
                                    <h4 class="text-xl font-bold text-gray-900">${item.name} Plan</h4>
                                    <p class="text-sm text-gray-600">${item.quantity} members</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-5">
                                <div class="text-right flex flex-col gap-1">
                                    <span class="text-base font-bold text-gray-900">${window.mobiloCart.currency_symbol}${item.sale_price}</span>
                                    <div class="input-quantity" data-quantity-control data-item-id="plan">
                                        <button class="mobilo-quantity-btn mobilo-decrease cursor-pointer" data-action="decrease">-</button>
                                        <span class="mobilo-quantity mobilo-seat-quantity" data-quantity>${item.quantity}</span>
                                        <button class="mobilo-quantity-btn mobilo-increase cursor-pointer" data-action="increase">+</button>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <button class="text-gray-600 hover:text-gray-900 mobilo-remove-item cursor-pointer" 
                                            data-action="remove"
                                            data-cart-item-key="${item.item_key}">
                                        <img src="${window.mobiloCart.themeUrl}/assets/images/delete.svg" alt="Delete" class="w-4 h-4">
                                    </button>
                                </div>
                            </div>
    `;
}

function renderCartAccessoriesItemShadow(item) {
    return `
                            <div class="flex justify-between items-center mb-3 mobilo-cart-item" data-cart-item-key="${item.item_key}">
                                <div class="flex items-center gap-3">
                                    <div>
                                        <h4 class="text-base font-bold text-gray-900 m-0">${item.name}</h4>
                                        <p class="text-sm text-gray-600 m-0">${item.quantity} units</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-5">
                                    <div class="text-right">
                                        <span class="text-base font-bold text-gray-900">${window.mobiloCart.currency_symbol}${item.subtotal}</span>
                                    </div>
                                    <button class="text-gray-600 hover:text-gray-900 mobilo-remove-item cursor-pointer" 
                                            data-action="remove"
                                            data-cart-item-key="${item.item_key}">
                                        <img src="${window.mobiloCart.themeUrl}/assets/images/delete.svg" alt="Delete" class="w-4 h-4">
                                    </button>
                                </div>
                            </div>
    `;
}

function renderCartItemShadow(item) {
    return `
    <div class="flex justify-between items-center mb-3 mobilo-cart-item" data-cart-item-key="${item.item_key}">
                                <div class="flex items-center gap-3">
                                    <div>
                                        <h4 class="text-base font-bold text-gray-900 m-0">${item.name}</h4>
                                        ${item.card_color ? `<p class="text-sm text-gray-600 m-0">${item.card_color}</p>` : ''}
                                    </div>
                                </div>
                                <div class="flex items-center gap-5">
                                    <div class="text-right flex flex-col gap-1">
                                        <span class="text-base font-bold text-gray-900 m-0">${window.mobiloCart.currency_symbol}${item.subtotal}</span>
                                        <div class="input-quantity" data-quantity-control data-item-id="${item.item_key}">
                                            <button class="mobilo-quantity-btn mobilo-decrease cursor-pointer" data-action="decrease">-</button>
                                            <span class="mobilo-quantity" data-quantity>${item.quantity}</span>
                                            <button class="mobilo-quantity-btn mobilo-increase cursor-pointer" data-action="increase">+</button>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <button class="text-gray-600 hover:text-gray-900 mobilo-remove-item cursor-pointer" 
                                                data-action="remove"
                                                data-cart-item-key="${item.item_key}">
                                            <img src="${window.mobiloCart.themeUrl}/assets/images/delete.svg" alt="Delete" class="w-4 h-4">
                                        </button>
                                    </div>
                                </div>
                            </div>
    `;
}

function renderCartTotalsShadow(cartData, shadowRoot) {
    const totalElement = shadowRoot.querySelector('.mobilo-cart-total');
    if (totalElement) {
        totalElement.textContent = `${window.mobiloCart.currency_symbol}${cartData.total || '0.00'}`;
    }

    const oneTimeElement = shadowRoot.querySelector('.mobilo-one-time');
    if (oneTimeElement && cartData.one_time && cartData.one_time !== '0.00') {
        oneTimeElement.textContent = `${window.mobiloCart.currency_symbol}${cartData.one_time}`;
        // Show the one-time pricing info
        const oneTimeContainer = oneTimeElement.closest('.space-y-3');
        if (oneTimeContainer) {
            oneTimeContainer.style.display = 'block';
        }
    } else {
        // Hide one-time pricing info if not applicable
        const oneTimeContainer = shadowRoot.querySelector('.space-y-3');
        if (oneTimeContainer && oneTimeContainer.querySelector('.mobilo-one-time')) {
            oneTimeContainer.style.display = 'none';
        }
    }

    const perYearElement = shadowRoot.querySelector('.mobilo-per-year');
    if (perYearElement && cartData.per_year) {
        perYearElement.textContent = `${window.mobiloCart.currency_symbol}${cartData.per_year}`;
    }
}

function updateProductStatesShadow(cartData, shadowRoot) {
    // Update add to cart buttons based on what's in cart
    const addToCartButtons = shadowRoot.querySelectorAll('.mobilo-add-to-cart');
    const upsellButtons = shadowRoot.querySelectorAll('.mobilo-add-upsell-all');

    // Check if cart has any items
    const hasProducts = cartData.items && cartData.items.products && cartData.items.products.length > 0;
    const hasAccessories = cartData.items && cartData.items.accessories && cartData.items.accessories.length > 0;
    const hasLicense = cartData.cart_license && cartData.cart_license.length > 0;
    const hasItems = hasProducts || hasAccessories || hasLicense;

    addToCartButtons.forEach(button => {
        const productId = button.dataset.productId;
        let isInCart = false;

        if (hasItems) {
            isInCart = (cartData.items.products && cartData.items.products.some(item => item.id == productId)) ||
                (cartData.items.accessories && cartData.items.accessories.some(item => item.id == productId));
        }

        if (isInCart) {
            button.textContent = 'In Cart';
            button.classList.add('bg-gray-500');
            button.classList.remove('bg-blue-600', 'hover:bg-blue-700');
            button.setAttribute('disabled', 'disabled');
        } else {
            button.textContent = 'Add';
            button.classList.remove('bg-gray-500');
            button.classList.add('bg-blue-600', 'hover:bg-blue-700');
            button.removeAttribute('disabled');
        }
    });

    // Update upsell buttons
    upsellButtons.forEach(button => {
        const productId = button.dataset.productId;
        let isInCart = false;

        if (hasItems) {
            isInCart = (cartData.items.products && cartData.items.products.some(item => item.id == productId)) ||
                (cartData.items.accessories && cartData.items.accessories.some(item => item.id == productId));
        }

        if (isInCart) {
            button.textContent = 'In cart';
            button.classList.add('bg-gray-500');
            button.classList.remove('bg-blue-600', 'hover:bg-blue-700');
            button.setAttribute('disabled', 'disabled');
        } else {
            button.textContent = 'Add for all members';
            button.classList.remove('bg-gray-500');
            button.classList.add('bg-blue-600', 'hover:bg-blue-700');
            button.removeAttribute('disabled');
        }
    });
}

function updateCartCountShadow(cartData, shadowRoot) {
    const cartCount = shadowRoot.querySelector('.mobilo-cart-count');
    if (cartCount) {
        cartCount.textContent = cartData.items_count || 0;
    }
}

function setButtonLoadingShadow(button, loading) {
    if (loading) {
        button.disabled = true;
        button.textContent = window.mobiloCart.strings.loading;
    } else {
        button.disabled = false;
        button.textContent = button.dataset.originalText || 'Add';
    }
}

function updateButtonStateShadow(button, inCart) {
    if (inCart) {
        button.textContent = 'In Cart';
        button.classList.add('bg-gray-500');
        button.classList.remove('bg-blue-600', 'hover:bg-blue-700');
        button.dataset.originalText = 'In Cart';
        button.setAttribute('disabled', 'disabled');
    } else {
        button.textContent = 'Add';
        button.classList.remove('bg-gray-500');
        button.classList.add('bg-blue-600', 'hover:bg-blue-700');
        button.dataset.originalText = 'Add';
        button.removeAttribute('disabled');
    }
}

function initializeShadowCartState(shadowRoot) {
    // Initialize any shadow DOM specific cart state here
    console.log('Shadow DOM cart initialized');
}

