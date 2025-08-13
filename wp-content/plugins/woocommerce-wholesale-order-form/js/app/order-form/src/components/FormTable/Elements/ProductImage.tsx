
import React, { useState, useEffect } from 'react';
import { Avatar, Button } from 'antd';
import ProductModal from './ProductModal';

import { bindActionCreators, Dispatch } from "redux";
import { connect } from 'react-redux';

declare var Options: any;

const ProductImage = (props: any) => {

    const { orderFormData, itemProps } = props;
    const { orderFormId, product, getPropValue, formStyles, itemId } = itemProps;
    const [url, setUrl] = useState(
        Options.site_url + '/wp-content/uploads/woocommerce-placeholder-300x300.png'
    );
    const [showModal, setShowModal] = useState(false);

    const showPopup = getPropValue({ formStyles, item: itemId, prop: 'showPopup' });

    useEffect(() => {
        try {
            if (product.images.length > 0) {
                setUrl(product.images[0]['src'])
            }
        } catch (error) {
            console.log(error)
        }
    }, [orderFormData.formPagination[orderFormId]])

    useEffect(() => {

        try {

            if (
                typeof orderFormData.formSelectedProducts[orderFormId] !== 'undefined' &&
                Object.keys(orderFormData.formSelectedProducts[orderFormId]).length > 0
            ) {

                if (typeof orderFormData.formSelectedProducts[orderFormId][product.id] !== 'undefined' && product.type === 'variable') {
                    const variationID = orderFormData.formSelectedProducts[orderFormId][product.id]['variationID'];

                    if (typeof orderFormData.formProducts[orderFormId]['variations'][product.id] !== 'undefined') {

                        const variations = orderFormData.formProducts[orderFormId]['variations'][product.id];
                        const variationData = variations.find((variation: any) => {
                            return variation.id === variationID;
                        })

                        if (typeof variationData !== 'undefined') {
                            if (typeof variationData.images !== 'undefined' && variationData.images.length > 0) {
                                setUrl(variationData.images[0]['src'])
                            } else if (typeof variationData.image !== 'undefined' && variationData.image !== null) {
                                setUrl(variationData.image['src'])
                            }
                        }

                    }

                }

            } else {

                if (product.images.length > 0) {
                    setUrl(product.images[0]['src'])
                } else {
                    setUrl(
                        Options.site_url + '/wp-content/uploads/woocommerce-placeholder-300x300.png'
                    )
                }

            }

        } catch (error) {
            console.log(error)
        }


    }, [orderFormData.formSelectedProducts[orderFormId]]);

    const modalProps = {
        ...props,
        showModal,
        setShowModal
    }

    return (
        <div className="product-image">
            {showPopup ? <ProductModal {...modalProps} /> : ''}
            {showPopup ?
                <Button type="link" onClick={(e: any) => setShowModal(true)}>
                    <Avatar
                        src={url}
                        shape="square"
                        style={{ width: '48px', height: '48px' }}
                    />
                </Button>
                :
                <Avatar
                    src={url}
                    shape="square"
                    style={{ width: '48px', height: '48px' }}
                />
            }


        </div>
    );

}

const mapStateToProps = (store: any) => ({
    orderForm: store.orderForm,
    orderFormData: store.orderFormData
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
    actions: bindActionCreators({}, dispatch)
});

export default connect(mapStateToProps, mapDispatchToProps)(ProductImage);
