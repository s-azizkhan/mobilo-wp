import React from 'react';
import { Button, notification, List } from 'antd';
import { ShoppingCartOutlined } from '@ant-design/icons';

import { bindActionCreators, Dispatch } from "redux";
import { connect } from 'react-redux';

import { productListActions, orderFormDataActions } from '../../../store/actions/';
const { addProductsToCartAction } = productListActions;
const { setCartSubtotal } = orderFormDataActions;

const AddSelectedProductsToCartButton = (props: any) => {

    const { orderForm, orderFormData, buttonText, styles, orderFormId, actions } = props;
    const { addProductsToCartAction } = actions;

    const addToCartSelectedProducts = () => {

        let products = orderFormData.formSelectedProducts[orderFormId];

        addProductsToCartAction({
            products,
            form_settings: orderFormData.formSettings[orderFormId],
            successCB: (args: any) => {

                let added: any = [];
                const successfully_added = args.data.successfully_added;

                Object.keys(successfully_added).forEach(productId => {

                    const product = products[productId] || {};

                    if (Object.keys(product).length > 0) {

                        if (['simple', 'variation'].includes(product.productType))
                            added.push(<div dangerouslySetInnerHTML={{ __html: '<b>' + product.productTitle + '</b> x ' + successfully_added[productId] }} />);

                    } else {

                        Object.keys(products).map((id: any, test: any) => {

                            if (typeof products[id].variationID !== 'undefined' &&
                                parseInt(products[id].variationID) === parseInt(productId)
                            ) {
                                added.push(<div dangerouslySetInnerHTML={{ __html: '<b>' + products[id].productTitle + '</b> x ' + successfully_added[productId] + '<br/>' + products[id].name }} />);
                            }

                        });

                    }

                });

                let failed: any = [];
                const failed_to_add = args.data.failed_to_add;
                failed_to_add.forEach((data: any, index: number) => {

                    const product = products[data.product_id] || {};

                    if (product)
                        failed.push(<div dangerouslySetInnerHTML={{ __html: '<b>' + product.productTitle + '</b> x ' + data.quantity + '<br/>' + data.error_message }} />);
                });

                if (added.length > 0) {
                    notification['success']({
                        message: 'Succesfully Added:',
                        description:
                            <div>
                                <List
                                    size="small"
                                    bordered
                                    dataSource={added}
                                    renderItem={(item: any) => <List.Item>{item}</List.Item>}
                                />
                                <a href={orderForm.cart_url} target="_blank"><Button style={{ marginTop: '10px' }} >View Cart<ShoppingCartOutlined /></Button></a>
                            </div>,
                        duration: 10
                    });
                }

                if (failed.length > 0) {
                    notification['error']({
                        message: 'Add to Cart Failed:',
                        description:
                            <List
                                size="small"
                                bordered
                                dataSource={failed}
                                renderItem={(item: any) => <List.Item>{item}</List.Item>}
                            />,
                        duration: 10
                    });
                }

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
            failCB: (args: any) => {

                notification['error']({
                    message: 'Add to Cart Failed:',
                    description: 'error'
                });

            }
        });
    }

    if (
        typeof orderFormData.formSelectedProducts[orderFormId] !== 'undefined' &&
        Object.keys(orderFormData.formSelectedProducts[orderFormId]).length > 0
    )
        return (
            <Button
                type="primary"
                style={{ ...styles }}
                onClick={() => addToCartSelectedProducts()}
            >{buttonText}</Button>
        );
    else
        return (
            <Button
                type="primary"
                style={{ ...styles }}
                disabled
            >{buttonText}</Button>
        );
}

const mapStateToProps = (store: any, props: any) => ({
    orderForm: store.orderForm,
    orderFormData: store.orderFormData,
    formStyles: store.orderFormData.formStyles[props.orderFormId]
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
    actions: bindActionCreators({
        addProductsToCartAction,
        setCartSubtotal
    }, dispatch)
});

export default connect(mapStateToProps, mapDispatchToProps)(AddSelectedProductsToCartButton);