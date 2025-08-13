
import React from 'react';
import { Pagination } from 'antd';

import { bindActionCreators, Dispatch } from "redux";
import { connect } from 'react-redux';

// Actions
import { } from '../../../store/actions';

const OrderFormPagination = React.memo((props: any) => {

    const { orderFormId, orderFormData, styles, fetchProducts } = props;

    if (typeof orderFormData['formPagination'][orderFormId] === 'undefined')
        return (<></>);

    const searchInput = typeof orderFormData['formFilters'][orderFormId] !== 'undefined' &&
        typeof orderFormData['formFilters'][orderFormId]['searchInput'] !== 'undefined' ?
        orderFormData['formFilters'][orderFormId]['searchInput'] : '';

    const selectedCategory = typeof orderFormData['formFilters'][orderFormId] !== 'undefined' &&
        typeof orderFormData['formFilters'][orderFormId]['selectedCategory'] !== 'undefined' ?
        orderFormData['formFilters'][orderFormId]['selectedCategory'] : '';

    return (
        <>
            <Pagination
                current={orderFormData['formPagination'][orderFormId]['active_page'] || 1}
                total={parseInt(orderFormData['formPagination'][orderFormId]['total_products']) || 0}
                pageSize={parseInt(orderFormData['formPagination'][orderFormId]['per_page']) || 0}
                style={{ ...styles }}
                onChange={(active_page: number) => {
                    fetchProducts({
                        search: searchInput,
                        category: selectedCategory,
                        active_page: active_page || orderFormData['formPagination'][orderFormId]['active_page'],
                        searching: 'no',
                    })
                }}
            />
        </>
    );

});

const mapStateToProps = (store: any) => ({
    pagination: store.pagination,
    orderFormData: store.orderFormData,
    filter: store.filter
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
    actions: bindActionCreators({}, dispatch)
});

export default connect(mapStateToProps, mapDispatchToProps)(OrderFormPagination);
