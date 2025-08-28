# Cart AJAX Actions

This directory contains individual AJAX action classes for cart operations, organized for better maintainability and selective loading.

## Structure

```
Cart/
├── AddToCartAction.php           # Add products to cart
├── UpdateCartQuantityAction.php  # Update cart item quantities
├── RemoveCartItemAction.php      # Remove items from cart
├── GetCartDataAction.php         # Get cart data
├── AddUpsellAllAction.php        # Add upsell products for all members
├── CartAjaxActions.php           # Loader class for managing actions
└── README.md                     # This file
```

## Usage

### Loading All Actions (Default)
```php
use Mobilo\WpTheme\Actions\Cart\CartAjaxActions;

$cart_actions = new CartAjaxActions();
$cart_actions->init(); // Loads all actions
```

### Loading Specific Actions Only
```php
use Mobilo\WpTheme\Actions\Cart\CartAjaxActions;

$cart_actions = new CartAjaxActions();
$cart_actions->load_specific_actions([
    'update_quantity',
    'remove_item',
    'get_cart_data'
]);
```

### Available Action Names
- `add_to_cart` - Add products to cart
- `update_quantity` - Update cart item quantities
- `remove_item` - Remove items from cart
- `get_cart_data` - Get cart data
- `add_upsell_all` - Add upsell products for all members

### Backward Compatibility
The original `CartAjaxActions` class in the parent directory maintains backward compatibility by delegating to the new structure.

## Benefits

1. **Selective Loading**: Only load the AJAX actions you need for each page
2. **Better Organization**: Each action is in its own file for easier maintenance
3. **Reduced Memory Usage**: Only load required actions instead of all actions
4. **Easier Testing**: Individual action classes can be tested in isolation
5. **Better Code Organization**: Follows single responsibility principle
