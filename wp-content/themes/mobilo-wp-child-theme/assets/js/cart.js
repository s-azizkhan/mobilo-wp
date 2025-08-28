/**
 * Mobilo Cart JavaScript
 * Handles all cart interactions and dynamic updates
 */
(function ($) {
    'use strict';

    class MobiloCart {
        constructor() {
            this.init();
        }

        init() {
            this.bindEvents();
            this.initializeCart();
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
                        <p class="font-bold text-gray-800">${mobiloCart.currency_symbol} ${item.subtotal}</p>
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
            } else {
                $button.text('Add')
                    .removeClass('bg-gray-500')
                    .addClass('bg-blue-600 hover:bg-blue-700')
                    .data('original-text', 'Add');
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
