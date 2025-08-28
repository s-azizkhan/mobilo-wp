# Mobilo Dynamic Cart Implementation

## Overview

This implementation provides a fully dynamic cart system for the Mobilo WordPress theme with the following features:

- **Dynamic Product Display**: Products are loaded from WooCommerce with real-time data
- **Interactive Cart Operations**: Add, remove, update quantities via AJAX
- **Material and Color Selection**: For variable products with variations
- **Real-time Cart Updates**: Cart sidebar updates automatically
- **Upsell Product Integration**: Add accessories for all team members
- **Responsive Design**: Works on all device sizes

## Files Created/Modified

### New Files:
1. `inc/Actions/CartAjaxActions.php` - AJAX handlers for cart operations
2. `assets/js/mobilo-cart.js` - Frontend JavaScript for cart interactions
3. `views/mobilo-cart-dynamic.php` - Dynamic cart template
4. `CART_IMPLEMENTATION.md` - This documentation

### Modified Files:
1. `inc/Shortcode/MobiloCartShortcode.php` - Updated to provide dynamic data
2. `inc/PageTemplates/CartPageTemplate.php` - Added AJAX actions initialization
3. `inc/helpers.php` - Added cart helper functions

## Features

### 1. Product Display
- Dynamic loading of products by SKU
- Support for variable products with material variations
- Card color selection for variable products
- Product thumbnails and descriptions
- Real-time pricing with VAT calculations

### 2. Cart Operations
- **Add to Cart**: Add products with variations and custom data
- **Update Quantity**: Increase/decrease quantities with +/- buttons
- **Remove Items**: Remove items from cart
- **Add for All Members**: Add upsell products for all team members

### 3. Cart Sidebar
- Real-time cart item display
- Quantity controls for each item
- Remove buttons for each item
- Dynamic totals calculation
- Plan information display
- Checkout button

### 4. AJAX Endpoints

The following AJAX endpoints are available:

- `mobilo_add_to_cart` - Add product to cart
- `mobilo_update_cart_quantity` - Update cart item quantity
- `mobilo_remove_cart_item` - Remove item from cart
- `mobilo_get_cart_data` - Get current cart data
- `mobilo_add_upsell_all` - Add upsell products for all members

## Usage

### 1. Using the Shortcode

Simply use the shortcode on any page:

```php
[mobilo_cart]
```

### 2. JavaScript Integration

The cart automatically initializes when the shortcode is present. All interactions are handled via JavaScript with the following classes:

- `.mobilo-add-to-cart` - Add to cart buttons
- `.mobilo-quantity-btn` - Quantity update buttons
- `.mobilo-remove-item` - Remove item buttons
- `.mobilo-material-btn` - Material selection buttons
- `.mobilo-card-color` - Card color selection
- `.mobilo-add-upsell-all` - Add upsell for all members
- `.mobilo-checkout-btn` - Checkout button

### 3. Data Attributes

Add to cart buttons support the following data attributes:

```html
<button class="mobilo-add-to-cart" 
        data-product-id="123"
        data-quantity="1"
        data-variation-id="456"
        data-variation='{"attribute_pa_material": "metal"}'
        data-card-color="Silver">
    Add
</button>
```

## Technical Details

### 1. Cart Data Structure

The cart data is structured as follows:

```php
[
    'items' => [
        'products' => [...], // Main products
        'accessories' => [...], // Upsell products
    ],
    'total' => '69.00',
    'one_time' => '69.00',
    'per_year' => '0.00',
    'cart_notes' => [...],
    'plan' => [...] // If subscription plan exists
]
```

### 2. Product Data Structure

Each product contains:

```php
[
    'id' => 123,
    'name' => 'Product Name',
    'price' => '69.00',
    'base_price' => '139.00',
    'sku' => 'MCC',
    'features' => [...],
    'variations' => [...], // For variable products
    'in_cart' => true/false
]
```

### 3. Error Handling

All AJAX operations include proper error handling:

- Input validation
- WooCommerce integration checks
- Exception handling with logging
- User-friendly error messages

### 4. Security

- Nonce verification for AJAX requests
- Input sanitization
- Proper escaping in templates
- WooCommerce security integration

## Customization

### 1. Styling

The cart uses Tailwind CSS classes. You can customize the appearance by:

- Modifying the CSS classes in the template
- Adding custom CSS to your theme
- Overriding the Tailwind configuration

### 2. Adding New Features

To add new cart features:

1. Add new AJAX action in `CartAjaxActions.php`
2. Add corresponding JavaScript handler in `mobilo-cart.js`
3. Update the template if needed

### 3. Product Configuration

Products are configured by SKU in the shortcode:

```php
$main_products_sku = ['MCC', 'MBC', 'MC_DIGITAL'];
$upsell_products_sku = ['NFC-SB', 'NFC-KF'];
```

## Troubleshooting

### Common Issues:

1. **Products not loading**: Check if SKUs exist in WooCommerce
2. **AJAX errors**: Verify WooCommerce is active and cart is initialized
3. **Styling issues**: Ensure Tailwind CSS is loading properly
4. **Cart not updating**: Check browser console for JavaScript errors

### Debug Mode:

Enable WordPress debug mode to see detailed error messages:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Performance Considerations

- Cart data is cached in WooCommerce session
- AJAX requests are optimized for minimal data transfer
- Images are lazy-loaded where appropriate
- JavaScript is loaded only when shortcode is present

## Browser Support

- Modern browsers (Chrome, Firefox, Safari, Edge)
- Mobile responsive design
- Progressive enhancement for older browsers
