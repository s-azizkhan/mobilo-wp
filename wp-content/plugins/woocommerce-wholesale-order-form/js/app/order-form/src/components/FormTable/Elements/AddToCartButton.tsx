import React from 'react';
import { Button, notification } from 'antd';
import { ShoppingCartOutlined } from '@ant-design/icons';

import { bindActionCreators, Dispatch } from "redux";
import { connect } from 'react-redux';

import { productListActions, orderFormDataActions } from '../../../store/actions/';
const { addProductToCartAction } = productListActions;
const { setCartSubtotal } = orderFormDataActions;

const AddToCartButton = (props: any) => {

    const { orderForm, orderFormData, itemProps, actions } = props;
    const { orderFormId, product, getPropValue, formStyles, itemId } = itemProps;
    const { addProductToCartAction } = actions;

    const onClick = (e: any) => {

        let variationId = 0;
        let quantity = 1;
        let variationName = '';

        if (
            typeof orderFormData.formSelectedProducts[orderFormId] !== 'undefined' &&
            typeof orderFormData.formSelectedProducts[orderFormId][product.id] !== 'undefined'
        ) {
            if (typeof orderFormData.formSelectedProducts[orderFormId][product.id]['variationID'] !== 'undefined')
                variationId = orderFormData.formSelectedProducts[orderFormId][product.id]['variationID'];

            if (typeof orderFormData.formSelectedProducts[orderFormId][product.id]['quantity'] !== 'undefined')
                quantity = orderFormData.formSelectedProducts[orderFormId][product.id]['quantity'];

        }

        if (
            typeof orderFormData.formProducts[orderFormId]['variations'] !== 'undefined' &&
            typeof orderFormData.formProducts[orderFormId]['variations'][product.id] !== 'undefined' &&
            variationId != 0 &&
            product.type === 'variable'
        ) {

            const variationData = orderFormData.formProducts[orderFormId]['variations'][product.id].find((data: any) => {

                return data.id === variationId;
            });

            if (variationData !== undefined) {
                variationName = variationData.attributes.map((attributes: any) => {
                    return attributes.name + ': ' + attributes.option;
                });
            }

        }

        addProductToCartAction({
            'product_type': product.type,
            'product_id': product.id,
            'variation_id': variationId,
            'quantity': quantity,
            'form_settings': orderFormData.formSettings[orderFormId],
            successCB: (args: any) => {

                notification['success']({
                    message: 'Succesfully Added:',
                    description:
                        <div>
                            <div dangerouslySetInnerHTML={{ __html: '<b>' + product.name + '</b> x ' + quantity + '<br/>' + variationName }} />
                            <a href={orderForm.cart_url} target="_blank"><Button style={{ marginTop: '10px' }} >View Cart<ShoppingCartOutlined /></Button></a>
                        </div>,
                    duration: 10
                });

                // Update cart total by triggering the added_to_cart custom event of wc.
                const fragments: any = args.data.fragments;
                const cart_hash: any = args.data.cart_hash;

                const event = new CustomEvent('added_to_cart', { detail: { fragments: fragments, cart_hash: cart_hash } });
                document.body.dispatchEvent(event);

                // Update subtotal below the order form.
                actions.setCartSubtotal({
                    [orderFormId]: {
                        cartSubtotal: args.data.cart_subtotal_markup
                    }
                })

            },
            failCB: () => {

                notification['error']({
                    message: 'Add to Cart',
                    description: 'Add to cart failed.',
                    duration: 10
                });

            }
        });

    }

    return (
        <Button
            type='primary'
            onClick={(e) => onClick(e)}
        >
            {getPropValue({ formStyles, item: itemId, prop: 'buttonText' }) || 'Add To Cart'}
        </Button>
    );
}

const mapStateToProps = (store: any, props: any) => ({
    orderForm: store.orderForm,
    orderFormData: store.orderFormData,
    formStyles: store.orderFormData.formStyles[props.orderFormId]
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
    actions: bindActionCreators({
        addProductToCartAction,
        setCartSubtotal
    }, dispatch)
});

export default connect(mapStateToProps, mapDispatchToProps)(AddToCartButton);