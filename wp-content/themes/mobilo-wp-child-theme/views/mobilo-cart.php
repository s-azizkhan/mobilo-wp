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
$plan = $cartData['plan'] ?? [];
?>

<!-- Main Content -->
<main id="mobilo-cart-dynamic" class="flex justify-center mx-auto">
    <div class="flex gap-8">
        <!-- Left Column - Product Selection -->
        <div class="flex" style="flex-direction: column;">
            <!-- Choose your card section -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-6 m-0">Choose your card</h2>
                <div class="space-y-5">
                    <!-- Mobilo Branded Card -->
                    <div class="card product-card flex">
                        <div class="flex gap-10">
                            <img src="<?= MOBILO_THEME_URL ?>/assets/images/branded-card-7f25c4.png"
                                alt="Mobilo Branded Card" class="w-80 h-52 object-cover rounded">
                        </div>
                        <div class="flex-1 space-y-4">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900 m-0">Mobilo Branded Card</h3>
                                <div class="space-y-1 mt-1">
                                    <p class="text-base font-bold text-gray-900 m-0">+ Free Key Fob</p>
                                    <p class="text-base font-bold text-gray-900 m-0">+ Free Smart Button</p>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div class="space-y-1.5">
                                    <div class="flex items-center gap-2">
                                        <img src="<?= MOBILO_THEME_URL ?>/assets/images/check.svg" class="w-4 h-4>
                        <span class=" text-base text-gray-900">Works with Apple & Android</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <img src="<?= MOBILO_THEME_URL ?>/assets/images/check.svg" class="w-4 h-4>
                        <span class=" text-base text-gray-900">NFC/RFID enabled</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <img src="<?= MOBILO_THEME_URL ?>/assets/images/check.svg" class="w-4 h-4>
                        <span class=" text-base text-gray-900">QR Code for older phones</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <img src="<?= MOBILO_THEME_URL ?>/assets/images/check.svg" class="w-4 h-4>
                        <span class=" text-base text-gray-900">Unlimited uses</span>
                                    </div>
                                </div>

                                <hr class="border-gray-200">

                                <div class="flex items-center gap-2">
                                    <div class="w-5 h-5 bg-white rounded flex items-center justify-center">
                                        <img src="<?= MOBILO_THEME_URL ?>/assets/images/shipping.svg" alt="Shipping"
                                            class="w-6 h-6">

                                    </div>
                                    <span class="text-base text-gray-900 m-0">Ships the same day</span>
                                </div>
                            </div>
                            <div class="flex justify-between items-center mt-3">
                                <div class="text-right">
                                    <span class="text-base font-bold text-gray-900 m-0">$9.99</span>
                                </div>
                                <button class="btn-primary" data-action="add-to-cart" data-item-id="branded-card"
                                    data-price="9.99">
                                    Add
                                </button>
                            </div>
                        </div>

                    </div>

                    <!-- Custom Designed Card -->
                    <div class="card product-card flex">
                        <div class="flex gap-10">
                            <img src="<?= MOBILO_THEME_URL ?>/assets/images/custom-card.png" alt="Custom Designed Card"
                                class="w-80 h-52 object-cover rounded">
                        </div>
                        <div class="flex-1 space-y-4">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900 m-0">Custom Designed Card</h3>
                            </div>

                            <div class="space-y-3">
                                <div class="space-y-1.5">
                                    <div class="flex items-center gap-2">
                                        <img src="<?= MOBILO_THEME_URL ?>/assets/images/check.svg" class="w-4 h-4>
                        <span class=" text-base text-gray-900">NFC chip</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <img src="<?= MOBILO_THEME_URL ?>/assets/images/check.svg" class="w-4 h-4
                        <span class=" text-base text-gray-900">Works with Apple & Android</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <img src="<?= MOBILO_THEME_URL ?>/assets/images/check.svg" class="w-4 h-4>
                        <span class=" text-base text-gray-900">Unlimited uses</span>
                                    </div>
                                </div>

                                <hr class="border-gray-200">

                                <div class="flex items-center gap-2">
                                    <div class="w-5 h-5 bg-white rounded flex items-center justify-center">
                                        <img src="<?= MOBILO_THEME_URL ?>/assets/images/shipping.svg" alt="Shipping"
                                            class="w-6 h-6">
                                    </div>
                                    <span class="text-base text-gray-900">Ships within 48 hours</span>
                                </div>
                            </div>
                            <!-- Card Material Selection -->
                            <div class="mt-5 space-y-3">
                                <p class="text-base font-bold text-gray-900 m-0">Card material:</p>
                                <div class="flex gap-2">
                                    <button class="material-btn" data-material="classic">
                                        Classic
                                    </button>
                                    <button class="material-btn active" data-material="metal">
                                        Metal
                                    </button>
                                    <button class="material-btn" data-material="wood">
                                        Wood
                                    </button>
                                </div>

                                <!-- Color Selection -->
                                <div class="flex gap-2 mt-4">
                                    <div class="color-btn" data-color="blue">
                                        <div class="color-fill color-blue"></div>
                                    </div>
                                    <div class="color-btn" data-color="green">
                                        <div class="color-fill color-green"></div>
                                    </div>
                                    <div class="color-btn active" data-color="red">
                                        <div class="color-fill color-red"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-between items-center mt-3">
                                <div class="text-right">
                                    <span class="text-base text-gray-600 line-through">$200</span>
                                    <span class="text-base font-bold text-gray-900 ml-3 m-0">$99.99</span>
                                </div>
                                <button class="btn-primary" data-action="add-to-cart" data-item-id="custom-card"
                                    data-price="99.99">
                                    Add
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Accessories Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-6 md:max-w-[702px]">
                <!-- NFC Key Fob -->
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <div class="flex justify-start mb-4">
                        <img decoding="async" alt="NFC Smart Button" class="h-28 w-auto"
                            src="<?= MOBILO_THEME_URL ?>/assets/images/key-fob.png">
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 m-0">NFC Smart Button</h3>
                        <p class="text-base text-gray-600 leading-relaxed mt-5">
                            Stick to the back of your phone and always be ready to connect.
                        </p>
                    </div>
                    <!-- TODO: fix button width -->
                    <div class="flex justify-between items-center mt-8">
                        <p class="text-base font-bold text-gray-800 m-0">
                            <span class="font-light">1x</span>$0.00
                        </p>
                        <button class="mobilo-add-upsell-all btn-primary" data-product-id="11211" data-quantity="1">
                            Add for all members </button>
                    </div>
                </div>
                <!-- NFC Smart Button -->
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <div class="flex justify-start mb-4">
                        <img decoding="async" alt="NFC Smart Button" class="h-28 w-auto"
                            src="<?= MOBILO_THEME_URL ?>/assets/images/smart-button.png">
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 m-0">NFC Smart Button</h3>
                        <p class="text-base text-gray-600 leading-relaxed mt-5">
                            Stick to the back of your phone and always be ready to connect.
                        </p>
                    </div>
                    <!-- TODO: fix button width -->
                    <div class="flex justify-between items-center mt-8">
                        <p class="text-base font-bold text-gray-800 m-0">
                            <span class="font-light">1x</span>$0.00
                        </p>
                        <button class="mobilo-add-upsell-all btn-primary" data-product-id="11211" data-quantity="1">
                            Add for all members </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Cart Summary -->
        <div class="cart-card">
            <div class="card space-y-5">
                <!-- Products Section -->
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center">
                        <img src="<?= MOBILO_THEME_URL ?>/assets/images/card.svg" alt="Card" class="w-6 h-6">
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 m-0">Products</h3>
                        <p class="text-sm text-gray-600 m-0">Cards & Accessories</p>
                    </div>
                </div>

                <!-- Cart Items -->
                <div class="space-y-5">
                    <!-- Custom Designed Card Item -->
                    <div class="flex justify-between items-center m-0 mb-1" data-cart-item="custom-card">
                        <div class="flex items-center gap-3">
                            <div>
                                <h4 class="text-base font-bold text-gray-900 m-0">Custom Designed Card</h4>
                                <p class="text-sm text-gray-600 m-0">[Material] · [Color]</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-5">
                            <div class="text-right">
                                <span class="text-base font-bold text-gray-900 m-0">$99.95</span>
                                <div class="input-quantity" data-quantity-control data-item-id="custom-card">
                                    <button data-action="decrease">-</button>
                                    <span data-quantity>1</span>
                                    <button data-action="increase">+</button>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <button class="text-gray-600 hover:text-gray-900" data-action="remove"
                                    data-item-id="custom-card">
                                    <img src="<?= MOBILO_THEME_URL ?>/assets/images/delete.svg" alt="Delete"
                                        class="w-4 h-4">
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- NFC Key Fob Item -->
                    <div class="flex justify-between items-center m-0 mb-1" data-cart-item="key-fob">
                        <div class="flex items-center gap-3">
                            <div>
                                <h4 class="text-base font-bold text-gray-900 m-0">NFC Key Fob</h4>
                                <p class="text-sm text-gray-600 m-0">1 units</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-5">
                            <div class="text-right">
                                <span class="text-base font-bold text-gray-900">$2.50</span>
                            </div>
                            <button class="text-gray-600 hover:text-gray-900" data-action="remove"
                                data-item-id="key-fob">
                                <img src="<?= MOBILO_THEME_URL ?>/assets/images/delete.svg" alt="Delete"
                                    class="w-4 h-4">
                            </button>
                        </div>
                    </div>

                    <!-- NFC Smart Button Item -->
                    <div class="flex justify-between items-center m-0 mb-1" data-cart-item="smart-button">
                        <div class="flex items-center gap-3">
                            <div>
                                <h4 class="text-base font-bold text-gray-900 m-0">NFC Smart Button</h4>
                                <p class="text-sm text-gray-600 m-0">1 units</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-5">
                            <div class="text-right">
                                <span class="text-base font-bold text-gray-900">$2.50</span>
                            </div>
                            <button class="text-gray-600 hover:text-gray-900" data-action="remove"
                                data-item-id="smart-button">
                                <img src="<?= MOBILO_THEME_URL ?>/assets/images/delete.svg" alt="Delete"
                                    class="w-4 h-4">
                            </button>
                        </div>
                    </div>
                </div>

                <hr class="border-gray-200">

                <!-- PRO Plan -->
                <div class="flex justify-between items-center m-0">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center">
                            <svg width="50" height="50" viewBox="0 0 50 50" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <rect width="50" height="50" rx="25" fill="#F8F8F8" />
                                <path
                                    d="M22.8443 35.6797H13.8281C13.5478 35.6797 13.3203 35.4527 13.3203 35.1719C13.3203 34.8911 13.5478 34.6641 13.8281 34.6641H22.8443C23.1246 34.6641 23.3521 34.8911 23.3521 35.1719C23.3521 35.4527 23.1246 35.6797 22.8443 35.6797Z"
                                    fill="#262626" />
                                <path
                                    d="M36.1744 35.6797H27.1582C26.8774 35.6797 26.6504 35.4527 26.6504 35.1719C26.6504 34.8911 26.8774 34.6641 27.1582 34.6641H36.1744C36.4552 34.6641 36.6822 34.8911 36.6822 35.1719C36.6822 35.4527 36.4552 35.6797 36.1744 35.6797Z"
                                    fill="#262626" />
                                <path
                                    d="M31.6621 32.1479C30.0726 32.1479 28.7305 30.8047 28.7305 29.2158C28.7305 27.6268 30.0731 26.2842 31.6621 26.2842C33.251 26.2842 34.5942 27.6268 34.5942 29.2158C34.5942 30.8047 33.2515 32.1479 31.6621 32.1479ZM31.6621 27.2993C30.6231 27.2993 29.7461 28.1768 29.7461 29.2153C29.7461 30.2538 30.6236 31.1318 31.6621 31.1318C32.7005 31.1318 33.5786 30.2538 33.5786 29.2153C33.5786 28.1768 32.7011 27.2993 31.6621 27.2993Z"
                                    fill="#262626" />
                                <path
                                    d="M27.1582 35.0601C26.8774 35.0601 26.6504 34.8326 26.6504 34.5523C26.6509 32.5134 28.6618 31.2307 30.5301 30.9016C30.8063 30.8503 31.0699 31.0372 31.1181 31.3134C31.1674 31.5897 30.9825 31.8532 30.7063 31.9015C29.2423 32.16 27.6665 33.0994 27.666 34.5528C27.666 34.8331 27.4385 35.0601 27.1582 35.0601Z"
                                    fill="#262626" />
                                <path
                                    d="M36.1748 35.0609C35.8945 35.0609 35.667 34.8339 35.667 34.5531C35.6664 33.1002 34.0907 32.1603 32.6267 31.9018C32.3504 31.853 32.1661 31.59 32.2148 31.3137C32.2631 31.0375 32.5261 30.8511 32.8029 30.9019C34.6711 31.231 36.6821 32.5142 36.6826 34.5526C36.6826 34.8334 36.4556 35.0609 36.1748 35.0609Z"
                                    fill="#262626" />
                                <path
                                    d="M13.8281 35.0609C13.8281 35.0609 13.8276 35.0609 13.8271 35.0609C13.5463 35.0603 13.3198 34.8323 13.3203 34.552C13.3249 32.5106 15.3348 31.2289 17.2005 30.9019C17.4788 30.8541 17.7403 31.0385 17.7886 31.3147C17.8373 31.591 17.6525 31.8545 17.3762 31.9028C15.9142 32.1592 14.3395 33.0982 14.3359 34.5546C14.3354 34.8344 14.1079 35.0609 13.8281 35.0609Z"
                                    fill="#262626" />
                                <path
                                    d="M18.3325 32.1479C16.743 32.1479 15.4004 30.8047 15.4004 29.2158C15.4004 27.6268 16.743 26.2842 18.3325 26.2842C19.922 26.2842 21.2646 27.6268 21.2646 29.2158C21.2646 30.8047 19.922 32.1479 18.3325 32.1479ZM18.3325 27.2993C17.2935 27.2993 16.416 28.1768 16.416 29.2153C16.416 30.2538 17.2935 31.1318 18.3325 31.1318C19.3715 31.1318 20.249 30.2538 20.249 29.2153C20.249 28.1768 19.3715 27.2993 18.3325 27.2993Z"
                                    fill="#262626" />
                                <path
                                    d="M36.1719 35.6797C35.8911 35.6797 35.6641 35.4527 35.6641 35.1719V34.5529C35.6641 34.272 35.8911 34.045 36.1719 34.045C36.4527 34.045 36.6797 34.272 36.6797 34.5529V35.1719C36.6797 35.4527 36.4527 35.6797 36.1719 35.6797Z"
                                    fill="#262626" />
                                <path
                                    d="M27.1582 35.6796C26.8774 35.6796 26.6504 35.4526 26.6504 35.1718V34.5527C26.6504 34.2719 26.8774 34.0449 27.1582 34.0449C27.439 34.0449 27.666 34.2719 27.666 34.5527V35.1718C27.666 35.4526 27.439 35.6796 27.1582 35.6796Z"
                                    fill="#262626" />
                                <path
                                    d="M22.8452 35.0604C22.5654 35.0604 22.3379 34.834 22.3374 34.5536C22.3343 33.0977 20.7591 32.1583 19.2971 31.9018C19.0208 31.8531 18.836 31.5901 18.8848 31.3138C18.933 31.0376 19.1935 30.8537 19.4728 30.9009C21.3385 31.2285 23.3484 32.5102 23.353 34.5511C23.3535 34.8314 23.127 35.0594 22.8462 35.0599C22.8457 35.0604 22.8452 35.0604 22.8452 35.0604Z"
                                    fill="#262626" />
                                <path
                                    d="M22.8418 35.6796C22.5615 35.6796 22.334 35.4526 22.334 35.1718V34.5527C22.334 34.2719 22.5615 34.0449 22.8418 34.0449C23.1221 34.0449 23.3496 34.2719 23.3496 34.5527V35.1718C23.3496 35.4526 23.1221 35.6796 22.8418 35.6796Z"
                                    fill="#262626" />
                                <path
                                    d="M13.8281 35.6797C13.5478 35.6797 13.3203 35.4527 13.3203 35.1719V34.5529C13.3203 34.272 13.5478 34.045 13.8281 34.045C14.1084 34.045 14.3359 34.272 14.3359 34.5529V35.1719C14.3359 35.4527 14.1084 35.6797 13.8281 35.6797Z"
                                    fill="#262626" />
                                <path
                                    d="M29.5143 21.7148H20.498C20.2177 21.7148 19.9902 21.4879 19.9902 21.207C19.9902 20.9262 20.2177 20.6992 20.498 20.6992H29.5143C29.7951 20.6992 30.0221 20.9262 30.0221 21.207C30.0221 21.4879 29.7951 21.7148 29.5143 21.7148Z"
                                    fill="#262626" />
                                <path
                                    d="M25.0024 18.1845C23.413 18.1845 22.0703 16.8414 22.0703 15.2524C22.0703 13.6635 23.413 12.3203 25.0024 12.3203C26.5919 12.3203 27.934 13.663 27.934 15.2519C27.934 16.8409 26.5919 18.1845 25.0024 18.1845ZM25.0024 13.3359C23.9634 13.3359 23.0859 14.2134 23.0859 15.2519C23.0859 16.2904 23.9634 17.1684 25.0024 17.1684C26.0414 17.1684 26.9184 16.2904 26.9184 15.2519C26.9184 14.2134 26.0414 13.3359 25.0024 13.3359Z"
                                    fill="#262626" />
                                <path
                                    d="M20.498 21.0969C20.498 21.0969 20.4975 21.0969 20.497 21.0969C20.2162 21.0964 19.9897 20.8684 19.9902 20.5881C19.9948 18.5467 22.0047 17.265 23.8704 16.9379C24.1487 16.8912 24.4097 17.0745 24.4585 17.3508C24.5072 17.627 24.3224 17.8906 24.0461 17.9388C22.5841 18.1953 21.0094 19.1342 21.0059 20.5906C21.0054 20.8704 20.7779 21.0969 20.498 21.0969Z"
                                    fill="#262626" />
                                <path
                                    d="M29.5057 21.0976C29.2269 21.0976 28.9999 20.8726 28.9979 20.5933C28.9877 19.1343 27.415 18.1944 25.9576 17.939C25.6814 17.8902 25.4965 17.6272 25.5448 17.3509C25.5935 17.0747 25.8561 16.8908 26.1328 16.9381C27.9919 17.2646 29.9993 18.5448 30.0135 20.5862C30.0155 20.867 29.7901 21.0955 29.5093 21.0976C29.5082 21.0976 29.5067 21.0976 29.5057 21.0976Z"
                                    fill="#262626" />
                                <path
                                    d="M29.502 21.7147C29.2211 21.7147 28.9941 21.4877 28.9941 21.2069V20.5879C28.9941 20.3071 29.2211 20.0801 29.502 20.0801C29.7828 20.0801 30.0098 20.3071 30.0098 20.5879V21.2069C30.0098 21.4877 29.7828 21.7147 29.502 21.7147Z"
                                    fill="#262626" />
                                <path
                                    d="M20.498 21.7147C20.2177 21.7147 19.9902 21.4877 19.9902 21.2069V20.5879C19.9902 20.3071 20.2177 20.0801 20.498 20.0801C20.7784 20.0801 21.0059 20.3071 21.0059 20.5879V21.2069C21.0059 21.4877 20.7784 21.7147 20.498 21.7147Z"
                                    fill="#262626" />
                                <path
                                    d="M21.1812 26.2983C21.0664 26.2983 20.9516 26.2597 20.8567 26.181C20.6414 26.0012 20.6119 25.6818 20.7912 25.466L22.9148 22.9143C23.0946 22.6984 23.4145 22.67 23.6298 22.8487C23.8452 23.0285 23.8746 23.3479 23.6954 23.5637L21.5717 26.1155C21.4716 26.2359 21.3269 26.2983 21.1812 26.2983Z"
                                    fill="#262626" />
                                <path
                                    d="M27.2094 29.7227H22.793C22.5127 29.7227 22.2852 29.4957 22.2852 29.2148C22.2852 28.934 22.5127 28.707 22.793 28.707H27.2094C27.4902 28.707 27.7172 28.934 27.7172 29.2148C27.7172 29.4957 27.4897 29.7227 27.2094 29.7227Z"
                                    fill="#262626" />
                                <path
                                    d="M28.8048 26.2984C28.6591 26.2984 28.5143 26.2359 28.4138 26.1156L26.2896 23.5638C26.1098 23.348 26.1393 23.0281 26.3551 22.8488C26.5709 22.6691 26.8924 22.6995 27.0701 22.9143L29.1943 25.4661C29.3741 25.6819 29.3446 26.0018 29.1288 26.1811C29.0343 26.2598 28.9185 26.2984 28.8048 26.2984Z"
                                    fill="#262626" />
                            </svg>

                        </div>
                        <div>
                            <h4 class="text-xl font-bold text-gray-900 m-0">PRO plan</h4>
                            <p class="text-sm text-gray-600 m-0">1 member</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-base font-bold text-gray-900 m-0">$0.00</span>
                    </div>
                </div>

                <hr class="border-gray-200">

                <!-- Order Total -->
                <div class="space-y-4">
                    <div class="flex justify-between items-center m-0">
                        <h3 class="text-base font-bold text-gray-900">Order Total</h3>
                        <span class="text-xl font-bold text-gray-900" data-cart-total>$104.95</span>
                    </div>

                    <div class="space-y-3">
                        <button class="w-full btn-primary text-lg py-4 px-8 h-14" data-action="checkout">
                            Checkout
                        </button>
                        <p class="text-sm text-gray-600 text-center">($524.00 one-time, $0.00 per year)</p>
                    </div>

                    <!-- Info Cards -->
                    <div class="space-y-2">
                        <div class="bg-gray-100 rounded px-4 py-3 flex items-center gap-2">
                            <img src="<?= MOBILO_THEME_URL ?>/assets/images/shipping.svg" alt="Shipping"
                                class="w-5 h-5">
                            <span class="text-sm text-gray-600">Shipping will be calculated at checkout</span>
                        </div>
                        <div class="bg-gray-100 rounded px-4 py-3 flex items-center gap-2">
                            <img src="<?= MOBILO_THEME_URL ?>/assets/images/pencil-ruler.svg" alt="Shipping"
                                class="w-4 h-4">
                            <span class="text-sm text-gray-600">Custom designs will be created after payment</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PRO Plan Card -->
            <div class="mt-6 bg-white rounded-2xl plan-card overflow-hidden">
                <div class="bg-gray-50 px-8 py-8 text-center">
                    <div class="space-y-2">
                        <h3 class="text-lg font-bold uppercase text-gray-900">PRO</h3>
                        <div class="flex items-end justify-center gap-1">
                            <span class="text-3xl font-bold text-[#181059]">$0</span>
                            <span class="text-sm text-gray-600">/ mo</span>
                        </div>
                        <p class="text-sm text-gray-600 m-0">Per member, billed annually.</p>
                    </div>
                    <div class="mt-4 flex items-center justify-center gap-2">
                        <span class="text-sm text-gray-900 m-0">All Mobilo features</span>
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

                            <span class="text-sm text-gray-900">1-5 members</span>
                        </div>
                    </div>

                    <div class="space-y-7">
                        <!-- Contact Sharing -->
                        <div class="space-y-4">
                            <h4 class="text-base font-bold text-gray-900">Contact Sharing</h4>
                            <div class="space-y-4">
                                <div class="flex items-center gap-2">
                                    <img src="<?= MOBILO_THEME_URL ?>/assets/images/check.svg" class="w-4 h-4>
                      <span class=" text-base text-gray-900">Unlimited taps/card shares</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <img src="<?= MOBILO_THEME_URL ?>/assets/images/check.svg" class="w-4 h-4>
                      <span class=" text-base text-gray-900">Personalized business card templates</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <img src="<?= MOBILO_THEME_URL ?>/assets/images/check.svg" class="w-4 h-4>
                      <span class=" text-base text-gray-900">Apple/Google QR Code Widget</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <img src="<?= MOBILO_THEME_URL ?>/assets/images/check.svg" class="w-4 h-4>
                      <span class=" text-base text-gray-900">Digital Wallet Card</span>
                                </div>
                            </div>
                        </div>

                        <!-- Lead Management -->
                        <div class="space-y-4">
                            <h4 class="text-base font-bold text-gray-900">Lead management</h4>
                            <div class="space-y-4">
                                <div class="flex items-center gap-2">
                                    <img src="<?= MOBILO_THEME_URL ?>/assets/images/check.svg" class="w-4 h-4>
                      <span class=" text-base text-gray-900">Unlimited lead capture</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <img src="<?= MOBILO_THEME_URL ?>/assets/images/check.svg" class="w-4 h-4>
                      <span class=" text-base text-gray-900">6,000+ CRM integrations with Zapier</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <img src="<?= MOBILO_THEME_URL ?>/assets/images/check.svg" class="w-4 h-4>
                      <span class=" text-base text-gray-900">Native integrations</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <img src="<?= MOBILO_THEME_URL ?>/assets/images/check.svg" class="w-4 h-4>
                      <span class=" text-base text-gray-900">Mobilo AI: Lead Scoring ✨</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <img src="<?= MOBILO_THEME_URL ?>/assets/images/check.svg" class="w-4 h-4>
                      <span class=" text-base text-gray-900">Business Card Scanner</span>
                                </div>
                            </div>
                        </div>

                        <!-- Team Management -->
                        <div class="space-y-4">
                            <h4 class="text-base font-bold text-gray-900">Team management and reporting</h4>
                            <div class="space-y-4">
                                <div class="flex items-center gap-2">
                                    <img src="<?= MOBILO_THEME_URL ?>/assets/images/check.svg" class="w-4 h-4>
                      <span class=" text-base text-gray-900">Brand governance: control member profiles</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <img src="<?= MOBILO_THEME_URL ?>/assets/images/check.svg" class="w-4 h-4>
                      <span class=" text-base text-gray-900">Create Departments, Groups, Office Locations</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <img src="<?= MOBILO_THEME_URL ?>/assets/images/check.svg" class="w-4 h-4>
                      <span class=" text-base text-gray-900">Single Sign-On for Microsoft & Google</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <img src="<?= MOBILO_THEME_URL ?>/assets/images/check.svg" class="w-4 h-4>
                      <span class=" text-base text-gray-900">Team insights & analytics</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <img src="<?= MOBILO_THEME_URL ?>/assets/images/check.svg" class="w-4 h-4>
                      <span class=" text-base text-gray-900">Malware link checker</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>