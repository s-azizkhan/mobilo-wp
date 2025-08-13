
import React from 'react';
import { Button } from 'antd';

import { bindActionCreators, Dispatch } from "redux";
import { connect } from 'react-redux';

// Actions
import { } from '../../../store/actions';

const SearchButton = React.memo((props: any) => {

    const { orderFormData, buttonText, styles, orderFormId, fetchProducts } = props;

    const onFormSubmit = (e: any) => {

        e.preventDefault();

        const searchInput = typeof orderFormData['formFilters'][orderFormId] !== 'undefined' &&
            typeof orderFormData['formFilters'][orderFormId]['searchInput'] !== 'undefined' ?
            orderFormData['formFilters'][orderFormId]['searchInput'] : '';

        const selectedCategory = typeof orderFormData['formFilters'][orderFormId] !== 'undefined' &&
            typeof orderFormData['formFilters'][orderFormId]['selectedCategory'] !== 'undefined' ?
            orderFormData['formFilters'][orderFormId]['selectedCategory'] : '';

        const activePage = typeof orderFormData['formFilters'][orderFormId] !== 'undefined' &&
            typeof orderFormData['formFilters'][orderFormId]['active_page'] !== 'undefined' ?
            orderFormData['formFilters'][orderFormId]['active_page'] : 1;

        if (searchInput || selectedCategory) {
            fetchProducts({
                search: searchInput,
                category: selectedCategory,
                active_page: activePage,
                searching: 'yes'
            });
        }

    }

    return (
        <Button
            type="primary"
            style={{ ...styles }}
            onClick={(e: any) => onFormSubmit(e)}
        >{buttonText}</Button>
    );

});

const mapStateToProps = (store: any) => ({
    orderForm: store.orderForm,
    orderFormData: store.orderFormData,
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
    actions: bindActionCreators({}, dispatch)
});

export default connect(mapStateToProps, mapDispatchToProps)(SearchButton);
