import { EDragAndDropActionTypes } from '../actions/dragAndDropActions';

export const defaults = {
    items: {
        // Header/Footer Elements
        'search-input': { id: 'search-input', content: 'Search Input' },
        'category-filter': { id: 'category-filter', content: 'Category Filter' },
        'add-selected-to-cart-button': { id: 'add-selected-to-cart-button', content: 'Add Selected To Cart Button' },
        'cart-subtotal': { id: 'cart-subtotal', content: 'Cart Subtotal' },
        'product-count': { id: 'product-count', content: 'Product Count' },
        'pagination': { id: 'pagination', content: 'Pagination' },
        // 'attribute-filter': { id: 'attribute-filter', content: 'Attribute Filter' },
        'search-button': { id: 'search-button', content: 'Search Button' },
        'clear-filters': { id: 'clear-filters', content: 'Clear Filters' },
        // Table Elements
        'product-image': { id: 'product-image', content: 'Product Image' },
        'product-name': { id: 'product-name', content: 'Product Name' },
        'sku': { id: 'sku', content: 'SKU' },
        'in-stock-amount': { id: 'in-stock-amount', content: 'In Stock Amount' },
        'price': { id: 'price', content: 'Price' },
        'quantity-input': { id: 'quantity-input', content: 'Quantity Input' },
        'add-to-cart-button': { id: 'add-to-cart-button', content: 'Add To Cart Button' },
        'combo-variation-dropdown': { id: 'combo-variation-dropdown', content: 'Combo Variation Dropdown' },
        // 'standard-variation-dropdowns': { id: 'standard-variation-dropdowns', content: 'WooCommerce Standard Variation Dropdowns' },
        // 'product-meta': { id: 'product-meta', content: 'Product Meta' },
        // 'global-attribute': { id: 'global-attribute', content: 'Global Attribute' },
        'short-description': { id: 'short-description', content: 'Short Description' },
        'add-to-cart-checkbox': { id: 'add-to-cart-checkbox', content: 'Add To Cart Checkbox' },
        // WooCommerce Widgets
        // 'cart-widget': { id: 'cart-widget', content: 'Cart Widget' },
        // 'filter-products-by-attribute': { id: 'filter-products-by-attribute', content: 'Filter Products by Attribute' },
        // 'filter-products-by-price': { id: 'filter-products-by-price', content: 'Filter Products by Price' },
    },
    formElements: {
        headerElements: {
            id: 'headerElements',
            title: 'HEADER/FOOTER ELEMENTS',
            desc: '',
            itemIds: ['search-input', 'category-filter', 'add-selected-to-cart-button', 'cart-subtotal', 'product-count', 'pagination', 'search-button', 'clear-filters']
        },
        tableElements: {
            id: 'tableElements',
            title: 'TABLE COLUMNS',
            desc: '',
            itemIds: ['product-image', 'product-name', 'sku', 'in-stock-amount', 'price', 'quantity-input', 'add-to-cart-button', 'combo-variation-dropdown', 'short-description', 'add-to-cart-checkbox']
        },
        // wooWidgets: {
        //     id: 'wooWidgets',
        //     title: 'WooCommerce Widgets',
        //     desc: 'Click and drag compatible WooCommerce widgets into the header/footer.',
        //     itemIds: ['cart-widget', 'filter-products-by-attribute', 'filter-products-by-price']
        // }
    },
    editorArea: {
        formHeader: {
            title: 'ORDER FORM HEADER',
            rows: []
        },
        formTable: {
            title: 'ORDER FORM TABLE',
            itemIds: []
        },
        formFooter: {
            title: 'ORDER FORM FOOTER',
            rows: []
        }
    }
};

export default function (state: any = defaults, action: any) {

    switch (action.type) {

        case EDragAndDropActionTypes.RESET_DND_DATA:

            return defaults;

        case EDragAndDropActionTypes.SET_DND_DATA:

            return action.payload;

        default:
            return state


    }

}