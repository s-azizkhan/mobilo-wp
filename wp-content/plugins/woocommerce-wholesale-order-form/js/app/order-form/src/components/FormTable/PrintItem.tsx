import React from 'react';

import AddToCartButton from './Elements/AddToCartButton';
import AddToCartCheckbox from './Elements/AddToCartCheckbox';
import ComboVariations from './Elements/ComboVariations';
import QuantityInput from './Elements/QuantityInput';
import ShortDescription from './Elements/ShortDescription';
import Price from './Elements/Price';
import ProductImage from './Elements/ProductImage';
import InStockAmount from './Elements/InStockAmount';
import ProductSku from './Elements/ProductSku';
import ProductName from './Elements/ProductName';

const PrintItem = (props: any) => {

    const { itemId, product } = props;

    const itemProps = { ...props }

    const displayItem = (itemId: string) => {

        switch (itemId) {

            // Table Elements
            case 'product-image':
                return (
                    <ProductImage
                        itemProps={itemProps}
                    />
                );
            case 'product-name':
                return (
                    <ProductName
                        itemProps={itemProps}
                    />);
            case 'sku':
                return (
                    <ProductSku
                        itemProps={itemProps}
                    />
                );
            case 'in-stock-amount':
                return (
                    <InStockAmount
                        itemProps={itemProps}
                    />
                );
            case 'price':
                return (
                    <Price
                        itemProps={itemProps}
                    />
                );
            case 'quantity-input':
                return (
                    <QuantityInput
                        itemProps={itemProps}
                    />
                );
            case 'add-to-cart-button':
                return (
                    <AddToCartButton
                        itemProps={itemProps}
                    />
                );
            case 'combo-variation-dropdown':
                return (
                    <ComboVariations
                        itemProps={itemProps}
                    />
                );

            // case 'standard-variation-dropdowns':
            //     return (<><Select
            //         showSearch
            //         placeholder='Select Variation'
            //         style={{ width: 250, marginTop: 10 }}
            //         filterOption={false}
            //         notFoundContent='No results found'
            //         allowClear={true} >
            //     </Select>
            //     </>);

            // case 'product-meta':
            //     return (<>Product Meta</>);
            // case 'global-attribute':
            //     return (<>Global Attribute</>);
            case 'short-description':
                return (
                    <ShortDescription
                        itemProps={itemProps}
                    />
                );
            case 'add-to-cart-checkbox':
                return (
                    <AddToCartCheckbox
                        itemProps={itemProps}
                    />
                );
            default:
                return (<></>);
        }

    }

    return (
        <>
            {displayItem(itemId)}
        </>
    )
}

// Array.from(Array(itemsPerPage).keys()).map

export default PrintItem;