import React, { useEffect, useState } from 'react';
import { Checkbox } from 'antd';

// Redux
import { bindActionCreators, Dispatch } from "redux";
import { connect } from 'react-redux';

import { orderFormActions } from '../../../../../store/actions';

const { setOrderFormSettingsData } = orderFormActions;

const ShowPopUp = (props: any) => {

    const { styling, setStyles, id, target, updateStyling, getPropValue, actions } = props;
    const { setOrderFormSettingsData } = actions;

    const [value, setValue] = useState(
        getPropValue({ styling, id, target, style: 'showPopup', extra: '' }) || false
    );

    useEffect(() => {

        setValue(
            getPropValue({ styling, id, target, style: 'showPopup', extra: '' }) || false
        )

    }, [id])

    return (
        <div className="show-popup">
            <Checkbox
                checked={value}
                onChange={(e: any) => {
                    updateStyling({
                        setStyles,
                        styling,
                        id,
                        target,
                        toUpdate: {
                            showPopup: e.target.checked
                        }
                    });
                    setValue(e.target.checked)
                }}
            >
                Show Popup On Click
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

export default connect(mapStateToProps, mapDispatchToProps)(ShowPopUp);