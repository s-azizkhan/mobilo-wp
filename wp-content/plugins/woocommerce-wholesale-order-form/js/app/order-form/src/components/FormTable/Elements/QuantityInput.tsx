import React, { useState, useEffect } from 'react';
import { InputNumber } from 'antd';

import { bindActionCreators, Dispatch } from "redux";
import { connect } from 'react-redux';

import { orderFormDataActions } from '../../../store/actions/';
const { setSelectedProductsToAddToCart } = orderFormDataActions;

const QuantityInput = (props: any) => {

    const { orderFormData, itemProps, actions } = props;
    const { orderFormId, product } = itemProps;
    const { setSelectedProductsToAddToCart } = actions;
    const [quantity, setQuantity] = useState(1);

    useEffect(() => {

        try {

            if (
                typeof orderFormData.formSelectedProducts[orderFormId] !== 'undefined' &&
                typeof orderFormData.formSelectedProducts[orderFormId][product.id] !== 'undefined' &&
                typeof orderFormData.formSelectedProducts[orderFormId][product.id].quantity !== 'undefined'
            ) {
                setQuantity(orderFormData.formSelectedProducts[orderFormId][product.id].quantity)
            }

        } catch (error) {
            console.log(error)
        }

    }, [orderFormData.formPagination[orderFormId]]);

    useEffect(() => {

        try {

            if (
                typeof orderFormData.formSelectedProducts[orderFormId] !== 'undefined' &&
                typeof orderFormData.formSelectedProducts[orderFormId][product.id] !== 'undefined' &&
                typeof orderFormData.formSelectedProducts[orderFormId][product.id].quantity === 'undefined'
            ) {
                setSelectedProductsToAddToCart({
                    [orderFormId]: {
                        ...orderFormData.formSelectedProducts[orderFormId],
                        [product.id]: {
                            ...orderFormData.formSelectedProducts[orderFormId][product.id],
                            quantity
                        }
                    }
                })
            }

        } catch (error) {
            console.log(error)
        }

    }, [orderFormData.formSelectedProducts[orderFormId]]);

    const onChange = (quantity: number) => {

        setQuantity(quantity);

        if (
            typeof orderFormData.formSelectedProducts[orderFormId] !== 'undefined' &&
            typeof orderFormData.formSelectedProducts[orderFormId][product.id] !== 'undefined'
        ) {
            setSelectedProductsToAddToCart({
                [orderFormId]: {
                    ...orderFormData.formSelectedProducts[orderFormId],
                    [product.id]: {
                        ...orderFormData.formSelectedProducts[orderFormId][product.id],
                        quantity
                    }
                }
            })
        } else {
            setSelectedProductsToAddToCart({
                [orderFormId]: {
                    ...orderFormData.formSelectedProducts[orderFormId],
                    [product.id]: {
                        productID: product.id,
                        productTitle: product.name,
                        productType: product.type,
                        quantity
                    }
                }
            })
        }

    }

    return (
        <InputNumber
            min={1}
            defaultValue={1}
            value={quantity}
            onChange={(quantity: any) => onChange(quantity)}
        />
    );
}

const mapStateToProps = (store: any, props: any) => ({
    orderFormData: store.orderFormData,
    formStyles: store.orderFormData.formStyles[props.orderFormId],
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
    actions: bindActionCreators({
        setSelectedProductsToAddToCart
        // addProductToCartAction
    }, dispatch)
});

export default connect(mapStateToProps, mapDispatchToProps)(QuantityInput);