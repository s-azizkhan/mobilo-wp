import React, { useEffect, useState } from 'react';
import { Checkbox } from 'antd';

// Redux
import { bindActionCreators, Dispatch } from "redux";
import { connect } from 'react-redux';

import { orderFormActions } from '../../../../../store/actions';

const { setOrderFormSettingsData } = orderFormActions;

const Sort = (props: any) => {

    const { styling, setStyles, id, target, updateStyling, getPropValue, actions } = props;
    const { setOrderFormSettingsData } = actions;

    const [value, setValue] = useState(
        getPropValue({ styling, id, target, style: 'sort', extra: '' }) || false
    );

    useEffect(() => {

        setValue(
            getPropValue({ styling, id, target, style: 'sort', extra: '' }) || false
        )

    }, [id])

    return (
        <div className="sort-option">
            <Checkbox
                checked={value}
                onChange={(e: any) => {
                    updateStyling({
                        setStyles,
                        styling,
                        id,
                        target,
                        toUpdate: {
                            sort: e.target.checked
                        }
                    });
                    setValue(e.target.checked)
                }}
            >
                Enable Sort
            </Checkbox>
        </div>
    )
}

const mapStateToProps = (store: any) => ({});

const mapDispatchToProps = (dispatch: Dispatch) => ({
    actions: bindActionCreators({
        setOrderFormSettingsData
    }, dispatch)
});

export default connect(mapStateToProps, mapDispatchToProps)(Sort);