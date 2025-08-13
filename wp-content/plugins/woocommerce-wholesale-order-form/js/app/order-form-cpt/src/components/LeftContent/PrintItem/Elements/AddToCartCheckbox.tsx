
import React, { useEffect, useState } from 'react';
import { Checkbox } from 'antd';

import { bindActionCreators, Dispatch } from "redux";
import { connect } from 'react-redux';

const ShortDescription = (props: any) => {

    const { key, product, selectedAll } = props;
    const [selected, setSelected] = useState(false);

    useEffect(() => {

        setSelected(selectedAll);

    }, [selectedAll]);

    return (
        <div key={key} className='row'>
            <Checkbox
                checked={selected}
                onChange={(e) => setSelected(e.target.checked)}
            />
        </div>
    );

}

const mapStateToProps = (store: any) => ({});

const mapDispatchToProps = (dispatch: Dispatch) => ({
    actions: bindActionCreators({}, dispatch)
});

export default connect(mapStateToProps, mapDispatchToProps)(ShortDescription);
