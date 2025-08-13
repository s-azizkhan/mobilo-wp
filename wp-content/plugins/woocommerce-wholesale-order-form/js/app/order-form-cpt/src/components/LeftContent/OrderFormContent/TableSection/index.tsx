import React, { useEffect } from 'react';
import { Droppable, Draggable } from "react-beautiful-dnd";
import ManageColumn from '../../ManageColumn';
import PrintItem from '../../PrintItem';

// Redux
import { bindActionCreators, Dispatch } from "redux";
import { connect } from 'react-redux';
import { stylingActions, productActions, dragAndDropActions } from '../../../../store/actions';

const { setShowStyling } = stylingActions;
const { fetchProducts, fetchCategories } = productActions;
const { setDndData } = dragAndDropActions;

const TableSection = (props: any) => {

    const { pagination, sectionId, data, styling, products, actions } = props;
    const { setShowStyling, fetchProducts, fetchCategories, setDndData } = actions;

    useEffect(() => {

        fetchCategories({
            categories: []
        });

    }, []);

    return (
        <Droppable
            direction={"horizontal"}
            droppableId={sectionId}
            key={sectionId}
        >
            {(provided, snapshot) => {
                return (
                    <div
                        className={`columns ${sectionId} ${sectionId}Container`}
                        {...provided.droppableProps}
                        ref={provided.innerRef}
                        style={{
                            border: '1px solid #eee',
                            background: snapshot.isDraggingOver
                                ? "lightblue"
                                : "#efefef",
                            padding: 4,
                            minHeight: data['editorArea'][sectionId]['itemIds'].length <= 0 ? '250px' : ''
                        }}
                    >
                        {data['editorArea'][sectionId]['itemIds'].map((itemId: any, index: any) => {

                            const colStyles = styling.styles[itemId] !== undefined ? styling.styles[itemId] : {};
                            const colId = data['editorArea'][sectionId]['colId'];
                            const item = data['items'][itemId];

                            if (typeof item === 'undefined')
                                return (<div key={index}></div>)
                            else
                                return (
                                    <Draggable
                                        key={item.id}
                                        draggableId={item.id}
                                        index={index}
                                    >
                                        {(provided, snapshot) => {

                                            const colProps = { colId, sectionId, item, data, setDndData, setShowStyling };

                                            return (
                                                <div
                                                    className={`table-item ${snapshot.isDragging ? 'is-dragging' : 'dropped'}`}
                                                    // {...provided.dragHandleProps}
                                                    ref={provided.innerRef}
                                                    {...provided.draggableProps}
                                                    style={{
                                                        padding: sectionId === 'formTable' ? '0px' : 8,
                                                        backgroundColor: snapshot.isDragging
                                                            ? "#263B4A"
                                                            : "#fff",
                                                        color: snapshot.isDragging ? "white" : '#000',
                                                        marginBottom: sectionId === 'formTable' ? '0px' : '5px',
                                                        flex: sectionId === 'formTable' ? '1' : 'none',
                                                        ...colStyles['element'],
                                                        ...provided.draggableProps.style,
                                                    }}
                                                >
                                                    <ManageColumn {...colProps} provided={provided} snapshot={snapshot} />
                                                    <PrintItem item={item} products={products} properties={colStyles['props']} />
                                                    {/* {printItemControl({ item, products })} */}
                                                </div>
                                            );
                                        }}
                                    </Draggable>
                                );
                        })}
                        {provided.placeholder}
                    </div>

                );
            }}
        </Droppable>)
}

const mapStateToProps = (store: any, props: any) => ({
    data: store.dragAndDrop,
    styling: store.styling,
    products: store.products,
    pagination: store.pagination
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
    actions: bindActionCreators({
        setShowStyling,
        fetchProducts,
        fetchCategories,
        setDndData
    }, dispatch)
});

export default connect(mapStateToProps, mapDispatchToProps)(TableSection);