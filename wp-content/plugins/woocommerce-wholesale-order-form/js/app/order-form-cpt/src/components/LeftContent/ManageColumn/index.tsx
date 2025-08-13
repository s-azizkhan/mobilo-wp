import React from 'react';
import { ControlOutlined, DeleteOutlined, DragOutlined } from '@ant-design/icons';
import { defaults } from '../../../store/reducers/dragAndDropReducer';

// Handles Deleting Item in Row Columns
const ManageColumn = (props: any) => {

    const { sectionId, item, data, setDndData, area, setShowStyling, styling, setStyles, provided, snapshot } = props;
    const dndData: any = defaults;

    const getColId = (column: string) => {

        if (area !== undefined) {
            let destElement = area.split('-');

            // deleting from formHeader and footerFooter
            if (column !== 'formTable')
                return data['editorArea'][column]['rows'][destElement[1]]['columns'][destElement[2]]['colId'];
            return null;
        } else
            return data['editorArea'][column]['id'];

    }


    const findOriginalLocation = (id: number) => {

        for (let column of Object.keys(dndData['formElements'])) {
            const index = dndData['formElements'][column]['itemIds'].indexOf(id);
            if (index >= 0) return { index, column };
        }
        return null;
    }

    const deletingItem = (id: number, column: string) => {

        let itemIds: any = [];
        let rowId: number = 0;
        let colId: number = 0;
        let destElement: any[] = [];

        if (area !== undefined) {
            destElement = area.split('-');
            rowId = parseInt(destElement[1]);
            colId = parseInt(destElement[2]);

            // deleting from formHeader and footerFooter
            if (column !== 'formTable')
                itemIds = data['editorArea'][column]['rows'][destElement[1]]['columns'][destElement[2]]['itemIds'];
        } else
            itemIds = data['editorArea'][column]['itemIds']; // deleting from formTable

        const index = itemIds.indexOf(id);

        if (index >= 0) {
            const originalLocation: any = findOriginalLocation(id);

            // Remove from Editor
            itemIds.splice(index, 1);

            // Return to Form Elements
            const dest = data['formElements'][originalLocation.column]['itemIds'];
            dest.splice(originalLocation.index, 0, id);

            if (column !== 'formTable') {
                // Handle updating 2x nested array of objects
                let updated = data['editorArea'][destElement[0]]['rows'].map((rows: any, rowIndex: number) => {
                    if (rowIndex === rowId) {
                        const cols = rows['columns'].map((col: any, colIndex: number) => {
                            if (colIndex === colId) {
                                return {
                                    ...col,
                                    itemIds
                                }
                            }
                            return col;
                        });
                        return {
                            ...rows,
                            columns: cols
                        }
                    }
                    return rows;
                });

                setDndData({
                    ...data,
                    editorArea: {
                        ...data['editorArea'],
                        [destElement[0]]: {
                            ...data['editorArea'][destElement[0]],
                            rows: updated
                        }
                    },
                    'formElements': {
                        ...data['formElements'],
                        [originalLocation.column]: {
                            ...data['formElements'][originalLocation.column],
                            itemIds: dest
                        }
                    }
                });
            } else {
                setDndData({
                    ...data,
                    'editorArea': {
                        ...data['editorArea'],
                        [column]: {
                            ...data['editorArea'][column],
                            itemIds
                        }
                    },
                    'formElements': {
                        ...data['formElements'],
                        [originalLocation.column]: {
                            ...data['formElements'][originalLocation.column],
                            itemIds: dest
                        }
                    }
                });
            }
        }

        // Remove styles
        const colIdToDelete = getColId(column)

        if (styling !== undefined && styling.hasOwnProperty('styles') > 0 && styling.styles[colIdToDelete] !== undefined) {

            const allStyles = styling.styles;
            delete allStyles[colIdToDelete];

            setStyles({
                ...styling,
                styles: allStyles
            })

        }

    }

    return (
        <div className={`item-controls${snapshot.isDragging ? ' dragging' : ''}`} >
            <DeleteOutlined
                onClick={(e: any) => {
                    setShowStyling({ show: false });
                    deletingItem(item.id, sectionId);
                }} />
            <ControlOutlined
                onClick={() =>
                    setShowStyling({
                        show: true,
                        type: 'ITEM',
                        id: sectionId === 'formTable' ? item.id : getColId(sectionId),
                        itemId: item.id,
                        section: sectionId
                    })
                } />
            <DragOutlined {...provided.dragHandleProps} />
        </div>
    );

}

export default ManageColumn;