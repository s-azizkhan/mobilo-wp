
import React, { useEffect } from 'react';

import { bindActionCreators, Dispatch } from "redux";
import { connect } from 'react-redux';

import { getPropValue } from '../../../../helpers/getPropValue';
import { updateStyling } from '../../../../helpers/updateStyling';

import { Input } from 'antd';

// Actions
import { stylingActions } from '../../../../store/actions';
import { findItemColumnId } from '../../../../helpers/findItemColumnId';

const { setStyles } = stylingActions;

const SearchInput = (props: any) => {

    const { styling, actions, style, placeholder, dragAndDrop } = props;
    const { setStyles } = actions;

    useEffect(() => {

        try {

            const columnId = findItemColumnId(dragAndDrop, 'search-input');

            const check = getPropValue({ styling, id: columnId, target: 'props', style: 'submitOnEnter', extra: '' });

            if (check === null) {
                updateStyling({
                    setStyles,
                    styling,
                    id: columnId,
                    target: 'props',
                    toUpdate: {
                        submitOnEnter: true
                    }
                });
            }

        } catch (error) {
            console.log(error)
        }

    }, []);

    return (
        <Input
            placeholder={placeholder}
            style={{ ...style }}
        />
    );

}

const mapStateToProps = (store: any) => ({
    styling: store.styling,
    dragAndDrop: store.dragAndDrop
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
    actions: bindActionCreators({
        setStyles
    }, dispatch)
});

export default connect(mapStateToProps, mapDispatchToProps)(SearchInput);
