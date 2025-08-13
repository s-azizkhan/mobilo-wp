
import React, { useState, useEffect } from 'react';

import { bindActionCreators, Dispatch } from "redux";
import { connect } from 'react-redux';

const ProductSku = (props: any) => {

    const { orderFormData, itemProps } = props;
    const { orderFormId, product } = itemProps;
    const [productSku, setProductSku] = useState(product.sku);

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

                        if (typeof variationData !== 'undefined')
                            setProductSku(variationData.sku)

                    }

                }

            } else {
                setProductSku(product.sku)
            }

        } catch (error) {
            console.log(error)
        }

    }, [orderFormData.formSelectedProducts[orderFormId]]);

    return (
        <div className="product-sku">
            {productSku}
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

export default connect(mapStateToProps, mapDispatchToProps)(ProductSku);
