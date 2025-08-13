import React from 'react';
import { ControlOutlined, DeleteOutlined, DragOutlined } from '@ant-design/icons';
import { defaults } from '../../../store/reducers/dragAndDropReducer';

// Handles Deleting Rows
const ManageRow = (props: any) => {

    const { sectionId, row, data, setData, setShowStyling, styling, setStyles, provided, snapshot } = props;
    const rowId = data['editorArea'][sectionId]['rows'][row]['rowId'];
    const dndData: any = defaults;

    const findOriginalLocation = (id: number) => {

        for (let column of Object.keys(dndData['formElements'])) {

            const index = dndData['formElements'][column]['itemIds'].indexOf(id);
            if (index >= 0) return { index, column };
        }
        return null;
    }

    const deletingRow = () => {
        // console.log(props)
        const rows = data['editorArea'][sectionId]['rows'];

        // Return items into right side draggable items
        // let moved: any = {};
        const colsData = rows[row]['columns'];

        Object.keys(colsData).map((d: any, i: number) => {
            const items = colsData[i]['itemIds'];
            items.map((item: any, ii: number) => {
                const originalLocation: any = findOriginalLocation(item);
                const dest = data['formElements'][originalLocation.column]['itemIds'];
                dest.splice(originalLocation.index, 0, item);
                // moved = {
                //     ...moved,
                //     [originalLocation.column]: {
                //         ...data['formElements'][originalLocation.column],
                //         itemIds: dest
                //     }
                // }
            })
        });
        // console.log(moved)

        rows.splice(row, 1);

        setData({
            ...data,
            editorArea: {
                ...data['editorArea'],
                [sectionId]: {
                    ...data['editorArea'][sectionId],
                    rows
                }
            },
            // 'formElements': {
            //     ...data['formElements'],
            //     [originalLocation.column]: {
            //         ...data['formElements'][originalLocation.column],
            //         itemIds: dest
            //     }
            // }
        });

        // Remove styles
        if (styling.styles[rowId] !== undefined) {

            const allStyles = styling.styles;
            delete allStyles[rowId];

            setStyles({
                ...styling,
                styles: allStyles
            })

        }
    }

    return (
        <div
            className={`row-controls${snapshot.isDragging ? ' dragging' : ''}`} >
            <DeleteOutlined
                onClick={(e: any) => {
                    setShowStyling({ show: false });
                    deletingRow();
                }}
            />
            <ControlOutlined
                onClick={() =>
                    setShowStyling({
                        show: true,
                        type: 'ROW',
                        id: rowId,
                        itemId: 'none',
                        section: sectionId
                    })
                }
            />
            <DragOutlined {...provided.dragHandleProps} />
        </div>
    );

}

export default ManageRow;