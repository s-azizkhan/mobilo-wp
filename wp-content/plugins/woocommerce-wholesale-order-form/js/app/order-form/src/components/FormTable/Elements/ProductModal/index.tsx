
import React, { useState, useEffect } from 'react';
import { Avatar, Select, Modal, Row, Col } from 'antd';

import { bindActionCreators, Dispatch } from "redux";
import { connect } from 'react-redux';

import DisplayVariations from './DisplayVariations';
import AddToCart from './AddToCart';

import { productListActions, orderFormDataActions } from '../../../../store/actions';
const { addProductToCartAction } = productListActions;
const { setCartSubtotal } = orderFormDataActions;

const { Option } = Select;

declare var Options: any;

const ProductModal = (props: any) => {

    const { orderFormData, itemProps, showModal, setShowModal } = props;
    const { orderFormId, product } = itemProps;

    const [selectedVariation, setSelectedVariation] = useState(0);
    const [variationName, setVariationName] = useState('');

    const [productName, setProductName] = useState(
        product.name
    )
    const [imageUrl, setImageUrl] = useState(
        Options.site_url + '/wp-content/uploads/woocommerce-placeholder-300x300.png'
    );
    const [priceHtml, setPriceHtml] = useState(
        product.price_html
    );

    const [shortDescription, setShortDescription] = useState(
        product.short_description
    );
    const [description, setDescription] = useState(
        product.description
    );

    const extraProps = {
        selectedVariation,
        setSelectedVariation,
        variationName,
        setVariationName
    }

    useEffect(() => {

        try {

            if (
                selectedVariation > 0 &&
                typeof orderFormData.formProducts[orderFormId] !== 'undefined'
            ) {
                const variations = orderFormData.formProducts[orderFormId]['variations'][product.id];
                const variation = variations.find((variation: any) => {
                    return variation.id === selectedVariation;
                });

                if (typeof variation !== 'undefined') {

                    setProductName(
                        typeof variation.name !== 'undefined' ? variation.name : product.name
                    )

                    if (typeof variation !== 'undefined' && typeof variation.images !== 'undefined' && variation.images.length > 0) {
                        setImageUrl(variation.images[0]['src'])
                    } else if (typeof variation.image !== 'undefined' && variation.image !== null) {
                        setImageUrl(variation.image['src'])
                    }

                    if (
                        typeof variation.wholesale_data !== 'undefined' &&
                        typeof variation.wholesale_data.price_html !== 'undefined'
                    )
                        setPriceHtml(variation.wholesale_data.price_html)
                    else
                        setPriceHtml(variation.price)

                    setShortDescription(variation.short_description || "")

                    setDescription(variation.description || "")

                }

            } else {

                setProductName(product.name)

                if (product.images.length > 0) {
                    setImageUrl(product.images[0]['src'])
                } else {
                    setImageUrl(
                        Options.site_url + '/wp-content/uploads/woocommerce-placeholder-300x300.png'
                    )
                }

                if (
                    typeof Options.wholesale_role !== 'undefined' &&
                    typeof product.wholesale_data !== 'undefined' &&
                    typeof product.wholesale_data.price_html !== 'undefined' &&
                    Options.wholesale_role !== '') {
                    setPriceHtml(product.wholesale_data.price_html)
                } else {
                    setPriceHtml(product.price_html)
                }

                setShortDescription(product.short_description || "")

                setDescription(product.description || "")

            }

        } catch (error) {
            console.log(error)
        }

    }, [selectedVariation]);

    return (
        <Modal
            title=""
            visible={showModal}
            onCancel={() => {
                setShowModal(false)
            }}
            width="650px"
            footer={null}>
            <Row>
                <Col xs={12} sm={12} md={12} lg={12} xl={12}>
                    <Avatar src={imageUrl} shape="square" size={200} />
                </Col>
                <Col xs={12} sm={12} md={12} lg={12} xl={12}>
                    <h2>{productName}</h2>
                    <p style={{ fontSize: "16px" }} dangerouslySetInnerHTML={{ __html: priceHtml }}></p>
                    <p style={{ fontSize: "16px" }} dangerouslySetInnerHTML={{ __html: shortDescription }}></p>
                    <p style={{ fontSize: "16px" }} dangerouslySetInnerHTML={{ __html: description }}></p>
                    <div>
                        <DisplayVariations {...props} {...extraProps} />
                    </div>
                    <div>
                        <AddToCart {...props} {...extraProps} />
                    </div>
                </Col>
            </Row>
        </Modal>
    );

}

const mapStateToProps = (store: any) => ({
    orderForm: store.orderForm,
    orderFormData: store.orderFormData
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
    actions: bindActionCreators({
        addProductToCartAction,
        setCartSubtotal
    }, dispatch)
});

export default connect(mapStateToProps, mapDispatchToProps)(ProductModal);
