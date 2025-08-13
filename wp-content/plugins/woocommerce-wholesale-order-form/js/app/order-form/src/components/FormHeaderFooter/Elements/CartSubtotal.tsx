
import React from 'react';

import { bindActionCreators, Dispatch } from "redux";
import { connect } from 'react-redux';

const CartSubtotal = (props: any) => {

    const { orderFormData, orderFormId, styles } = props;

    if (typeof orderFormData.formCartSubtotal[orderFormId] === 'undefined')
        return (<></>);

    return (
        <div
            style={{ ...styles }}
            dangerouslySetInnerHTML={{ __html: orderFormData.formCartSubtotal[orderFormId].cartSubtotal }}>
        </div>
    );

}

const mapStateToProps = (store: any) => ({
    orderFormData: store.orderFormData
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
    actions: bindActionCreators({}, dispatch)
});

export default connect(mapStateToProps, mapDispatchToProps)(CartSubtotal);
