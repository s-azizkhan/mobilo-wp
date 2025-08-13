

import React from 'react';
import { Button } from 'antd';

import { bindActionCreators, Dispatch } from "redux";
import { connect } from 'react-redux';

// Actions
import { orderFormDataActions } from '../../../store/actions';
const { setFormFilters } = orderFormDataActions;

const SearchButton = React.memo((props: any) => {

    const { orderFormData, buttonText, styles, orderFormId, actions, fetchProducts } = props;
    const { setFormFilters } = actions;

    const showAll = (e: any) => {

        e.preventDefault();
        fetchProducts({
            search: '',
            category: '',
            active_page: 1,
            per_page: orderFormData.formPagination[orderFormId].per_page || 10,
            searching: 'no',
            sort_order: '',
            products: '',
            categories: '',
            show_all: true
        });

        setFormFilters({
            [orderFormId]: {
                searchInput: '',
                selectedCategory: ''
            }
        })
    }

    return (
        <Button
            type="primary"
            style={{ ...styles }}
            onClick={(e: any) => showAll(e)}
        >{buttonText}</Button>
    );

});

const mapStateToProps = (store: any) => ({
    orderForm: store.orderForm,
    orderFormData: store.orderFormData,
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
    actions: bindActionCreators({
        setFormFilters
    }, dispatch)
});

export default connect(mapStateToProps, mapDispatchToProps)(SearchButton);
