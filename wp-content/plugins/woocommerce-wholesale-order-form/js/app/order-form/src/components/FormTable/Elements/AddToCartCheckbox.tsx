import React, { useState, useEffect } from 'react';
import { Checkbox } from 'antd';

import { bindActionCreators, Dispatch } from "redux";
import { connect } from 'react-redux';

import { orderFormDataActions } from '../../../store/actions/';
const { updateSelectedProducts } = orderFormDataActions;

const AddToCartButton = (props: any) => {

    const { orderFormData, itemProps, actions } = props;
    const { orderFormId, selectedAll, somethingChanged, product } = itemProps;
    const { updateSelectedProducts } = actions;
    const [selected, setSelected] = useState(false);

    const onChange = (e: any) => {

        setSelected(e.target.checked);

        updateSelectedProducts({
            selected: e.target.checked,
            orderFormData,
            orderFormId,
            product
        });

    }

    useEffect(() => {

        try {
            if (somethingChanged)
                setSelected(selectedAll);
        } catch (error) {
            console.log(error)
        }

    }, [selectedAll, somethingChanged]);

    useEffect(() => {

        try {
            if (typeof orderFormData.formSelectedProducts[orderFormId] !== 'undefined') {

                if (typeof orderFormData.formSelectedProducts[orderFormId][product.id] !== 'undefined')
                    setSelected(true)
                else
                    setSelected(false)
            }
        } catch (error) {
            console.log(error)
        }

    }, [orderFormData.formPagination[orderFormId], orderFormData.formSelectedProducts[orderFormId]]);

    return (
        <Checkbox
            checked={selected}
            onChange={(e) => onChange(e)}
        />
    );
}

const mapStateToProps = (store: any, props: any) => ({
    orderForm: store.orderForm,
    orderFormData: store.orderFormData,
    formStyles: store.orderFormData.formStyles[props.orderFormId]
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
    actions: bindActionCreators({
        updateSelectedProducts
    }, dispatch)
});

export default connect(mapStateToProps, mapDispatchToProps)(AddToCartButton);