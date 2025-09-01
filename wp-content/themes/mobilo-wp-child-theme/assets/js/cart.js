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
(function ($) {
    'use strict';

    class MobiloCart {
        constructor() {
            this.init();
        }

        init() {
            this.bindEvents();
            // as default cart data coming from server, we don't need to initialize cart
            // this.initializeCart();
        }

        bindEvents() {
            // Add to cart buttons
            $(document).on('click', '.mobilo-add-to-cart', this.handleAddToCart.bind(this));

            // Update quantity buttons
            $(document).on('click', '.mobilo-quantity-btn', this.handleQuantityUpdate.bind(this));

            // Remove item buttons
            $(document).on('click', '.mobilo-remove-item', this.handleRemoveItem.bind(this));

            // Add upsell for all members
            $(document).on('click', '.mobilo-add-upsell-all', this.handleAddUpsellAll.bind(this));

            // Material selection
            $(document).on('click', '.mobilo-material-btn', this.handleMaterialSelection.bind(this));

            // Card color selection
            $(document).on('click', '.mobilo-card-color', this.handleCardColorSelection.bind(this));

            // Checkout button
            $(document).on('click', '.mobilo-checkout-btn', this.handleCheckout.bind(this));
        }

        initializeCart() {
            // Set initial states
            this.updateCartDisplay();
        }

        handleAddToCart(e) {
            e.preventDefault();

            const $button = $(e.currentTarget);
            const productId = $button.data('product-id');
            const quantity = $button.data('quantity') || 1;
            const variationId = $button.data('variation-id') || 0;
            const variation = $button.data('variation') || {};
            const cardColor = $button.data('card-color') || '';

            if (!productId) {
                this.showMessage('Invalid product', 'error');
                return;
            }

            const data = {
                action: mobiloCart.actions.add_to_cart,
                product_id: productId,
                quantity: quantity,
                variation_id: variationId,
                variation: variation,
                card_color: cardColor,
                _ajaxNonce: mobiloCart.nonce
            };
            this.setButtonLoading($button, true);

            $.ajax({
                url: mobiloCart.ajaxUrl,
                type: 'POST',
                data,
                success: (response) => {
                    if (response.success) {
                        this.updateCartDisplay(response.cart_data);
                        this.showMessage(response.message, 'success');
                        this.updateButtonState($button, true);
                    } else {
                        this.showMessage(response.data || 'Failed to add to cart', 'error');
                    }
                },
                error: (xhr, status, error) => {
                    this.showMessage('An error occurred while adding to cart', 'error');
                    console.error('Add to cart error:', error);
                },
                complete: () => {
                    this.setButtonLoading($button, false);
                }
            });
        }

        handleQuantityUpdate(e) {
            e.preventDefault();

            const $button = $(e.currentTarget);
            const $item = $button.closest('.mobilo-cart-item');
            const cartItemKey = $item.data('cart-item-key');
            if (!cartItemKey) {
                this.showMessage('Invalid item', 'error');
                return;
            }
            const currentQty = parseInt($item.find('.mobilo-quantity').text());
            const isIncrease = $button.hasClass('mobilo-increase');

            let newQty = isIncrease ? currentQty + 1 : currentQty - 1;
            if (newQty < 0) newQty = 0;

            this.updateCartItemQuantity(cartItemKey, newQty);
        }

        handleRemoveItem(e) {
            e.preventDefault();

            const $button = $(e.currentTarget);
            const $item = $button.closest('.mobilo-cart-item');
            const cartItemKey = $item.data('cart-item-key');
            if (!cartItemKey) {
                this.showMessage('Invalid item', 'error');
                return;
            }

            if (confirm('Are you sure you want to remove this item?')) {
                this.removeCartItem(cartItemKey);
            }
        }

        handleAddUpsellAll(e) {
            e.preventDefault();

            const $button = $(e.currentTarget);
            const productId = $button.data('product-id');
            const quantity = $button.data('quantity') || 1;

            this.setButtonLoading($button, true);

            $.ajax({
                url: mobiloCart.ajaxUrl,
                type: 'POST',
                data: {
                    action: mobiloCart.actions.add_upsell_all,
                    product_id: productId,
                    quantity: quantity,
                    _ajaxNonce: mobiloCart.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.updateCartDisplay(response.cart_data);
                        this.showMessage(response.message, 'success');
                    } else {
                        this.showMessage(response.data || 'Failed to add products', 'error');
                    }
                },
                error: (xhr, status, error) => {
                    this.showMessage('An error occurred', 'error');
                    console.error('Add upsell error:', error);
                },
                complete: () => {
                    this.setButtonLoading($button, false);
                }
            });
        }

        handleMaterialSelection(e) {
            e.preventDefault();

            const $button = $(e.currentTarget);
            const material = $button.data('material');
            const $container = $button.closest('.mobilo-product-card');

            // Update active state
            $container.find('.mobilo-material-btn').removeClass('active');
            $button.addClass('active');

            // Update add to cart button with selected material
            const $addButton = $container.find('.mobilo-add-to-cart');
            $addButton.data('variation', { 'attribute_pa_material': material });

            // Update price if available
            const $priceElement = $container.find('.mobilo-product-price');
            const variationPrice = $button.data('price');
            if (variationPrice) {
                $priceElement.text(variationPrice);
            }
        }

        handleCardColorSelection(e) {
            e.preventDefault();

            const $color = $(e.currentTarget);
            const colorName = $color.data('color');
            const $container = $color.closest('.mobilo-product-card');

            // Update active state
            $container.find('.mobilo-card-color').removeClass('active');
            $color.addClass('active');

            // Update add to cart button with selected color
            const $addButton = $container.find('.mobilo-add-to-cart');
            $addButton.data('card-color', colorName);
        }

        handleCheckout(e) {
            e.preventDefault();

            const checkoutUrl = $(e.currentTarget).data('checkout-url') || '/checkout/';
            window.location.href = checkoutUrl;
        }

        updateCartItemQuantity(cartItemKey, quantity) {
            $.ajax({
                url: mobiloCart.ajaxUrl,
                type: 'POST',
                data: {
                    action: mobiloCart.actions.update_cart_quantity,
                    cart_item_key: cartItemKey,
                    quantity: quantity,
                    _ajaxNonce: mobiloCart.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.updateCartDisplay(response.cart_data);
                        this.showMessage(response.message, 'success');
                    } else {
                        this.showMessage(response.data || 'Failed to update cart', 'error');
                    }
                },
                error: (xhr, status, error) => {
                    this.showMessage('An error occurred while updating cart', 'error');
                    console.error('Update cart error:', error);
                }
            });
        }

        removeCartItem(cartItemKey) {
            $.ajax({
                url: mobiloCart.ajaxUrl,
                type: 'POST',
                data: {
                    action: mobiloCart.actions.remove_cart_item,
                    cart_item_key: cartItemKey,
                    _ajaxNonce: mobiloCart.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.updateCartDisplay(response.cart_data);
                        this.showMessage(response.message, 'success');
                    } else {
                        this.showMessage(response.data || 'Failed to remove item', 'error');
                    }
                },
                error: (xhr, status, error) => {
                    this.showMessage('An error occurred while removing item', 'error');
                    console.error('Remove item error:', error);
                }
            });
        }

        updateCartDisplay(cartData = null) {
            if (!cartData) {
                // Fetch current cart data
                $.ajax({
                    url: mobiloCart.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: mobiloCart.actions.get_cart_data,
                        _ajaxNonce: mobiloCart.nonce
                    },
                    success: (response) => {
                        if (response.success) {
                            this.renderCart(response.cart_data);
                        }
                    }
                });
            } else {
                this.renderCart(cartData);
            }
        }

        renderCart(cartData) {
            // Update cart items
            this.renderCartItems(cartData.items);

            // Update cart totals
            this.renderCartTotals(cartData);

            // Update product states
            this.updateProductStates(cartData);

            // Update cart count
            this.updateCartCount(cartData);
        }

        renderCartItems(items) {
            const $cartContainer = $('.mobilo-cart-items');
            if (!$cartContainer.length) return;

            let html = '';

            // Render products
            if (items.products && items.products.length > 0) {
                items.products.forEach(item => {
                    html += this.renderCartItem(item);
                });
            }

            // Render accessories
            if (items.accessories && items.accessories.length > 0) {
                items.accessories.forEach(item => {
                    html += this.renderCartItem(item);
                });
            }

            $cartContainer.html(html);
        }

        renderCartItem(item) {
            return `
                <div class="flex justify-between items-center mb-4 mobilo-cart-item" data-cart-item-key="${item.item_key}">
                    <div>
                        <p class="font-medium text-gray-700">${item.name}</p>
                        ${item.card_color ? `<p class="text-sm text-gray-500">${item.card_color}</p>` : ''}
                    </div>
                    <div class="flex items-center gap-2">
                        <p class="font-bold text-gray-800">${mobiloCart.currency_symbol}${item.subtotal}</p>
                        <div class="flex items-center border rounded-md">
                            <button class="mobilo-quantity-btn mobilo-decrease px-2 py-1 text-gray-500">-</button>
                            <span class="mobilo-quantity px-2 py-1">${item.quantity}</span>
                            <button class="mobilo-quantity-btn mobilo-increase px-2 py-1 text-gray-500">+</button>
                        </div>
                        <button class="mobilo-remove-item ml-2 text-red-500 hover:text-red-700">
                            <span class="material-icons text-sm">delete</span>
                        </button>
                    </div>
                </div>
            `;
        }

        renderCartTotals(cartData) {
            const $totalElement = $('.mobilo-cart-total');
            if ($totalElement.length) {
                $totalElement.text(`${mobiloCart.currency_symbol}${cartData.total}`);
            }

            const $oneTimeElement = $('.mobilo-one-time');
            if ($oneTimeElement.length && cartData.one_time) {
                $oneTimeElement.text(`${mobiloCart.currency_symbol}${cartData.one_time}`);
            }

            const $perYearElement = $('.mobilo-per-year');
            if ($perYearElement.length && cartData.per_year) {
                $perYearElement.text(`${mobiloCart.currency_symbol}${cartData.per_year}`);
            }
        }

        updateProductStates(cartData) {
            // Update add to cart buttons based on what's in cart
            $('.mobilo-add-to-cart').each(function () {
                const $button = $(this);
                const productId = $button.data('product-id');
                const isInCart = cartData.items.products.some(item => item.id == productId) ||
                    cartData.items.accessories.some(item => item.id == productId);

                if (isInCart) {
                    $button.text('In Cart').addClass('bg-gray-500').removeClass('bg-blue-600 hover:bg-blue-700');
                } else {
                    $button.text('Add').removeClass('bg-gray-500').addClass('bg-blue-600 hover:bg-blue-700');
                }
            });
        }

        updateCartCount(cartData) {
            const $cartCount = $('.mobilo-cart-count');
            if ($cartCount.length) {
                $cartCount.text(cartData.items_count || 0);
            }
        }

        setButtonLoading($button, loading) {
            if (loading) {
                $button.prop('disabled', true).text(mobiloCart.strings.loading);
            } else {
                $button.prop('disabled', false).text($button.data('original-text') || 'Add');
            }
        }

        updateButtonState($button, inCart) {
            if (inCart) {
                $button.text('In Cart')
                    .addClass('bg-gray-500')
                    .removeClass('bg-blue-600 hover:bg-blue-700')
                    .data('original-text', 'In Cart');
                $button.prop('disabled', true);
            } else {
                $button.text('Add')
                    .removeClass('bg-gray-500')
                    .addClass('bg-blue-600 hover:bg-blue-700')
                    .data('original-text', 'Add');
                $button.prop('disabled', false);
            }
        }

        showMessage(message, type = 'info') {
            // Create or update message element
            let $message = $('.mobilo-message');
            if (!$message.length) {
                $message = $('<div class="mobilo-message fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg"></div>');
                $('body').append($message);
            }

            const bgColor = type === 'success' ? 'bg-green-500' :
                type === 'error' ? 'bg-red-500' : 'bg-blue-500';

            $message.removeClass('bg-green-500 bg-red-500 bg-blue-500')
                .addClass(bgColor)
                .text(message)
                .fadeIn();

            // Auto hide after 3 seconds
            setTimeout(() => {
                $message.fadeOut();
            }, 3000);
        }
    }

    // Initialize when document is ready
    $(document).ready(function () {
        new MobiloCart();
    });

})(jQuery);

