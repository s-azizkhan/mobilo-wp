import React, { useEffect, useState } from 'react';
import { Select } from 'antd';

import { bindActionCreators, Dispatch } from "redux";
import { connect } from 'react-redux';

import { orderFormDataActions } from '../../../store/actions/';
const { setSelectedProductsToAddToCart } = orderFormDataActions;

const { Option } = Select;

const ComboVariations = (props: any) => {

    const { orderFormData, itemProps, actions } = props;
    const { orderFormId, product } = itemProps;
    const { setSelectedProductsToAddToCart } = actions;
    const [selectedVariation, setSelectedVariation] = useState(0);

    useEffect(() => {

        try {

            if (
                typeof orderFormData.formSelectedProducts[orderFormId] !== 'undefined' &&
                typeof orderFormData.formSelectedProducts[orderFormId][product.id] !== 'undefined' &&
                typeof orderFormData.formSelectedProducts[orderFormId][product.id].variationID !== 'undefined'
            ) {
                setSelectedVariation(orderFormData.formSelectedProducts[orderFormId][product.id].variationID)
            }

        } catch (error) {
            console.log(error)
        }

    }, [orderFormData.formPagination[orderFormId]]);

    if (
        product.type !== 'variable' ||
        typeof orderFormData.formProducts[orderFormId] === 'undefined' ||
        typeof orderFormData.formProducts[orderFormId]['variations'] === 'undefined'
    )
        return (<></>);

    const variations = orderFormData.formProducts[orderFormId]['variations'][product.id];

    if (typeof variations === 'undefined' || variations.length <= 0)
        return (<></>);

    const variationsOptions = variations.map((variation: any) => {
        const name = variation.attributes.map((attributes: any) => {
            return <span key={attributes.id}>{attributes.name + ': ' + attributes.option} </span>
        });
        return <Option key={variation.id} value={variation.id}>{name}</Option>;
    });

    const onChange = (variationID: number) => {

        if (typeof variationID !== 'undefined') {

            // Set selected variation id
            setSelectedVariation(variationID);

            // Set selected variation name
            const variation = variations.find((variation: any) => {
                return variation.id === variationID;
            });
            const name = variation.attributes.map((attributes: any) => {
                return attributes.name + ' : ' + attributes.option;
            });

            // Add selected variation into the state selected products
            if (
                typeof orderFormData.formSelectedProducts[orderFormId] !== 'undefined' &&
                typeof orderFormData.formSelectedProducts[orderFormId][product.id] !== 'undefined'
            ) {
                setSelectedProductsToAddToCart({
                    [orderFormId]: {
                        ...orderFormData.formSelectedProducts[orderFormId],
                        [product.id]: {
                            ...orderFormData.formSelectedProducts[orderFormId][product.id],
                            productID: product.id,
                            variationID,
                            productTitle: product.name,
                            productType: product.type,
                            name: name.join('<br/>')
                        }

                    }
                })
            } else {
                setSelectedProductsToAddToCart({
                    [orderFormId]: {
                        ...orderFormData.formSelectedProducts[orderFormId],
                        [product.id]: {
                            productID: product.id,
                            variationID,
                            productTitle: product.name,
                            productType: product.type,
                            name: name.join('<br/>')
                        }

                    }
                })
            }

        } else {

            setSelectedVariation(0);

            const formSelectedProducts = orderFormData.formSelectedProducts[orderFormId];
            delete formSelectedProducts[product.id];

            setSelectedProductsToAddToCart({
                [orderFormId]: {
                    ...orderFormData.formSelectedProducts[orderFormId],
                    ...formSelectedProducts
                }
            })

        }

    }

    let selectProps = {}
    if (selectedVariation !== 0)
        selectProps = {
            value: selectedVariation
        }

    return (
        <Select
            showSearch
            placeholder='Select Variation'
            style={{ width: 250, marginTop: 10 }}
            filterOption={false}
            notFoundContent='No results found'
            allowClear={true}
            {...selectProps}
            onChange={(variationId: any) => onChange(variationId)}
        >
            {variationsOptions}
        </Select>
    );
}

const mapStateToProps = (store: any, props: any) => ({
    orderFormData: store.orderFormData,
    formStyles: store.orderFormData.formStyles[props.orderFormId],
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
    actions: bindActionCreators({
        setSelectedProductsToAddToCart
        // addProductToCartAction
    }, dispatch)
});

export default connect(mapStateToProps, mapDispatchToProps)(ComboVariations);