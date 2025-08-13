
import React, { useEffect, useState } from 'react';

import { bindActionCreators, Dispatch } from "redux";
import { connect } from 'react-redux';

const ShortDescription = (props: any) => {

    const { orderFormData, itemProps } = props;
    const { orderFormId, product } = itemProps;
    const [productDescription, setProductDescription] = useState(product.short_description);

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
                            setProductDescription(variationData.description)

                    }

                }

            } else {
                setProductDescription(product.short_description)
            }

        } catch (error) {
            console.log(error)
        }

    }, [orderFormData.formSelectedProducts[orderFormId]]);

    return (
        <div
            className='row'
            dangerouslySetInnerHTML={{ __html: productDescription }}>
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

export default connect(mapStateToProps, mapDispatchToProps)(ShortDescription);
