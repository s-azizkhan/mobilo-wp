
import React from 'react';

import { bindActionCreators, Dispatch } from "redux";
import { connect } from 'react-redux';

const ShortDescription = (props: any) => {

    const { product, key } = props;

    return (
        <div
            key={key}
            className='row'
            dangerouslySetInnerHTML={{ __html: product.short_description }}>
        </div>
    );

}

const mapStateToProps = (store: any) => ({});

const mapDispatchToProps = (dispatch: Dispatch) => ({
    actions: bindActionCreators({}, dispatch)
});

export default connect(mapStateToProps, mapDispatchToProps)(ShortDescription);
