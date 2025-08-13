
import React, { useState, useEffect } from 'react';

import { bindActionCreators, Dispatch } from "redux";
import { connect } from 'react-redux';

const InStockAmount = (props: any) => {

    const { orderFormData, itemProps } = props;
    const { orderFormId, product } = itemProps;
    const [stockQuantity, setStockQuantity] = useState(product.stock_quantity);

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
                            setStockQuantity(variationData.stock_quantity)

                    }

                }

            } else {
                setStockQuantity(product.stock_quantity)
            }

        } catch (error) {
            console.log(error)
        }


    }, [orderFormData.formSelectedProducts[orderFormId]]);

    return (
        <div className="instock-amount">
            {product.stock_status === "outofstock" ? "Out of stock" : stockQuantity}
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

export default connect(mapStateToProps, mapDispatchToProps)(InStockAmount);
