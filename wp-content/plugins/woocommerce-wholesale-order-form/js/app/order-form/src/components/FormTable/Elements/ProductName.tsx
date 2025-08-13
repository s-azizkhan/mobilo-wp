
import React, { useState, useEffect } from 'react';
import { Button } from 'antd';
import ProductModal from './ProductModal';

import { bindActionCreators, Dispatch } from "redux";
import { connect } from 'react-redux';

const ProductName = (props: any) => {

    const { orderFormData, itemProps } = props;
    const { orderFormId, product, getPropValue, formStyles, itemId } = itemProps;
    const [productName, setProductName] = useState(product.name);
    const [showModal, setShowModal] = useState(false);

    const showPopup = getPropValue({ formStyles, item: itemId, prop: 'showPopup' });

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

                        if (typeof variationData !== 'undefined' && typeof variationData.name !== 'undefined')
                            setProductName(variationData.name)

                    }

                }

            } else {
                setProductName(product.name)
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
        <div className="product-name">
            {showPopup ? <ProductModal {...modalProps} /> : ''}
            {showPopup ? <Button type="link" onClick={(e: any) => setShowModal(true)}>{productName}</Button> : productName}
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

export default connect(mapStateToProps, mapDispatchToProps)(ProductName);
