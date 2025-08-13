import React, { useState } from 'react'
import { Popover, Button } from 'antd';
import { PlusSquareOutlined } from '@ant-design/icons';

// Redux
import { bindActionCreators, Dispatch } from "redux";
import { connect } from 'react-redux';
import { dragAndDropActions } from '../../../../store/actions';
const { setDndData } = dragAndDropActions;

// Generate random id for row and col
const createID = () => {
    return Array(16)
        .fill(0)
        .map(() => String.fromCharCode(Math.floor(Math.random() * 26) + 97))
        .join('') +
        Date.now().toString(24);
}

const AddNewRow = (props: any) => {

    const { data, sectionId, hasRows, actions } = props;
    const [visible, setVisible] = useState(false);

    const setNewSection = (cols: any) => {

        const newCols = Array.from(Array(cols)).map((x, i) => {

            return {
                colId: createID(),
                itemIds: []
            };
        });

        const newData = [{
            rowId: createID(),
            columns: newCols
        }];

        const newRow = [
            ...data['editorArea'][sectionId]['rows'],
            ...newData
        ];

        actions.setDndData({
            ...data,
            editorArea: {
                ...data['editorArea'],
                [sectionId]: {
                    ...data['editorArea'][sectionId],
                    rows: newRow
                }
            }
        });

    }

    const SectionSetup = (
        <div className="Setup">
            <h4>1 Column</h4>
            <div
                className="Columns OneColumn"
                onClick={
                    () => { setVisible(false); setNewSection(1); }
                }
            >
                <div />
            </div>
            <h4>2 Column</h4>
            <div
                className="Columns TwoColumn"
                onClick={
                    () => { setVisible(false); setNewSection(2); }
                }>
                <div /><div />
            </div>
            <h4>3 Column</h4>
            <div
                className="Columns ThreeColumn"
                onClick={
                    () => { setVisible(false); setNewSection(3); }
                }>
                <div /><div /><div />
            </div>
            <h4>4 Column</h4>
            <div
                className="Columns ThreeColumn"
                onClick={
                    () => { setVisible(false); setNewSection(4); }
                }>
                <div /><div /><div /><div />
            </div>
        </div>);

    return (
        <>
            {
                hasRows ?
                    <Popover
                        trigger="click"
                        content={SectionSetup}
                        title="Select Your Structure"
                        visible={visible}
                        onVisibleChange={(visible) => setVisible(visible)}
                    >
                        <Button className="add-new-section"><PlusSquareOutlined />Add New Section</Button>
                    </Popover>
                    :
                    <div
                        style={{
                            border: '1px solid #eee',
                            background: "#efefef",
                            padding: 10,
                            minHeight: '150px',
                            display: 'flex',
                            justifyContent: 'center',
                            alignItems: 'center'
                        }}
                    >
                        <Popover
                            trigger="click"
                            content={SectionSetup}
                            title="Select Your Structure"
                            visible={visible}
                            onVisibleChange={(visible) => setVisible(visible)}
                        >
                            <Button> <PlusSquareOutlined />Add New Section</Button>

                        </Popover>

                    </div>

            }
        </>
    )
}


const mapStateToProps = (store: any, props: any) => ({
    data: store.dragAndDrop
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
    actions: bindActionCreators({
        setDndData
    }, dispatch)
});

export default connect(mapStateToProps, mapDispatchToProps)(AddNewRow);