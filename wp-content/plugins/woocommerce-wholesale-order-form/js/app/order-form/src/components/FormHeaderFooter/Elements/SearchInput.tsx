
import React from 'react';
import { Input } from 'antd';

import { bindActionCreators, Dispatch } from "redux";
import { connect } from 'react-redux';

// Actions
import { orderFormDataActions } from '../../../store/actions';
const { setFormFilters } = orderFormDataActions;

const SearchInput = (props: any) => {

    const { orderFormData, placeholder, styles, actions, orderFormId, fetchProducts, submitOnEnter } = props;
    const { setFormFilters } = actions;

    const searchInput = typeof orderFormData['formFilters'][orderFormId] === 'undefined' ? "" : orderFormData['formFilters'][orderFormId]['searchInput'];

    const onTextEnter = (e: any) => {

        e.preventDefault();

        let selectedCategory = '';

        if (
            typeof orderFormData.formFilters[orderFormId] !== 'undefined' &&
            typeof orderFormData.formFilters[orderFormId].selectedCategory !== 'undefined'
        )
            selectedCategory = orderFormData.formFilters[orderFormId].selectedCategory;

        if (submitOnEnter) {
            fetchProducts({
                search: e.target.value,
                category: selectedCategory,
                active_page: 1,
                searching: 'yes'
            });
        }
    }

    return (
        <>
            <Input
                placeholder={placeholder}
                style={{ ...styles }}
                onChange={e => setFormFilters({
                    [orderFormId]: {
                        ...orderFormData['formFilters'][orderFormId],
                        searchInput: e.target.value
                    }
                })}
                value={searchInput}
                onPressEnter={(e) => onTextEnter(e)}
            />
        </>
    );

}

const mapStateToProps = (store: any) => ({
    orderFormData: store.orderFormData,
    filter: store.filter
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
    actions: bindActionCreators({
        setFormFilters
    }, dispatch)
});

export default connect(mapStateToProps, mapDispatchToProps)(SearchInput);
