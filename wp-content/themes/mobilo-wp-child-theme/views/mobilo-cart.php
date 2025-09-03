<?php
/**
 * Template Name: Mobilo Dynamic Cart
 * Used on mobilo_cart shortcode with dynamic data
 */

// Extract data from shortcode
$products = $cartData['products'] ?? [];
$upsell_products = $cartData['upsell_products'] ?? [];
$cart_data = $cartData['cart_data'] ?? [];
$currency = $cartData['currency'] ?? 'USD';
$currency_symbol = $cartData['currency_symbol'] ?? '$';
$plan = $cartData['plan'] ?? [];
$cart_license = $cart_data['cart_license'][0] ?? [] ;
?>

<!-- Main Content -->
<main id="mobilo-cart-dynamic" class="flex justify-center mx-auto">
    <div class="flex gap-8 justify-center">
        <!-- Left Column - Product Selection -->
        <div class="flex" style="flex-direction: column;">
            <!-- Choose your card section -->
            <div>
                <h2 class="text-3xl font-bold text-gray-900 mb-6 m-0"><?php _e('Choose your card', 'mobilo'); ?></h2>
                <div class="space-y-5">
                    <!-- Main Products -->
                    <?php foreach ($products as $product): ?>
                        <div class="card product-card mobilo-product-card flex gap-5">
                            <div class="flex gap-10">
                                <?php if (!empty($product['thumbnail'])): ?>
                                    <img decoding="async" src="<?php echo esc_url($product['thumbnail']); ?>" 
                                         alt="<?php echo esc_attr($product['name']); ?>" 
                                         class="w-72 h-52 object-cover rounded">
                                <?php else: ?>
                                    <div class="w-72 h-52 bg-gray-200 rounded flex items-center justify-center">
                                        <img decoding="async" alt="<?php echo esc_attr($product['name']); ?>" src="<?= MOBILO_THEME_URL ?>/assets/images/custom-card.png"  class="w-72 h-52 object-cover rounded">
                                        <!-- <span class="text-gray-400"><?php _e('No image', 'mobilo'); ?></span> -->
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 space-y-4">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900 m-0"><?php echo esc_html($product['name']); ?></h3>
                                    <!-- <?php if (!empty($product['short_description'])): ?>
                                        <div class="space-y-1 mt-1">
                                            <p class="text-base font-bold text-gray-900 m-0"><?php echo esc_html($product['short_description']); ?></p>
                                        </div>
                                    <?php endif; ?> -->
                                </div>

                                <?php if (!empty($product['features'])): ?>
                                    <div class="space-y-3">
                                        <div class="space-y-1.5">
                                            <?php foreach ($product['features'] as $feature): ?>
                                                <div class="flex items-center gap-2">
                                                    <img src="<?= MOBILO_THEME_URL ?>/assets/images/check.svg" class="w-4 h-4">
                                                    <span class="text-base text-gray-900"><?php echo esc_html($feature); ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>

                                        <hr class="border-gray-200">

                                        <div class="flex items-center gap-2">
                                            <div class="w-5 h-5 bg-white rounded flex items-center justify-center">
                                                <img src="<?= MOBILO_THEME_URL ?>/assets/images/shipping.svg" alt="Shipping" class="w-6 h-6">
                                            </div>
                                            <span class="text-base text-gray-900 m-0"><?php echo esc_html($product['shipping_info'] ?? __('Ships within 48 hours', 'mobilo')); ?></span>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Material Selection for Variable Products -->
                                <?php if (isset($product['variations']) && !empty($product['variations'])): ?>
                                    <div class="mt-5 space-y-3">
                                        <p class="text-base font-bold text-gray-900 m-0"><?php _e('Card material:', 'mobilo'); ?></p>
                                        <div class="flex gap-2">
                                            <?php foreach ($product['variations'] as $material => $variation): ?>
                                                <button class="material-btn <?php echo ($material === $product['default_attribute']) ? 'active' : ''; ?>" 
                                                        data-material="<?php echo esc_attr($material); ?>"
                                                        data-price="<?php echo esc_attr($variation['price']); ?>">
                                                    <?php echo esc_html(ucfirst($material)); ?>
                                                </button>
                                            <?php endforeach; ?>
                                        </div>

                                        <!-- Color Selection -->
                                        <?php 
                                        $default_variation = $product['variations'][$product['default_attribute']] ?? null;
                                        if ($default_variation && isset($default_variation['card_colors'])): ?>
                                            <div class="flex gap-2 mt-4">
                                                <?php foreach ($default_variation['card_colors'] as $color_name => $color_image): ?>
                                                    <div class="color-btn <?php echo ($color_name === 'Silver') ? 'active' : ''; ?>" 
                                                         data-color="<?php echo esc_attr($color_name); ?>">
                                                        <img src="<?php echo esc_url($color_image); ?>" 
                                                             alt="<?php echo esc_attr($color_name); ?>" 
                                                             class="w-8 h-8 rounded-full border-2">
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <div class="flex justify-between items-center mt-3">
                                    <div class="text-right">
                                        <?php if ($product['base_price'] !== $product['price']): ?>
                                            <span class="text-base text-gray-600 line-through"><?php echo $currency_symbol; ?><?php echo esc_html($product['base_price']); ?></span>
                                        <?php endif; ?>
                                        <span class="text-base font-bold text-gray-900 ml-3 m-0 mobilo-product-price"><?php echo $currency_symbol; ?><?php echo esc_html($product['price']); ?></span>
                                    </div>
                                    <button class="btn-primary mobilo-add-to-cart <?php echo $product['in_cart'] ? 'bg-gray-500' : ''; ?>" 
                                            <?php echo $product['in_cart'] ? 'disabled' : ''; ?>
                                            data-product-id="<?php echo esc_attr($product['id']); ?>"
                                            data-quantity="1"
                                            <?php if (isset($product['variations'])): ?>
                                                data-variation-id="<?php echo esc_attr($product['variations'][$product['default_attribute']]['id'] ?? ''); ?>"
                                                data-variation="<?php echo esc_attr(json_encode(['attribute_pa_material' => $product['default_attribute']])); ?>"
                                            <?php endif; ?>
                                            <?php if (isset($default_variation['card_colors'])): ?>
                                                data-card-color="Silver"
                                            <?php endif; ?>>
                                        <?php echo $product['in_cart'] ? __('In Cart', 'mobilo') : __('Add', 'mobilo'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Upsell Products Section -->
            <?php if (!empty($upsell_products)): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-6 md:max-w-[702px]">
                    <?php foreach ($upsell_products as $product): ?>
                        <?php 
                        // Check if this upsell product is already in cart
                        // $is_in_cart = mc_is_product_in_cart($product['id']);
                        // TODO: note: is is already handled in cart.js
                        $is_in_cart = false;
                        ?>
                        <div class="bg-white p-6 rounded-lg shadow-sm">
                            <div class="flex justify-start mb-4">
                                <?php if (!empty($product['thumbnail'])): ?>
                                    <img decoding="async" alt="<?php echo esc_attr($product['name']); ?>" class="h-28 w-auto"
                                        src="<?php echo esc_url($product['thumbnail']); ?>">
                                <?php else: ?>
                                    <div class="h-28 w-28 bg-gray-200 rounded-lg flex items-center justify-center">
                                        <span class="text-gray-400"><?php _e('No image', 'mobilo'); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 m-0"><?php echo esc_html($product['name']); ?></h3>
                                <p class="text-base text-gray-600 leading-relaxed mt-5">
                                    <?php echo esc_html($product['short_description'] ?? __('Stick to the back of your phone and always be ready to connect.', 'mobilo')); ?>
                                </p>
                            </div>
                            <div class="flex justify-between items-center mt-8">
                                <p class="text-base font-bold text-gray-800 m-0">
                                    <span class="font-light">1x</span><?php echo $currency_symbol; ?><?php echo esc_html($product['price']); ?>
                                </p>
                                <button class="mobilo-add-upsell-all btn-primary <?php echo $is_in_cart ? 'bg-gray-500' : ''; ?>" 
                                        data-product-id="<?php echo esc_attr($product['id']); ?>" 
                                        data-quantity="1"
                                        <?php echo $is_in_cart ? 'disabled' : ''; ?>>
                                    <?php echo $is_in_cart ? __('In cart', 'mobilo') : __('Add for all members', 'mobilo'); ?>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Column - Cart Summary -->
        <div class="cart-card">
            <div class="card space-y-5">
                <!-- Empty Cart Section -->
                <div class="flex items-center gap-3 <?php echo $cart_data['is_cart_empty'] ? '' : 'hidden'; ?>" id="mobilo-empty-cart">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center">
                        <img src="<?= MOBILO_THEME_URL ?>/assets/images/empty-cart.svg" alt="Card" class="">
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 m-0">Please select a card to continue.</p>
                    </div>
                </div>
                <!-- Products Section -->
                <div class="flex items-center gap-3 <?php echo $cart_data['is_cart_empty'] ? 'hidden' : ''; ?>" id="mobilo-products-section">
                    <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center">
                        <img src="<?= MOBILO_THEME_URL ?>/assets/images/card.svg" alt="Card" class="w-6 h-6">
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 m-0">Products</h3>
                        <p class="text-sm text-gray-600 m-0">Cards & Accessories</p>
                    </div>
                </div>

                <div class="non-empty-cart <?php echo $cart_data['is_cart_empty'] ? 'hidden' : ''; ?>" id="mobilo-non-empty-cart">
                    <!-- Cart Items -->
                    <div class="space-y-5 mobilo-cart-items">
                        <?php if (!empty($cart_data['items']['products'])): ?>
                            <?php foreach ($cart_data['items']['products'] as $item): ?>
                                <div class="flex justify-between items-center mb-3 mobilo-cart-item" data-cart-item-key="<?php echo esc_attr($item['item_key']); ?>">
                                    <div class="flex items-center gap-3">
                                        <div>
                                            <h4 class="text-base font-bold text-gray-900 m-0"><?php echo esc_html($item['name']); ?></h4>
                                            <?php if (!empty($item['card_color'])): ?>
                                                <p class="text-sm text-gray-600 m-0"><?php echo esc_html($item['card_color']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-5">
                                        <div class="text-right flex flex-col gap-1">
                                            <span class="text-base font-bold text-gray-900 m-0"><?php echo $currency_symbol; ?><?php echo esc_html($item['subtotal']); ?></span>
                                            <div class="input-quantity" data-quantity-control data-item-id="<?php echo esc_attr($item['item_key']); ?>">
                                                <button class="mobilo-quantity-btn mobilo-decrease cursor-pointer" data-action="decrease">-</button>
                                                <span class="mobilo-quantity" data-quantity><?php echo esc_html($item['quantity']); ?></span>
                                                <button class="mobilo-quantity-btn mobilo-increase cursor-pointer" data-action="increase">+</button>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <button class="text-gray-600 hover:text-gray-900 mobilo-remove-item cursor-pointer" 
                                                    data-action="remove"
                                                    data-cart-item-key="<?php echo esc_attr($item['item_key']); ?>">
                                                <img src="<?= MOBILO_THEME_URL ?>/assets/images/delete.svg" alt="Delete" class="w-4 h-4">
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <?php if (!empty($cart_data['items']['accessories'])): ?>
                            <?php foreach ($cart_data['items']['accessories'] as $item): ?>
                                <div class="flex justify-between items-center mb-3 mobilo-cart-item" data-cart-item-key="<?php echo esc_attr($item['item_key']); ?>">
                                    <div class="flex items-center gap-3">
                                        <div>
                                            <h4 class="text-base font-bold text-gray-900 m-0"><?php echo esc_html($item['name']); ?></h4>
                                            <p class="text-sm text-gray-600 m-0"><?php echo esc_html($item['quantity']); ?> units</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-5">
                                        <div class="text-right">
                                            <span class="text-base font-bold text-gray-900"><?php echo $currency_symbol; ?><?php echo esc_html($item['subtotal']); ?></span>
                                        </div>
                                        <button class="text-gray-600 hover:text-gray-900 mobilo-remove-item cursor-pointer" 
                                                data-action="remove"
                                                data-cart-item-key="<?php echo esc_attr($item['item_key']); ?>">
                                            <img src="<?= MOBILO_THEME_URL ?>/assets/images/delete.svg" alt="Delete" class="w-4 h-4">
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <hr class="border-gray-200 cart-license-divider">

                    <!-- Plan Information -->
                    <?php if (isset($cart_license) && !empty($cart_license)): ?>
                        <div class="flex justify-between items-center my-3" id="mobilo-cart-license">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center">
                                    <img src="<?= MOBILO_THEME_URL ?>/assets/images/team.svg" alt="plan">
                                </div>
                                <div>
                                    <h4 class="text-xl font-bold text-gray-900"><?php echo esc_html($cart_license['name']); ?> Plan</h4>
                                    <p class="text-sm text-gray-600"><?php echo esc_html($cart_license['quantity'] ?? 1); ?> members</p>
                                    <!-- <p class="text-sm text-gray-600">Per employee, billed annually.</p> -->
                                </div>
                            </div>
                            <div class="flex items-center gap-5">
                                <div class="text-right flex flex-col gap-1">
                                    <span class="text-base font-bold text-gray-900"><?php echo $currency_symbol; ?><?php echo esc_html($cart_license['sale_price']); ?></span>
                                    <div class="input-quantity" data-quantity-control data-item-id="plan">
                                        <button class="mobilo-quantity-btn mobilo-decrease cursor-pointer" data-action="decrease">-</button>
                                        <span class="mobilo-quantity mobilo-seat-quantity" data-quantity><?php echo esc_html($cart_license['quantity'] ?? 1); ?></span>
                                        <button class="mobilo-quantity-btn mobilo-increase cursor-pointer" data-action="increase">+</button>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <button class="text-gray-600 hover:text-gray-900 mobilo-remove-item cursor-pointer" 
                                            data-action="remove"
                                            data-cart-item-key="<?php echo esc_attr($cart_license['item_key']); ?>">
                                        <img src="<?= MOBILO_THEME_URL ?>/assets/images/delete.svg" alt="Delete" class="w-4 h-4">
                                    </button>
                                </div>
                            </div>
                            
                        </div>
                    <?php endif; ?>

                </div>
                
                <hr class="border-gray-200">

                <!-- Order Total -->
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <h3 class="text-base font-bold text-gray-900"><?php _e('Order Total', 'mobilo'); ?></h3>
                        <span class="text-xl font-bold text-gray-900 mobilo-cart-total" data-cart-total><?php echo $currency_symbol; ?><?php echo esc_html($cart_data['total'] ?? '0.00'); ?></span>
                    </div>

                    <div class="space-y-3">
                        <a href="<?php echo esc_url(wc_get_checkout_url()); ?>" 
                        <?php echo $cart_data['is_cart_empty'] ? 'disabled="disabled"' : ''; ?>
                        >    
                        <button class="w-full btn-primary text-lg py-4 px-8 h-14 mobilo-checkout-btn" 
                                data-action="checkout"
                                data-checkout-url="<?php echo esc_url(wc_get_checkout_url()); ?>">
                            <?php _e('Checkout', 'mobilo'); ?>
                        </button>
                        </a>

                        <?php if (isset($cart_data['one_time']) && $cart_data['one_time'] !== '0.00'): ?>
                            <p class="text-sm text-gray-600 text-center mt-3">
                                (<span class="mobilo-one-time"><?php echo $currency_symbol; ?><?php echo esc_html($cart_data['one_time']); ?></span> <?php _e('one-time', 'mobilo'); ?>, 
                                <span class="mobilo-per-year"><?php echo $currency_symbol; ?><?php echo esc_html($cart_data['per_year'] ?? '0.00'); ?></span> <?php _e('per year', 'mobilo'); ?>)
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Info Cards -->
                    <?php if (!empty($cart_data['cart_notes'])): ?>
                        <div class="space-y-2 cart-notes">
                            <?php foreach ($cart_data['cart_notes'] as $note_key => $note): ?>
                                <div class="bg-gray-100 rounded px-4 py-3 flex items-center gap-2">
                                    <img src="<?= MOBILO_THEME_URL ?>/assets/images/<?php echo ($note_key === 'shipping') ? 'shipping' : 'pencil-ruler'; ?>.svg" 
                                         alt="<?php echo esc_attr($note_key); ?>" class="w-5 h-5">
                                    <span class="text-sm text-gray-600"><?php echo esc_html($note); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="space-y-2 cart-notes">
                            <div class="bg-gray-100 rounded px-4 py-3 flex items-center gap-2">
                                <img src="<?= MOBILO_THEME_URL ?>/assets/images/shipping.svg" alt="Shipping" class="w-5 h-5">
                                <span class="text-sm text-gray-600"><?php _e('Shipping will be calculated at checkout', 'mobilo'); ?></span>
                            </div>
                            <div class="bg-gray-100 rounded px-4 py-3 flex items-center gap-2">
                                <img src="<?= MOBILO_THEME_URL ?>/assets/images/pencil-ruler.svg" alt="Custom Design" class="w-4 h-4">
                                <span class="text-sm text-gray-600"><?php _e('Custom designs will be created after payment', 'mobilo'); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Plan Details Card -->
            <?php if (isset($plan) && !empty($plan)): ?>
                <div class="mt-6 bg-white rounded-2xl plan-card overflow-hidden">
                    <div class="bg-gray-50 px-8 py-8 text-center">
                        <div class="space-y-2">
                            <h3 class="text-lg font-bold uppercase text-gray-900"><?php echo esc_html($plan['title']); ?></h3>
                            <div class="flex items-end justify-center gap-1">
                                <span class="text-3xl font-bold text-[#181059]"><?php echo $currency_symbol; ?><?php echo esc_html($plan['sale_price']); ?></span>
                                <span class="text-sm text-gray-600">/ <?php echo esc_html($plan['billing_cycle']); ?></span>
                            </div>
                            <p class="text-sm text-gray-600 m-0">1 <?php _e('Per member, billed annually.', 'mobilo'); ?></p>
                        </div>
                        <div class="mt-4 flex flex-col items-center justify-center gap-2">
                            <?php if ($plan['trial_text']): ?>
                                <span class="text-sm font-bold text-gray-900"><?php echo $plan['trial_text']; ?></span>
                            <?php endif; ?>
                            <span class="text-sm text-gray-900 m-0"><?php echo $plan['feature_tagline'] ?? 'All Mobilo features'; ?></span>
                        </div>
                    </div>

                    <div class="p-7 space-y-8">
                        <div class="text-center">
                            <div class="flex items-center justify-center gap-1.5 mb-4">
                                <svg width="12" height="14" viewBox="0 0 12 14" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M8.11177 6.49936C9.00758 5.84603 9.59093 4.7887 9.59093 3.59755C9.59093 1.61817 7.98058 0.0078125 6.00119 0.0078125C4.0218 0.0078125 2.41145 1.61817 2.41145 3.59755C2.41145 4.7887 2.99477 5.84603 3.89061 6.49936C1.66373 7.35152 0.078125 9.51061 0.078125 12.0335C0.078125 13.1221 0.963816 14.0078 2.05248 14.0078H9.9499C11.0386 14.0078 11.9243 13.1221 11.9243 12.0335C11.9243 9.51061 10.3386 7.35152 8.11177 6.49936ZM3.48838 3.59755C3.48838 2.21199 4.61563 1.08475 6.00119 1.08475C7.38675 1.08475 8.514 2.21199 8.514 3.59755C8.514 4.98312 7.38675 6.11039 6.00119 6.11039C4.61563 6.11039 3.48838 4.98312 3.48838 3.59755ZM9.9499 12.9309H2.05248C1.55764 12.9309 1.15506 12.5283 1.15506 12.0334C1.15506 9.36123 3.329 7.18727 6.00122 7.18727C8.67344 7.18727 10.8474 9.36121 10.8474 12.0334C10.8474 12.5283 10.4448 12.9309 9.9499 12.9309Z"
                                        fill="#262626" />
                                </svg>
                                <span class="text-sm text-gray-900"><?php echo esc_html($plan['member_range'] ?? '1-5 members'); ?></span>
                            </div>
                        </div>

                        <!-- Plan Features -->
                        <?php if (isset($plan['features']) && is_array($plan['features'])): ?>
                            <div class="space-y-7">
                                <?php foreach ($plan['features'] as $feature): ?>
                                    <div class="space-y-4">
                                        <h4 class="text-base font-bold text-gray-900"><?php echo esc_html($feature['heading']); ?></h4>
                                        <?php if (isset($feature['contents']) && is_array($feature['contents'])): ?>
                                            <div class="space-y-4">
                                                <?php foreach ($feature['contents'] as $content): ?>
                                                    <div class="flex items-center gap-2">
                                                        <img src="<?= MOBILO_THEME_URL ?>/assets/images/check.svg" class="w-4 h-4">
                                                        <span class="text-base text-gray-900"><?php echo esc_html($content); ?></span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>