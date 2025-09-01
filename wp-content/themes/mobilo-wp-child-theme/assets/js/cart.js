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

    show(message, type = 'info', duration = 3000) {
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

        toast.style.cssText = baseStyles + typeStyles[type] || typeStyles.info;
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
        setTimeout(() => {
            this.remove(toast);
        }, duration);

        // Click to dismiss
        toast.addEventListener('click', () => {
            this.remove(toast);
        });

        return toast;
    }

    remove(toast) {
        if (toast && toast.parentNode) {
            toast.style.transform = 'translateY(-100px)';
            toast.style.opacity = '0';
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
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
    // Apply cart CSS to dynamic cart
    fetchCSS(mobiloCart.themeUrl + '/assets/dist/cart.css').then(css => {
        mainShadow.innerHTML = `<style>${css}</style>${mainContent.innerHTML}`;

        // Initialize cart functionality inside shadow DOM
        initializeShadowCart(mainShadow);
    });
}

// Function to initialize cart functionality inside shadow DOM
function initializeShadowCart(shadowRoot) {
    // Wait for shadow DOM content to be ready
    setTimeout(() => {
        // Re-bind all event listeners to elements inside shadow DOM
        bindShadowEvents(shadowRoot);

        // Initialize cart state for shadow DOM
        initializeShadowCartState(shadowRoot);
    }, 100);
}

// Function to bind events inside shadow DOM
function bindShadowEvents(shadowRoot) {
    // Material selection
    const materialButtons = shadowRoot.querySelectorAll('[data-material]');
    materialButtons.forEach(button => {
        button.addEventListener('click', function () {
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
        });
    });

    // Color selection
    const colorButtons = shadowRoot.querySelectorAll('[data-color]');
    colorButtons.forEach(button => {
        button.addEventListener('click', function () {
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
        });
    });

    // Add to cart buttons
    const addToCartButtons = shadowRoot.querySelectorAll('.mobilo-add-to-cart');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            handleAddToCartShadow(e, shadowRoot);
        });
    });

    // Update quantity buttons
    const quantityButtons = shadowRoot.querySelectorAll('.mobilo-quantity-btn');
    quantityButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            handleQuantityUpdateShadow(e, shadowRoot);
        });
    });

    // Remove item buttons
    const removeButtons = shadowRoot.querySelectorAll('.mobilo-remove-item');
    removeButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            handleRemoveItemShadow(e, shadowRoot);
        });
    });

    // Add upsell buttons
    const upsellButtons = shadowRoot.querySelectorAll('.mobilo-add-upsell-all');
    upsellButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            handleAddToCartShadow(e, shadowRoot);
        });
    });

    // Checkout button
    const checkoutButtons = shadowRoot.querySelectorAll('.mobilo-checkout-btn');
    checkoutButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const checkoutUrl = this.dataset.checkoutUrl || '/checkout/';
            window.location.href = checkoutUrl;
        });
    });
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
    // Update cart items
    renderCartItemsShadow(cartData.items, shadowRoot);

    // Update cart totals
    renderCartTotalsShadow(cartData, shadowRoot);

    // Update product states
    updateProductStatesShadow(cartData, shadowRoot);

    // Update cart count
    // updateCartCountShadow(cartData, shadowRoot);
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
        totalElement.textContent = `${window.mobiloCart.currency_symbol}${cartData.total}`;
    }

    const oneTimeElement = shadowRoot.querySelector('.mobilo-one-time');
    if (oneTimeElement && cartData.one_time) {
        oneTimeElement.textContent = `${window.mobiloCart.currency_symbol}${cartData.one_time}`;
    }

    const perYearElement = shadowRoot.querySelector('.mobilo-per-year');
    if (perYearElement && cartData.per_year) {
        perYearElement.textContent = `${window.mobiloCart.currency_symbol}${cartData.per_year}`;
    }
}

function updateProductStatesShadow(cartData, shadowRoot) {
    // Update add to cart buttons based on what's in cart
    const addToCartButtons = shadowRoot.querySelectorAll('.mobilo-add-to-cart');
    addToCartButtons.forEach(button => {
        const productId = button.dataset.productId;
        const isInCart = cartData.items.products.some(item => item.id == productId) ||
            cartData.items.accessories.some(item => item.id == productId);

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

