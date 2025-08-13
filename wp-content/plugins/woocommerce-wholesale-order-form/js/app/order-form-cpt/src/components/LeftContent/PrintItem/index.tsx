import React, { useState } from 'react';
// import { GoldOutlined, ReconciliationOutlined } from '@ant-design/icons';
import { Button, Row, Col, Avatar, InputNumber, Checkbox } from 'antd';

// Elements
import OrderFormPagination from './Elements/OrderFormPagination';
import CategoryFilter from './Elements/CategoryFilter';
import ShortDescription from './Elements/ShortDescription';
import AddToCartCheckbox from './Elements/AddToCartCheckbox';
import ComboVariations from './Elements/ComboVariations';
import SearchInput from './Elements/SearchInput';

declare var Options: any;

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

    const { item, styles, properties, products, pagination, orderForm } = props;

    const [selectedAll, setSelectedAll] = useState(false);

    const displayItem = (id: string) => {

        switch (item.id) {

            // HEADER AND FOOTER ELEMENTS
            case 'search-input':

                return (
                    <SearchInput
                        placeholder={getPropValue({ properties, prop: 'placeholder' }) || 'Search Products'}
                        style={{ ...styles }}
                    />
                );
            case 'category-filter':
                return (
                    <CategoryFilter
                        placeholder={getPropValue({ properties, prop: 'placeholder' }) || 'Select Category'}
                        style={{ ...styles }}
                        products={products}
                    />
                );
            case 'add-selected-to-cart-button':
                return (
                    <Button
                        type="primary"
                        style={{ ...styles }}
                    >{getPropValue({ properties, prop: 'buttonText' }) || 'Add Selected Products To Cart'}</Button>
                );
            case 'cart-subtotal':
                return (<>
                    <Row>
                        <Col>
                            <div
                                style={{ ...styles }}
                                dangerouslySetInnerHTML={{ __html: orderForm.cartSubtotal || 'Cart Empty' }}>
                            </div>
                        </Col>
                    </Row>
                </>);
            case 'product-count':
                const totalProducts = pagination.total_products || 0;
                return (<>
                    <Row>
                        <Col>
                            <div
                                style={{ ...styles }}
                                {...properties}
                                dangerouslySetInnerHTML={{ __html: `${totalProducts} Product(s)` }}>
                            </div>
                        </Col>
                    </Row>
                </>);
            case 'pagination':

                let productPerPage = getPropValue({ properties, prop: 'productsPerPage' });
                if (typeof productPerPage !== 'number')
                    productPerPage = 0

                return (
                    <OrderFormPagination
                        styles={{ ...styles }}
                        products={products}
                        productsPerPage={productPerPage}
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
                    <Button
                        type="primary"
                        style={{ ...styles }}
                    >{getPropValue({ properties, prop: 'buttonText' }) || 'Search'}</Button>
                );
            case 'clear-filters':
                return (
                    <Button
                        type="primary"
                        style={{ ...styles }}
                    >{getPropValue({ properties, prop: 'buttonText' }) || 'Clear Filters'}</Button>
                );

            // TABLE ELEMENTS
            case 'product-image':
                const placeholder = Options.site_url + '/wp-content/uploads/woocommerce-placeholder-300x300.png';
                return (<>
                    <div className='row heading'>
                        {getPropValue({ properties, prop: 'columnHeading' }) || item.content}
                    </div>
                    {products.products.map((d: any, i: any) => {
                        return (
                            <div key={i} className='row'>
                                {
                                    d.images.length > 0 ?
                                        <Avatar src={d.images[0]['src']} shape="square" style={{ width: '48px', height: '48px' }} />
                                        :
                                        <Avatar src={placeholder} shape="square" style={{ width: '48px', height: '48px' }} />
                                }

                            </div>
                        );
                    })}
                </>);
            case 'product-name':
                return (<>
                    <div className='row heading'>
                        {getPropValue({ properties, prop: 'columnHeading' }) || item.content}
                    </div>
                    {products.products.map((d: any, i: any) => {
                        return (
                            <div key={i} className='row'>
                                {d.name}
                            </div>
                        );
                    })}
                </>);
            case 'sku':
                return (<>
                    <div className='row heading'>
                        {getPropValue({ properties, prop: 'columnHeading' }) || item.content}
                    </div>
                    {products.products.map((d: any, i: any) => {
                        return (
                            <div key={i} className='row'>
                                {d.sku}
                            </div>
                        );
                    })}
                </>);
            case 'in-stock-amount':
                return (<>
                    <div className='row heading'>
                        {getPropValue({ properties, prop: 'columnHeading' }) || item.content}
                    </div>
                    {products.products.map((d: any, i: any) => {
                        return (
                            <div key={i} className='row'>
                                {d.stock_status === "outofstock" ? "Out of stock" : d.stock_quantity}
                            </div>
                        );
                    })}
                </>);
            case 'price':
                return (<>
                    <div className='row heading'>
                        {getPropValue({ properties, prop: 'columnHeading' }) || item.content}
                    </div>
                    {products.products.map((d: any, i: any) => {
                        return (
                            <div
                                key={i}
                                className='row'
                                dangerouslySetInnerHTML={{ __html: d.price_html }}
                            />
                        );
                    })}
                </>);
            case 'quantity-input':
                return (<>
                    <div className='row heading'>
                        {getPropValue({ properties, prop: 'columnHeading' }) || item.content}
                    </div>
                    {products.products.map((d: any, i: any) => {
                        return (
                            <div key={i} className='row'>
                                <InputNumber
                                    min={1}
                                    defaultValue={1}
                                />
                            </div>
                        );
                    })}
                </>);
            case 'add-to-cart-button':
                return (<>
                    <div className='row heading'>
                        {getPropValue({ properties, prop: 'columnHeading' }) || item.content}
                    </div>
                    {products.products.map((d: any, i: any) => {
                        return (
                            <div key={i} className='row'>
                                <Button type='primary'>{getPropValue({ properties, prop: 'buttonText' }) || 'Add To Cart'}</Button>
                            </div>
                        );
                    })}
                </>);
            case 'combo-variation-dropdown':
                return (<>
                    <div className='row heading'>
                        {getPropValue({ properties, prop: 'columnHeading' }) || item.content}
                    </div>
                    {products.products.map((d: any, i: any) => {
                        return (
                            <div key={i} className='row'>
                                <ComboVariations
                                    product={d}
                                />
                            </div>
                        );
                    })}
                </>);

            // case 'standard-variation-dropdowns':
            //     return (<>
            //         <div className='row heading'>
            //             {getPropValue({ properties, prop: 'columnHeading' }) || item.content}
            //         </div>
            //         {products.products.map((d: any, i: any) => {
            //             return (
            //                 <div key={i} className='row'>
            //                     <Select
            //                         showSearch
            //                         placeholder='Select Variation'
            //                         style={{ width: 250, marginTop: 10 }}
            //                         filterOption={false}
            //                         notFoundContent='No results found'
            //                         allowClear={true} >
            //                     </Select>
            //                 </div>
            //             );
            //         })}
            // </>);

            // case 'product-meta':
            //     return (<>
            //         <div className='row heading'>
            //             {getPropValue({ properties, prop: 'columnHeading' }) || item.content}
            //         </div>
            //         {products.products.map((d: any, i: any) => {
            //             return (
            //                 <div key={i} className='row'>
            //                     Product Meta {i}
            //                 </div>
            //             );
            //         })}
            //     </>);
            // case 'global-attribute':
            //     return (<>
            //         <div className='row heading'>
            //             {getPropValue({ properties, prop: 'columnHeading' }) || item.content}
            //         </div>
            //         {products.products.map((d: any, i: any) => {
            //             return (
            //                 <div key={i} className='row'>
            //                     Global Attribute {i}
            //                 </div>
            //             );
            //         })}
            //     </>);
            case 'short-description':
                return (<>
                    <div className='row heading'>
                        {getPropValue({ properties, prop: 'columnHeading' }) || item.content}
                    </div>
                    {products.products.map((d: any, i: any) => {
                        return (
                            <ShortDescription
                                product={d}
                                key={i}
                            />
                        );
                    })}
                </>);

            case 'add-to-cart-checkbox':
                return (<>
                    <div className='row heading'>
                        <Checkbox
                            onChange={(e) => setSelectedAll(e.target.checked)} />
                    </div>
                    {products.products.map((d: any, i: any) => {
                        return (
                            <AddToCartCheckbox
                                product={d}
                                key={i}
                                selectedAll={selectedAll}
                            />
                        );
                    })}
                </>);

            // WOOCOMMERCE WIDGETS
            // case 'cart-widget':
            //     return (<><ReconciliationOutlined /> {item.content}</>);
            // case 'filter-products-by-attribute':
            //     return (<><GoldOutlined /> {item.content}</>);
            // case 'filter-products-by-price':
            //     return (<><GoldOutlined /> {item.content}</>);
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