// Cart functionality
document.addEventListener('DOMContentLoaded', function () {
    // Initialize cart state
    let cartState = {
        items: {
            'custom-card': { quantity: 1, price: 99.95, material: 'metal', color: 'blue' },
            'key-fob': { quantity: 1, price: 2.50 },
            'smart-button': { quantity: 1, price: 2.50 }
        },
        proPlan: { price: 0.00 }
    };

    // Material selection
    const materialButtons = document.querySelectorAll('[data-material]');
    materialButtons.forEach(button => {
        button.addEventListener('click', function () {
            const material = this.dataset.material;

            // Remove active class from all buttons
            materialButtons.forEach(btn => {
                btn.classList.remove('active');
            });

            // Add active class to clicked button
            this.classList.add('active');

            // Update cart state
            cartState.items['custom-card'].material = material;
            updateCartDisplay();
        });
    });

    // Color selection
    const colorButtons = document.querySelectorAll('[data-color]');
    colorButtons.forEach(button => {
        button.addEventListener('click', function () {
            const color = this.dataset.color;

            // Remove active class from all color buttons
            colorButtons.forEach(btn => {
                btn.classList.remove('active');
            });

            // Add active class to clicked button
            this.classList.add('active');

            // Update cart state
            cartState.items['custom-card'].color = color;
            updateCartDisplay();
        });
    });

    // Quantity controls
    const quantityControls = document.querySelectorAll('[data-quantity-control]');
    quantityControls.forEach(control => {
        const minusBtn = control.querySelector('[data-action="decrease"]');
        const plusBtn = control.querySelector('[data-action="increase"]');
        const quantityDisplay = control.querySelector('[data-quantity]');
        const itemId = control.dataset.itemId;

        if (minusBtn && plusBtn && quantityDisplay) {
            minusBtn.addEventListener('click', function () {
                if (cartState.items[itemId].quantity > 1) {
                    cartState.items[itemId].quantity--;
                    quantityDisplay.textContent = cartState.items[itemId].quantity;
                    updateCartDisplay();
                }
            });

            plusBtn.addEventListener('click', function () {
                cartState.items[itemId].quantity++;
                quantityDisplay.textContent = cartState.items[itemId].quantity;
                updateCartDisplay();
            });
        }
    });

    // Remove item buttons
    const removeButtons = document.querySelectorAll('[data-action="remove"]');
    removeButtons.forEach(button => {
        button.addEventListener('click', function () {
            const itemId = this.dataset.itemId;
            if (cartState.items[itemId]) {
                delete cartState.items[itemId];
                updateCartDisplay();
                // Remove the item from DOM
                const itemElement = this.closest('[data-cart-item]');
                if (itemElement) {
                    itemElement.remove();
                }
            }
        });
    });

    // Add to cart buttons
    const addButtons = document.querySelectorAll('[data-action="add-to-cart"]');
    addButtons.forEach(button => {
        button.addEventListener('click', function () {
            const itemId = this.dataset.itemId;
            const price = parseFloat(this.dataset.price);

            if (!cartState.items[itemId]) {
                cartState.items[itemId] = { quantity: 1, price: price };
            } else {
                cartState.items[itemId].quantity++;
            }

            updateCartDisplay();
            showNotification('Item added to cart!');
        });
    });

    // Checkout button
    const checkoutButton = document.querySelector('[data-action="checkout"]');
    if (checkoutButton) {
        checkoutButton.addEventListener('click', function () {
            const total = calculateTotal();
            showNotification(`Proceeding to checkout. Total: $${total.toFixed(2)}`);
            // Here you would typically redirect to checkout page
        });
    }

    // Calculate total
    function calculateTotal() {
        let total = 0;
        Object.values(cartState.items).forEach(item => {
            total += item.price * item.quantity;
        });
        total += cartState.proPlan.price;
        return total;
    }

    // Update cart display
    function updateCartDisplay() {
        const totalElement = document.querySelector('[data-cart-total]');
        if (totalElement) {
            const total = calculateTotal();
            totalElement.textContent = `$${total.toFixed(2)}`;
        }
    }

    // Show notification
    function showNotification(message) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full';
        notification.textContent = message;

        document.body.appendChild(notification);

        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);

        // Remove after 3 seconds
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }

    // Initialize display
    updateCartDisplay();
});

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
                this.disabled = false;
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
            handleAddUpsellAllShadow(e, shadowRoot);
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

    updateCartItemQuantityShadow(cartItemKey, newQty, shadowRoot);
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
            html += renderCartItemShadow(item);
        });
    }

    cartContainer.innerHTML = html;

    // Re-bind events for new items
    bindShadowEvents(shadowRoot);
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
                                            <button class="mobilo-quantity-btn mobilo-decrease" data-action="decrease">-</button>
                                            <span class="mobilo-quantity" data-quantity>${item.quantity}</span>
                                            <button class="mobilo-quantity-btn mobilo-increase" data-action="increase">+</button>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <button class="text-gray-600 hover:text-gray-900 mobilo-remove-item" 
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
        } else {
            button.textContent = 'Add';
            button.classList.remove('bg-gray-500');
            button.classList.add('bg-blue-600', 'hover:bg-blue-700');
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
        button.disabled = true;
    } else {
        button.textContent = 'Add';
        button.classList.remove('bg-gray-500');
        button.classList.add('bg-blue-600', 'hover:bg-blue-700');
        button.dataset.originalText = 'Add';
        button.disabled = false;
    }
}

function initializeShadowCartState(shadowRoot) {
    // Initialize any shadow DOM specific cart state here
    console.log('Shadow DOM cart initialized');
}

