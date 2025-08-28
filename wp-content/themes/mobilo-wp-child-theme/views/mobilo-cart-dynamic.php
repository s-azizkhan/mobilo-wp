<?php
/**
 * Template Name: Mobilo Dynamic Cart
 * Used on mobilo_cart shortcode with dynamic data
 */

// Extract data from shortcode
$products = $cartData['products'] ?? [];
$upsell_products = $cartData['upsell_products'] ?? [];
$cart_data = $cartData['cart_data'] ?? [];
$cart_count = $cartData['cart_count'] ?? 0;
$currency = $cartData['currency'] ?? 'USD';
$currency_symbol = $cartData['currency_symbol'] ?? '$';
?>

<!-- add tailwind css -->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
<script src="https://cdn.tailwindcss.com"></script>

<main class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2">
        <h2 class="text-3xl font-bold text-gray-800 mb-8"><?php _e('Choose your card', 'mobilo'); ?></h2>
        <div class="space-y-6">
            
            <!-- Main Products -->
            <?php foreach ($products as $product): ?>
                <div class="mobilo-product-card bg-white p-6 rounded-lg shadow-sm flex flex-col md:flex-row items-center gap-6">
                    <?php if (!empty($product['thumbnail'])): ?>
                        <img alt="<?php echo esc_attr($product['name']); ?>" class="w-48 h-auto" src="<?php echo esc_url($product['thumbnail']); ?>" />
                    <?php else: ?>
                        <div class="w-48 h-32 bg-gray-200 rounded-lg flex items-center justify-center">
                            <span class="material-icons text-gray-400 text-4xl">image</span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="flex-grow">
                        <h3 class="text-xl font-bold text-gray-800"><?php echo esc_html($product['name']); ?></h3>
                        <?php if (!empty($product['short_description'])): ?>
                            <p class="text-gray-500 mb-4"><?php echo esc_html($product['short_description']); ?></p>
                        <?php endif; ?>
                        
                        <?php if (!empty($product['features'])): ?>
                            <ul class="space-y-2 text-gray-600 mb-4">
                                <?php foreach ($product['features'] as $feature): ?>
                                    <li class="flex items-center">
                                        <span class="material-icons text-green-500 mr-2">check</span>
                                        <?php echo esc_html($feature); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        
                        <!-- Material Selection for Variable Products -->
                        <?php if (isset($product['variations']) && !empty($product['variations'])): ?>
                            <div class="flex items-center space-x-4 mb-4">
                                <span class="font-medium text-gray-700"><?php _e('Card material:', 'mobilo'); ?></span>
                                <div class="flex space-x-2">
                                    <?php foreach ($product['variations'] as $material => $variation): ?>
                                        <button class="mobilo-material-btn px-4 py-1 rounded-full text-sm border <?php echo ($material === $product['default_attribute']) ? 'bg-gray-800 text-white' : ''; ?>"
                                                data-material="<?php echo esc_attr($material); ?>"
                                                data-price="<?php echo esc_attr($variation['price']); ?>">
                                            <?php echo esc_html(ucfirst($material)); ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <!-- Card Color Selection -->
                            <?php 
                            $default_variation = $product['variations'][$product['default_attribute']] ?? null;
                            if ($default_variation && isset($default_variation['card_colors'])): ?>
                                <div class="flex items-center space-x-2">
                                    <?php foreach ($default_variation['card_colors'] as $color_name => $color_image): ?>
                                        <img alt="<?php echo esc_attr($color_name); ?> card color" 
                                             class="mobilo-card-color w-8 h-8 rounded-full border-2 <?php echo ($color_name === 'Silver') ? 'border-blue-500' : ''; ?> cursor-pointer"
                                             src="<?php echo esc_url($color_image); ?>"
                                             data-color="<?php echo esc_attr($color_name); ?>" />
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex flex-col items-end self-stretch justify-between w-full md:w-auto">
                        <div class="text-right">
                            <?php if ($product['base_price'] !== $product['price']): ?>
                                <p class="text-gray-400 line-through text-sm"><?php echo $currency_symbol; ?><?php echo esc_html($product['base_price']); ?></p>
                            <?php endif; ?>
                            <p class="text-xl font-bold text-gray-800 mobilo-product-price"><?php echo $currency_symbol; ?><?php echo esc_html($product['price']); ?></p>
                        </div>
                        
                        <button class="mobilo-add-to-cart w-full md:w-auto bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 <?php echo $product['in_cart'] ? 'bg-gray-500' : ''; ?>"
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
            <?php endforeach; ?>
            
            <!-- Upsell Products Grid -->
            <?php if (!empty($upsell_products)): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach ($upsell_products as $product): ?>
                        <div class="bg-white p-6 rounded-lg shadow-sm">
                            <div class="flex justify-center mb-4">
                                <?php if (!empty($product['thumbnail'])): ?>
                                    <img alt="<?php echo esc_attr($product['name']); ?>" class="h-24 w-auto" src="<?php echo esc_url($product['thumbnail']); ?>" />
                                <?php else: ?>
                                    <div class="h-24 w-24 bg-gray-200 rounded-lg flex items-center justify-center">
                                        <span class="material-icons text-gray-400 text-2xl">image</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800 text-center"><?php echo esc_html($product['name']); ?></h3>
                            <p class="text-gray-600 text-center my-2"><?php echo esc_html($product['short_description'] ?? ''); ?></p>
                            <div class="flex justify-between items-center mt-4">
                                <p class="text-lg font-bold text-gray-800">1x <?php echo $currency_symbol; ?><?php echo esc_html($product['price']); ?></p>
                                <button class="mobilo-add-upsell-all bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700"
                                        data-product-id="<?php echo esc_attr($product['id']); ?>"
                                        data-quantity="1">
                                    <?php _e('Add for all members', 'mobilo'); ?>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Cart Sidebar -->
    <div class="lg:col-span-1">
        <div class="bg-white p-6 rounded-lg shadow-sm top-8">
            <div class="border-b pb-4 mb-4">
                <div class="flex justify-between items-center mb-4">
                    <div class="flex items-center gap-4">
                        <div class="p-2 bg-gray-100 rounded-lg">
                            <span class="material-icons text-gray-600">business_center</span>
                        </div>
                        <div>
                            <p class="font-bold text-gray-800"><?php echo esc_html($cart_data['items']['title'] ?? __('Products', 'mobilo')); ?></p>
                            <p class="text-sm text-gray-500"><?php echo esc_html($cart_data['items']['sub_title'] ?? __('Cards & Accessories', 'mobilo')); ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Cart Items -->
                <div class="mobilo-cart-items">
                    <?php if (!empty($cart_data['items']['products'])): ?>
                        <?php foreach ($cart_data['items']['products'] as $item): ?>
                            <div class="flex justify-between items-center mb-4 mobilo-cart-item" data-cart-item-key="<?php echo esc_attr($item['item_key']); ?>">
                                <div>
                                    <p class="font-medium text-gray-700"><?php echo esc_html($item['name']); ?></p>
                                    <?php if (!empty($item['card_color'])): ?>
                                        <p class="text-sm text-gray-500"><?php echo esc_html($item['card_color']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="flex items-center gap-2">
                                    <p class="font-bold text-gray-800"><?php echo $currency_symbol; ?><?php echo esc_html($item['subtotal']); ?></p>
                                    <div class="flex items-center border rounded-md">
                                        <button class="mobilo-quantity-btn mobilo-decrease px-2 py-1 text-gray-500">-</button>
                                        <span class="mobilo-quantity px-2 py-1"><?php echo esc_html($item['quantity']); ?></span>
                                        <button class="mobilo-quantity-btn mobilo-increase px-2 py-1 text-gray-500">+</button>
                                    </div>
                                    <button class="mobilo-remove-item ml-2 text-red-500 hover:text-red-700" data-cart-item-key="<?php echo esc_attr($item['item_key']); ?>">
                                        <span class="material-icons text-sm">delete</span>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <?php if (!empty($cart_data['items']['accessories'])): ?>
                        <?php foreach ($cart_data['items']['accessories'] as $item): ?>
                            <div class="flex justify-between items-center mb-4 mobilo-cart-item" data-cart-item-key="<?php echo esc_attr($item['item_key']); ?>">
                                <div>
                                    <p class="font-medium text-gray-700"><?php echo esc_html($item['name']); ?></p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <p class="font-bold text-gray-800"><?php echo $currency_symbol; ?><?php echo esc_html($item['subtotal']); ?></p>
                                    <div class="flex items-center border rounded-md">
                                        <button class="mobilo-quantity-btn mobilo-decrease px-2 py-1 text-gray-500">-</button>
                                        <span class="mobilo-quantity px-2 py-1"><?php echo esc_html($item['quantity']); ?></span>
                                        <button class="mobilo-quantity-btn mobilo-increase px-2 py-1 text-gray-500">+</button>
                                    </div>
                                    <button class="mobilo-remove-item ml-2 text-red-500 hover:text-red-700">
                                        <span class="material-icons text-sm">delete</span>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Plan Information -->
                <?php if (isset($cart_data['plan'])): ?>
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <span class="material-icons text-gray-600">person</span>
                            <div>
                                <p class="font-medium text-gray-700"><?php echo esc_html($cart_data['plan']['title']); ?></p>
                                <p class="text-sm text-gray-500">
                                    <?php echo esc_html($cart_data['plan']['count_prefix']); ?>
                                    <?php echo esc_html($cart_data['plan']['count']); ?>
                                    <?php echo esc_html($cart_data['plan']['count_suffix']); ?>
                                </p>
                            </div>
                        </div>
                        <p class="font-bold text-gray-800"><?php echo $currency_symbol; ?><?php echo esc_html($cart_data['plan']['sub_total']); ?></p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Cart Totals -->
            <div class="flex justify-between items-center mb-4">
                <p class="font-bold text-lg text-gray-800"><?php _e('Order total:', 'mobilo'); ?></p>
                <p class="font-bold text-lg text-gray-800 mobilo-cart-total"><?php echo $currency_symbol; ?><?php echo esc_html($cart_data['total'] ?? '0.00'); ?></p>
            </div>
            
            <button class="mobilo-checkout-btn w-full bg-blue-600 text-white py-3 rounded-lg font-bold hover:bg-blue-700 mb-4"
                    data-checkout-url="<?php echo esc_url(wc_get_checkout_url()); ?>">
                <?php _e('Checkout', 'mobilo'); ?>
            </button>
            
            <?php if (isset($cart_data['one_time']) && $cart_data['one_time'] !== '0.00'): ?>
                <p class="text-center text-sm text-gray-500 mb-4">
                    <span class="mobilo-one-time"><?php echo $currency_symbol; ?><?php echo esc_html($cart_data['one_time']); ?></span> <?php _e('one-time', 'mobilo'); ?>
                </p>
            <?php endif; ?>
            
            <?php if (isset($cart_data['per_year']) && $cart_data['per_year'] !== '0.00'): ?>
                <p class="text-center text-sm text-gray-500 mb-4">
                    <span class="mobilo-per-year"><?php echo $currency_symbol; ?><?php echo esc_html($cart_data['per_year']); ?></span> <?php _e('per year', 'mobilo'); ?>
                </p>
            <?php endif; ?>
            
            <!-- Cart Notes -->
            <div class="bg-gray-100 p-4 rounded-lg text-gray-600 text-sm space-y-2">
                <?php if (!empty($cart_data['cart_notes'])): ?>
                    <?php foreach ($cart_data['cart_notes'] as $note_key => $note): ?>
                        <div class="flex items-start gap-2">
                            <span class="material-icons mt-1">
                                <?php echo ($note_key === 'shipping') ? 'local_shipping' : 'palette'; ?>
                            </span>
                            <p><?php echo esc_html($note); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Plan Details -->
        <?php if (isset($cart_data['plan'])): ?>
            <div class="bg-white p-6 rounded-lg shadow-sm mt-8">
                <p class="text-sm text-gray-500 mb-2"><?php _e('Chosen plan', 'mobilo'); ?></p>
                <p class="font-bold text-gray-800 mb-2"><?php echo esc_html($cart_data['plan']['title']); ?></p>
                <h2 class="text-4xl font-bold text-gray-800 mb-4"><?php echo $currency_symbol; ?><?php echo esc_html($cart_data['plan']['price']); ?></h2>
                <button class="w-full border border-gray-300 text-gray-700 py-2 rounded-lg font-medium hover:bg-gray-100 mb-4">
                    <?php _e('All Mobilo features', 'mobilo'); ?>
                </button>
                <div class="text-center text-sm text-gray-500 mb-6 flex items-center justify-center gap-2">
                    <span class="material-icons">group</span>
                    <span><?php echo esc_html($cart_data['plan']['count']); ?> <?php _e('members', 'mobilo'); ?></span>
                </div>
                
                <!-- Plan Features -->
                <div class="space-y-4 text-sm">
                    <div>
                        <h4 class="font-bold text-gray-800 mb-2"><?php _e('Contact Sharing', 'mobilo'); ?></h4>
                        <ul class="space-y-2 text-gray-600">
                            <li class="flex items-center"><span class="material-icons text-green-500 mr-2">check</span><?php _e('Unlimited taps/card shares', 'mobilo'); ?></li>
                            <li class="flex items-center"><span class="material-icons text-green-500 mr-2">check</span><?php _e('Personalized business card templates', 'mobilo'); ?></li>
                            <li class="flex items-center"><span class="material-icons text-green-500 mr-2">check</span><?php _e('Apple/Google QR Code Widget', 'mobilo'); ?></li>
                            <li class="flex items-center"><span class="material-icons text-green-500 mr-2">check</span><?php _e('Digital Wallet Card', 'mobilo'); ?></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800 mb-2"><?php _e('Lead management', 'mobilo'); ?></h4>
                        <ul class="space-y-2 text-gray-600">
                            <li class="flex items-center"><span class="material-icons text-green-500 mr-2">check</span><?php _e('Unlimited lead capture', 'mobilo'); ?></li>
                            <li class="flex items-center"><span class="material-icons text-green-500 mr-2">check</span><?php _e('4,000+ CRM integrations with Zapier', 'mobilo'); ?></li>
                            <li class="flex items-center"><span class="material-icons text-green-500 mr-2">check</span><?php _e('Native integrations', 'mobilo'); ?></li>
                            <li class="flex items-center"><span class="material-icons text-green-500 mr-2">check</span><?php _e('Mobilo AI: Lead Scoring & more', 'mobilo'); ?></li>
                            <li class="flex items-center"><span class="material-icons text-green-500 mr-2">check</span><?php _e('Business Card Scanner', 'mobilo'); ?></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800 mb-2"><?php _e('Team management and reporting', 'mobilo'); ?></h4>
                        <ul class="space-y-2 text-gray-600">
                            <li class="flex items-center"><span class="material-icons text-green-500 mr-2">check</span><?php _e('Brand governance: control employee profiles', 'mobilo'); ?></li>
                            <li class="flex items-center"><span class="material-icons text-green-500 mr-2">check</span><?php _e('Create Departments, Groups, Office Locations', 'mobilo'); ?></li>
                            <li class="flex items-center"><span class="material-icons text-green-500 mr-2">check</span><?php _e('Team insights & analytics', 'mobilo'); ?></li>
                            <li class="flex items-center"><span class="material-icons text-green-500 mr-2">check</span><?php _e('Malware link checker', 'mobilo'); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>
