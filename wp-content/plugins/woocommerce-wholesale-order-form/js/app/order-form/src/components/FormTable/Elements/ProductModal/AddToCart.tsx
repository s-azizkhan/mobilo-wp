
import React, { useState } from 'react';
import { InputNumber, Button, notification } from 'antd';
import { ShoppingCartOutlined } from '@ant-design/icons';

const AddToCart = (props: any) => {

    const { orderForm, itemProps, variation, actions, selectedVariation, variationName } = props;
    const { orderFormId, product } = itemProps;

    const [quantity, setQuantity] = useState(1);

    return (
        <div style={{ marginBottom: '10px' }}>
            {product.stock_quantity === 0 || (variation && variation.stock_quantity === 0) ?
                <div>
                    <p className="outofstock">Out of Stock</p>
                    <InputNumber min={1} defaultValue={1} style={{ marginRight: '10px' }} />
                    <Button type='primary' disabled>Add To Cart</Button>
                </div>
                :
                <div>
                    <InputNumber
                        min={1}
                        defaultValue={1}
                        onChange={(quantity: any) => setQuantity(quantity)}
                        style={{ marginRight: '10px' }} />
                    <Button
                        type='primary'
                        onClick={(e: any) => {

                            actions.addProductToCartAction({
                                'product_type': product.type,
                                'product_id': product.id,
                                'variation_id': selectedVariation,
                                'quantity': quantity,
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

                        }}>Add To Cart</Button>
                </div>
            }
        </div>
    )

}

export default AddToCart;