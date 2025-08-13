
import React, { useState, useEffect } from 'react';

import { bindActionCreators, Dispatch } from "redux";
import { connect } from 'react-redux';

declare var Options: any;

const Price = (props: any) => {

    const { orderFormData, itemProps } = props;
    const { orderFormId, product } = itemProps;
    const [priceHtml, setPriceHtml] = useState(product.price_html);

    useEffect(() => {

        try {

            if (
                typeof Options.wholesale_role !== 'undefined' &&
                typeof product.wholesale_data !== 'undefined' &&
                typeof product.wholesale_data.price_html !== 'undefined' &&
                Options.wholesale_role !== '') {
                setPriceHtml(product.wholesale_data.price_html)
            }

        } catch (error) {
            console.log(error)
        }

    }, [Options.wholesale_role]);

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

                            if (
                                typeof variationData.wholesale_data !== 'undefined' &&
                                typeof variationData.wholesale_data.price_html !== 'undefined'
                            )
                                setPriceHtml(variationData.wholesale_data.price_html)
                            else
                                setPriceHtml(variationData.price_html || variationData.price)

                        }

                    }

                }

            } else {

                if (
                    typeof Options.wholesale_role !== 'undefined' &&
                    typeof product.wholesale_data !== 'undefined' &&
                    typeof product.wholesale_data.price_html !== 'undefined' &&
                    Options.wholesale_role !== '') {
                    setPriceHtml(product.wholesale_data.price_html)
                } else {
                    setPriceHtml(product.price_html)
                }

            }

        } catch (error) {
            console.log(error)
        }


    }, [orderFormData.formSelectedProducts[orderFormId]]);

    return (<div dangerouslySetInnerHTML={{ __html: priceHtml }} />);

}

const mapStateToProps = (store: any) => ({
    orderForm: store.orderForm,
    orderFormData: store.orderFormData
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
    actions: bindActionCreators({}, dispatch)
});

export default connect(mapStateToProps, mapDispatchToProps)(Price);
