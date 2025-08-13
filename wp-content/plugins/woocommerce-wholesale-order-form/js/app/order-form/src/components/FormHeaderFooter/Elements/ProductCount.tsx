

import React from 'react';

import { bindActionCreators, Dispatch } from "redux";
import { connect } from 'react-redux';

const ProductCount = React.memo((props: any) => {

    const { orderFormData, styles, orderFormId } = props;

    if (typeof orderFormData.formPagination[orderFormId] === 'undefined')
        return (<></>);

    const productCount = orderFormData.formPagination[orderFormId].total_products || 0;

    return (
        <div
            style={{ ...styles }}
            dangerouslySetInnerHTML={{ __html: `${productCount} Product(s)` }}>
        </div>
    );

});

const mapStateToProps = (store: any) => ({
    orderForm: store.orderForm,
    orderFormData: store.orderFormData,
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
    actions: bindActionCreators({}, dispatch)
});

export default connect(mapStateToProps, mapDispatchToProps)(ProductCount);
