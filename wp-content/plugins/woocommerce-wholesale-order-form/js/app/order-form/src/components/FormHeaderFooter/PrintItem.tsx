import React from 'react';
// import { GoldOutlined } from '@ant-design/icons';

// Elements
import SearchInput from './Elements/SearchInput';
import CategoryFilter from './Elements/CategoryFilter';
import OrderFormPagination from './Elements/OrderFormPagination';
import SearchButton from './Elements/SearchButton';
import ClearFilters from './Elements/ClearFilters';
import AddSelectedProductsToCartButton from './Elements/AddSelectedProductsToCartButton';
import CartSubtotal from './Elements/CartSubtotal';
import ProductCount from './Elements/ProductCount'

const getPropValue = (props: any) => {
    const { properties, prop } = props;

    if (typeof properties === 'undefined')
        return null;
    else if (typeof properties !== 'undefined' && typeof properties[prop] !== 'undefined')
        return properties[prop];
    else
        return null;

}

const PrintItem = (props: any) => {

    const { item, styles, properties, products, orderFormId, fetchProducts } = props;

    const displayItem = (id: string) => {

        switch (item.id) {

            // Header/Footer Elements
            case 'search-input':
                return (
                    <SearchInput
                        orderFormId={orderFormId}
                        placeholder={getPropValue({ properties, prop: 'placeholder' }) || 'Search Products'}
                        styles={styles}
                        fetchProducts={fetchProducts}
                        submitOnEnter={getPropValue({ properties, prop: 'submitOnEnter' }) || false}
                    />
                );
            case 'category-filter':
                return (
                    <CategoryFilter
                        orderFormId={orderFormId}
                        placeholder={getPropValue({ properties, prop: 'placeholder' }) || 'Select Category'}
                        styles={styles}
                        fetchProducts={fetchProducts}
                        submitOnChange={getPropValue({ properties, prop: 'submitOnChange' }) || false}
                    />
                );
            case 'add-selected-to-cart-button':
                return (
                    <AddSelectedProductsToCartButton
                        orderFormId={orderFormId}
                        styles={styles}
                        buttonText={getPropValue({ properties, prop: 'buttonText' }) || 'Add Selected Products To Cart'}
                    />

                );
            case 'cart-subtotal':
                return (
                    <CartSubtotal
                        orderFormId={orderFormId}
                        styles={styles}
                    />
                );
            case 'product-count':
                return (
                    <ProductCount
                        orderFormId={orderFormId}
                        styles={styles}
                    />
                );
            case 'pagination':
                return (
                    <OrderFormPagination
                        productsPerPage={getPropValue({ properties, prop: 'productsPerPage' }) || 12}
                        orderFormId={orderFormId}
                        styles={styles}
                        fetchProducts={fetchProducts}
                    />
                );
            // case 'attribute-filter':
            //     return (
            //         <div
            //             style={{ ...styles }}
            //         >
            //             <GoldOutlined />
            //             {item.content}
            //         </div>
            //     );
            case 'search-button':
                return (
                    <SearchButton
                        orderFormId={orderFormId}
                        styles={styles}
                        buttonText={getPropValue({ properties, prop: 'buttonText' }) || 'Search'}
                        fetchProducts={fetchProducts}
                    />
                );
            case 'clear-filters':
                return (
                    <ClearFilters
                        orderFormId={orderFormId}
                        styles={styles}
                        buttonText={getPropValue({ properties, prop: 'buttonText' }) || 'Clear Filters'}
                        fetchProducts={fetchProducts}
                    />
                );
            default:
                return (<></>);
        }

    }

    return (
        <>
            {typeof item !== 'undefined' ? displayItem(item.id) : ''}
        </>
    )
}

// Array.from(Array(itemsPerPage).keys()).map

export default PrintItem;