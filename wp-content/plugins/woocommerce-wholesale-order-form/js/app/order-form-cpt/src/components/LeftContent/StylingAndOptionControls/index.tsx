import React, { useState, useEffect } from 'react'
import { Drawer, Collapse } from 'antd';
import ShowStyles from './ShowStyles';
import ShowOptions from './ShowOptions';

// Redux
import { bindActionCreators, Dispatch } from "redux";
import { connect } from 'react-redux';

// Actions
import { stylingActions } from '../../../store/actions';
import { defaults } from '../../../store/reducers/dragAndDropReducer';

const { setShowStyling, setStyles } = stylingActions;

const { Panel } = Collapse;

const StylingAndOptionControls = (props: any) => {

    const { styling, actions } = props;
    const { setShowStyling, setStyles } = actions;
    const [itemName, setItemName] = useState('');

    const styleProps = { styling, setStyles }
    const defaultItems: any = defaults;

    useEffect(() => {

        if (
            styling.show === true &&
            typeof styling.item.itemId !== 'undefined' &&
            styling.item.itemId !== 'none'
        ) {
            setItemName(defaultItems.items[styling.item.itemId].content);
        } else {
            setItemName('');
        }

    }, [styling.item])


    return (
        <Drawer
            title="Styling and Options"
            placement="right"
            closable={true}
            onClose={() => setShowStyling({ show: false })}
            visible={styling.show}
            mask={false}
            width={400}
        >
            <div className="stylings">
                <h3>{itemName}</h3>

                {styling.item.section !== 'formTable' ?
                    <Collapse defaultActiveKey={['1']}  >
                        <Panel header="Styles" key="1">
                            <ShowStyles {...styleProps} />
                        </Panel>
                        <Panel header="Options" key="2">
                            <ShowOptions {...styleProps} />
                        </Panel>
                    </Collapse> :
                    <Collapse defaultActiveKey={['1']}  >
                        <Panel header="Options" key="1">
                            <ShowOptions {...styleProps} />
                        </Panel>
                    </Collapse>}
            </div>
        </Drawer>

    )
}

const mapStateToProps = (store: any, props: any) => ({
    styling: store.styling
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
    actions: bindActionCreators({
        setShowStyling,
        setStyles
    }, dispatch)
});

export default connect(mapStateToProps, mapDispatchToProps)(StylingAndOptionControls);