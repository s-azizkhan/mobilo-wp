import React, { useState, useEffect } from 'react';
import { Table, Checkbox } from 'antd';
import PrintItem from './PrintItem';

import { bindActionCreators, Dispatch } from "redux";
import { connect } from 'react-redux';

// Actions
import { orderFormDataActions } from '../../store/actions/';
import { orderFormActions } from '../../store/actions';

const { fetchProducts } = orderFormActions;
const { updateSelectedProducts } = orderFormDataActions;

export const getPropValue = (props: any) => {

    const { formStyles, item, prop } = props;

    if (
        typeof formStyles !== 'undefined' &&
        typeof formStyles[item] !== 'undefined' &&
        typeof formStyles[item]['props'] !== 'undefined' &&
        typeof formStyles[item]['props'][prop] !== 'undefined'
    )
        return formStyles[item]['props'][prop];
    else
        return null;

}

declare var Options: any;

const FormTable = (props: any) => {

    const { orderFormId, orderFormData, actions } = props;
    const { updateSelectedProducts, fetchProducts } = actions;

    const [selectedAll, setSelectedAll] = useState(false);
    const [selected, setSelected] = useState(false);
    const [currentPage, setCurrentPage] = useState(1);
    const [somethingChanged, setSomethingChanged] = useState(false);

    const formStyles = orderFormData['formStyles'][orderFormId];
    const formTable = orderFormData['formTable'][orderFormId];

    let fetching = false;
    if (
        typeof orderFormData['formProducts'][orderFormId] !== 'undefined' &&
        typeof orderFormData['formProducts'][orderFormId]['fetching'] !== 'undefined'
    )
        fetching = orderFormData['formProducts'][orderFormId]['fetching'];

    let formProducts: any = [];
    if (
        typeof orderFormData['formProducts'][orderFormId] !== 'undefined' &&
        typeof orderFormData['formProducts'][orderFormId]['products'] !== 'undefined'
    )
        formProducts = orderFormData['formProducts'][orderFormId]['products'];

    // If select all was checked
    useEffect(() => {

        try {
            if (formTable !== undefined && somethingChanged) {
                setSomethingChanged(false)
                updateSelectedProducts({
                    selected: selectedAll,
                    orderFormData,
                    orderFormId
                });
            }
        } catch (error) {
            console.log(error)
        }

    }, [selectedAll]);

    // When page is changed check if the page has selected all state
    useEffect(() => {

        try {

            if (
                typeof orderFormData.formPagination[orderFormId] !== 'undefined' &&
                typeof orderFormData.formPagination[orderFormId].active_page !== 'undefined') {
                setCurrentPage(orderFormData.formPagination[orderFormId].active_page);

                if (
                    typeof orderFormData.formPagination[orderFormId].selectedAll !== 'undefined' &&
                    typeof orderFormData.formPagination[orderFormId].selectedAll[currentPage] !== 'undefined'
                ) {
                    setSelected(
                        orderFormData.formPagination[orderFormId].selectedAll[currentPage]
                    );
                }

            }

        } catch (error) {
            console.log(error)
        }

    }, [orderFormData.formPagination[orderFormId]]);

    // When select all was checked save state
    useEffect(() => {

        try {

            if (
                typeof orderFormData.formPagination[orderFormId] !== 'undefined' &&
                typeof orderFormData.formPagination[orderFormId].selectedAll !== 'undefined' &&
                typeof orderFormData.formPagination[orderFormId].selectedAll[currentPage] !== 'undefined'
            ) {
                setSelected(
                    orderFormData.formPagination[orderFormId].selectedAll[currentPage]
                );
                setSelectedAll(
                    orderFormData.formPagination[orderFormId].selectedAll[currentPage]
                );
            } else {
                setSelected(false);
                setSelectedAll(false);
            }

        } catch (error) {
            console.log(error)
        }

    }, [currentPage]);

    if (formTable === undefined)
        return (<></>);

    const columns = Object.keys(formTable['itemIds']).map((key: any) => {

        const item = formTable['itemIds'][key];

        switch (item) {
            case 'product-image':
                return {
                    title: getPropValue({ formStyles, item, prop: 'columnHeading' }) || 'Product Image',
                    dataIndex: 'product-image',
                    key: 'product-image'
                };
            case 'product-name':
                return {
                    title: getPropValue({ formStyles, item, prop: 'columnHeading' }) || 'Product Name',
                    dataIndex: 'product-name',
                    key: 'product-name',
                    sorter: getPropValue({ formStyles, item, prop: 'sort' }) || false
                };
            case 'sku':
                return {
                    title: getPropValue({ formStyles, item, prop: 'columnHeading' }) || 'SKU',
                    dataIndex: 'sku',
                    key: 'sku',
                    // sorter: getPropValue({ formStyles, item, prop: 'sort' }) || false
                };
            case 'in-stock-amount':
                return {
                    title: getPropValue({ formStyles, item, prop: 'columnHeading' }) || 'In Stock Amount',
                    dataIndex: 'in-stock-amount',
                    key: 'in-stock-amount',
                    // sorter: getPropValue({ formStyles, item, prop: 'sort' }) || false
                };
            case 'price':
                return {
                    title: getPropValue({ formStyles, item, prop: 'columnHeading' }) || 'Price',
                    dataIndex: 'price',
                    key: 'price',
                    // sorter: getPropValue({ formStyles, item, prop: 'sort' }) || false
                };
            case 'quantity-input':
                return {
                    title: getPropValue({ formStyles, item, prop: 'columnHeading' }) || 'Quantity',
                    dataIndex: 'quantity-input',
                    key: 'quantity-input'
                };
            case 'add-to-cart-button':
                return {
                    title: getPropValue({ formStyles, item, prop: 'columnHeading' }) || 'Add To Cart',
                    dataIndex: 'add-to-cart-button',
                    key: 'add-to-cart-button'
                };
            case 'combo-variation-dropdown':
                return {
                    title: getPropValue({ formStyles, item, prop: 'columnHeading' }) || 'Combo Variation Dropdown',
                    dataIndex: 'combo-variation-dropdown',
                    key: 'combo-variation-dropdown'
                };
            // case 'standard-variation-dropdowns':
            //     return {
            //         title: getPropValue({ formStyles, item, prop: 'columnHeading' }) || '',
            //         dataIndex: 'standard-variation-dropdowns',
            //         key: 'standard-variation-dropdowns'
            //     };
            case 'product-meta':
                return {
                    title: getPropValue({ formStyles, item, prop: 'columnHeading' }) || '',
                    dataIndex: 'product-meta',
                    key: 'product-meta'
                };
            case 'global-attribute':
                return {
                    title: getPropValue({ formStyles, item, prop: 'columnHeading' }) || '',
                    dataIndex: 'global-attribute',
                    key: 'global-attribute'
                };
            case 'short-description':
                return {
                    title: getPropValue({ formStyles, item, prop: 'columnHeading' }) || 'Short Description',
                    dataIndex: 'short-description',
                    key: 'short-description'
                };
            case 'add-to-cart-checkbox':
                return {
                    title: <Checkbox
                        checked={selected}
                        onChange={() => {
                            setSelectedAll(!selectedAll)
                            setSomethingChanged(true);
                        }} />,
                    dataIndex: 'add-to-cart-checkbox',
                    key: 'add-to-cart-checkbox'
                };
            default:
                return {};

        }

    });


    const dataSource = formProducts !== undefined && formProducts.length > 0 ? formProducts.map((product: any) => {
        const printItemProps = { orderFormId, product, getPropValue, formStyles }
        return {
            key: product.id,
            'product-image': <PrintItem {...printItemProps} itemId='product-image' />,
            'product-name': <PrintItem {...printItemProps} itemId='product-name' />,
            'sku': <PrintItem {...printItemProps} itemId='sku' />,
            'in-stock-amount': <PrintItem {...printItemProps} itemId='in-stock-amount' />,
            'price': <PrintItem {...printItemProps} itemId='price' />,
            'quantity-input': <PrintItem {...printItemProps} itemId='quantity-input' />,
            'add-to-cart-button': <PrintItem {...printItemProps} itemId='add-to-cart-button' />,
            'combo-variation-dropdown': <PrintItem {...printItemProps} itemId='combo-variation-dropdown' />,
            // 'standard-variation-dropdowns': <PrintItem {...printItemProps} itemId='standard-variation-dropdowns' />,
            'product-meta': <PrintItem {...printItemProps} itemId='product-meta' />,
            'global-attribute': <PrintItem {...printItemProps} itemId='global-attribute' />,
            'short-description': <PrintItem {...printItemProps} itemId='short-description' />,
            'add-to-cart-checkbox': <PrintItem {...printItemProps} selectedAll={selectedAll} somethingChanged={somethingChanged} itemId='add-to-cart-checkbox' />
        };
    }) : [];

    let tableProps: any = {
        loading: fetching,
        dataSource: columns.length > 0 ? dataSource : [],
        columns: columns,
        pagination: false
    }

    const searchInput = typeof orderFormData['formFilters'][orderFormId] !== 'undefined' &&
        typeof orderFormData['formFilters'][orderFormId]['searchInput'] !== 'undefined' ?
        orderFormData['formFilters'][orderFormId]['searchInput'] : '';

    const selectedCategory = typeof orderFormData['formFilters'][orderFormId] !== 'undefined' &&
        typeof orderFormData['formFilters'][orderFormId]['selectedCategory'] !== 'undefined' ?
        orderFormData['formFilters'][orderFormId]['selectedCategory'] : '';

    const handleSorting = (pagination: any, filters: any, sorter: any) => {

        const sort_order = sorter.order === 'ascend' ? 'asc' : 'desc';
        const sort_by = sorter.field === 'product-name' ? 'title' : '';

        fetchProducts({
            sort_order: sort_order,
            sort_by: sort_by,
            search: searchInput,
            category: selectedCategory,
            active_page: orderFormData.formPagination[orderFormId]['active_page'] || 1,
            searching: 'no',
            show_all: false,
            attributes: { id: orderFormId },
            wholesale_role: Options.wholesale_role,
            per_page: orderFormData.formSettings[orderFormId]['products_per_page'] || 10,
            form_settings: orderFormData.formSettings[orderFormId]
        })

    }

    return (
        <div className='form-table'>
            <Table
                className="wwof-order-form"
                {...tableProps}

                onChange={(pagination: any, filters: any, sorter: any) => handleSorting(pagination, filters, sorter)}
            />
        </div >
    );

}

const mapStateToProps = (store: any, props: any) => ({
    orderForm: store.orderForm,
    orderFormData: store.orderFormData
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
    actions: bindActionCreators({
        updateSelectedProducts,
        fetchProducts
    }, dispatch)
});

export default connect(mapStateToProps, mapDispatchToProps)(FormTable);