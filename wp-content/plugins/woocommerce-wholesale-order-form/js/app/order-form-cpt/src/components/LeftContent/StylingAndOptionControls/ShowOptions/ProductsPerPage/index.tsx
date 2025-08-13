import React, { useState, useEffect } from 'react';
import { InputNumber } from 'antd';

// Redux
import { bindActionCreators, Dispatch } from "redux";
import { connect } from 'react-redux';
import { paginationActions, productActions, orderFormActions } from '../../../../../store/actions';

const { setOrderFormSettingsData } = orderFormActions;
const { setPaginationState } = paginationActions;
const { fetchProducts } = productActions;

const ProductsPerPage = (props: any) => {

    const { products, styling, setStyles, id, target, updateStyling, getPropValue, actions } = props;
    const { setPaginationState, setOrderFormSettingsData } = actions;
    const [value, setValue] = useState(
        getPropValue({ styling, id, target, style: 'productsPerPage', extra: '' }) || 10
    );

    useEffect(() => {

        // If value is changed we update the 
        fetchProducts({
            per_page: value
        });

    }, [value]);

    useEffect(() => {

        // If products is updated we update the products page page
        setPaginationState({
            per_page: value
        });

    }, [products])

    useEffect(() => {

        // Save this in the settings
        setOrderFormSettingsData({
            'products_per_page': value
        });

    }, [value]);

    return (
        <div className="products-per-page">
            <label htmlFor="products-per-page">Products Per Page:</label>
            <InputNumber
                value={value}
                onChange={(val: any) => {
                    updateStyling({
                        setStyles,
                        styling,
                        id,
                        target,
                        toUpdate: {
                            productsPerPage: val
                        }
                    });
                    setValue(val)
                }}
            />
        </div>
    )
}

const mapStateToProps = (store: any) => ({
    products: store.products
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
    actions: bindActionCreators({
        setPaginationState,
        fetchProducts,
        setOrderFormSettingsData
    }, dispatch)
});

export default connect(mapStateToProps, mapDispatchToProps)(ProductsPerPage);