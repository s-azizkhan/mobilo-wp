import React from 'react';
import { Droppable, Draggable } from 'react-beautiful-dnd';
import AddNewRow from './AddNewRow';
import ManageColumn from '../../ManageColumn';
import ManageRow from '../../ManageRow';
import PrintItem from '../../PrintItem';

// Redux
import { bindActionCreators, Dispatch } from "redux";
import { connect } from 'react-redux';
import { stylingActions, dragAndDropActions } from '../../../../store/actions';

const { setDndData } = dragAndDropActions;
const { setShowStyling, setStyles } = stylingActions;

const HeaderFooterSection = (props: any) => {

    const { data, sectionId, actions, styling, products, readableStyles, pagination, orderForm } = props;
    const { setShowStyling, setStyles, setDndData } = actions;
    const dataRows = data['editorArea'][sectionId];

    // If there are no elements inside header and footer show "Add New Section" option
    if (dataRows['rows'].length === 0)
        return <AddNewRow sectionId={sectionId} hasRows={false} />
    else
        return (
            <Droppable
                droppableId={sectionId}
                // direction="horizontal"
                type={sectionId}
            >
                {(provided) => (
                    <div
                        className={`formHeaderFooterContainer`}
                        {...provided.droppableProps}
                        ref={provided.innerRef}
                        style={{
                            border: '1px solid #eee',
                            background: "#efefef",
                            padding: 10,
                            display: 'grid',
                            gridGap: '10px',
                        }}
                    >
                        {Object.keys(dataRows['rows']).map((row: any, index: any) => {
                            const rowId = dataRows['rows'][row]['rowId'];
                            const rowStyles = styling.styles[rowId] !== undefined ? styling.styles[rowId] : {};
                            const columns = dataRows['rows'][row]['columns'];

                            const rowProps = { sectionId, row, data, setData: setDndData, setShowStyling, styling, setStyles };

                            return (
                                <Draggable
                                    draggableId={`${sectionId}-${row}`}
                                    index={index}
                                    key={`${sectionId}-${row}`}
                                >
                                    {(provided, snapshot) => (
                                        <div
                                            {...provided.draggableProps}
                                            ref={provided.innerRef}
                                            key={index}
                                            className={`row ${rowId}`}
                                            style={{
                                                border: '1px solid #fff',
                                                minHeight: '40px',
                                                backgroundColor: snapshot.isDragging
                                                    ? "#263B4A"
                                                    : "inherit",
                                                color: snapshot.isDragging ? "white" : '#000',
                                                ...readableStyles({ styles: rowStyles['box'] }),
                                                ...provided.draggableProps.style
                                            }}
                                        >
                                            <ManageRow {...rowProps} provided={provided} snapshot={snapshot} />

                                            <Droppable
                                                droppableId={`${sectionId}-${row}`}
                                                direction="horizontal"
                                                type={`${sectionId}-${row}`}
                                            >
                                                {(provided, snapshot) => (
                                                    <div
                                                        {...provided.droppableProps}
                                                        ref={provided.innerRef}
                                                        style={{
                                                            display: 'flex',
                                                            flexDirection: 'row',
                                                        }}
                                                    >
                                                        {Object.keys(columns).map((col: any, index: any) => {
                                                            const colId = columns[col]['colId'];
                                                            const colStyles = styling.styles[colId] !== undefined ? styling.styles[colId] : {};
                                                            const items = dataRows['rows'][row]['columns'][col]['itemIds'];

                                                            return (
                                                                <Draggable
                                                                    draggableId={`${sectionId}-${row}-${col}`}
                                                                    index={index}
                                                                    key={`${sectionId}-${row}-${col}`}
                                                                >
                                                                    {(provided, snapshot) => {

                                                                        const itemProvided = provided;
                                                                        const itemSnapshot = snapshot;

                                                                        const readableColStyles = readableStyles({ styles: colStyles['box'] });
                                                                        let hasCustomWidth = false;

                                                                        if (readableColStyles && typeof readableColStyles['width'] !== 'undefined') {
                                                                            if (readableColStyles['width'].indexOf('px') >= 0) {
                                                                                const val = parseInt(readableColStyles['width']);
                                                                                if (val)
                                                                                    hasCustomWidth = true;
                                                                            }
                                                                        }

                                                                        return (
                                                                            <div
                                                                                {...provided.draggableProps}
                                                                                className={`col ${col}`}
                                                                                ref={provided.innerRef}
                                                                                key={index}
                                                                                style={{
                                                                                    minHeight: '40px',
                                                                                    flex: hasCustomWidth ? 'none' : 1,
                                                                                    // margin: '5px',
                                                                                    padding: '4px',
                                                                                    backgroundColor: snapshot.isDragging
                                                                                        ? "#263B4A"
                                                                                        : "inherit",
                                                                                    color: snapshot.isDragging ? "white" : '#000',
                                                                                    ...provided.draggableProps.style
                                                                                }}
                                                                            >
                                                                                <span style={{ display: 'none' }} {...provided.dragHandleProps}></span>
                                                                                <Droppable
                                                                                    droppableId={`${sectionId}-${row}-${col}-item`}
                                                                                    // direction="horizontal"
                                                                                    // type={`${row}${col}`}
                                                                                    type={sectionId !== 'formTable' ? "HEADER-FOOTER" : "DEFAULT"}
                                                                                    isDropDisabled={items.length > 0 ? true : false}
                                                                                >
                                                                                    {(provided, snapshot) => (
                                                                                        <div
                                                                                            className={`drop-item ${items.length > 0 ? 'has-items' : 'no-item'}`}
                                                                                            {...provided.droppableProps}
                                                                                            ref={provided.innerRef}
                                                                                            style={{
                                                                                                border: '1px solid #fff',
                                                                                                background: snapshot.isDraggingOver
                                                                                                    ? "lightblue"
                                                                                                    : "inherit",
                                                                                                ...readableStyles({ styles: colStyles['box'] })
                                                                                            }}
                                                                                        >
                                                                                            {!snapshot.isDraggingOver && items.length === 0 ? 'Drop Item Here' : ''}
                                                                                            {items.map((itemKey: any, index: any) => {

                                                                                                const item = data['items'][itemKey];

                                                                                                return (
                                                                                                    <Draggable
                                                                                                        draggableId={`${sectionId}-${row}-${col}-item`}
                                                                                                        index={index}
                                                                                                        key={`${sectionId}-${row}-${col}-item`}
                                                                                                        isDragDisabled={true}
                                                                                                    >
                                                                                                        {(provided, snapshot) => {

                                                                                                            const colProps = { setStyles, styling, colId, sectionId, item, data, setDndData, area: `${sectionId}-${row}-${col}`, setShowStyling }
                                                                                                            const printItemProps = { item, styles: readableStyles({ styles: colStyles['element'] }), properties: colStyles['props'], products, pagination, orderForm }

                                                                                                            return (
                                                                                                                <>
                                                                                                                    <ManageColumn {...colProps} provided={itemProvided} snapshot={itemSnapshot} />
                                                                                                                    <div
                                                                                                                        {...provided.dragHandleProps}
                                                                                                                        {...provided.draggableProps}
                                                                                                                        className={`col ${col} item`}
                                                                                                                        ref={provided.innerRef}
                                                                                                                        style={{
                                                                                                                            backgroundColor: snapshot.isDragging
                                                                                                                                ? "#263B4A"
                                                                                                                                : "inherit",
                                                                                                                            color: snapshot.isDragging ? "white" : '#000',
                                                                                                                            ...readableStyles({ styles: colStyles['box'] }),
                                                                                                                            width: '100%',
                                                                                                                            height: '100%',
                                                                                                                            display: 'flex',
                                                                                                                            ...provided.draggableProps.style,
                                                                                                                        }}
                                                                                                                    >
                                                                                                                        <PrintItem {...printItemProps} />
                                                                                                                    </div>
                                                                                                                </>
                                                                                                            )
                                                                                                        }}
                                                                                                    </Draggable>
                                                                                                )
                                                                                            })}
                                                                                            {provided.placeholder}
                                                                                        </div>
                                                                                    )}
                                                                                </Droppable>
                                                                            </div>
                                                                        )
                                                                    }}
                                                                </Draggable>
                                                            )
                                                        })}
                                                        {provided.placeholder}
                                                    </div>
                                                )
                                                }
                                            </Droppable>

                                        </div>
                                    )
                                    }
                                </Draggable>

                            )
                        })}
                        <AddNewRow sectionId={sectionId} hasRows={true} />
                        {provided.placeholder}
                    </div>
                )
                }
            </Droppable >
        )
}

const mapStateToProps = (store: any, props: any) => ({
    orderForm: store.orderForm,
    data: store.dragAndDrop,
    styling: store.styling,
    products: store.products,
    pagination: store.pagination
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
    actions: bindActionCreators({
        setShowStyling,
        setStyles,
        setDndData
    }, dispatch)
});

export default connect(mapStateToProps, mapDispatchToProps)(HeaderFooterSection);