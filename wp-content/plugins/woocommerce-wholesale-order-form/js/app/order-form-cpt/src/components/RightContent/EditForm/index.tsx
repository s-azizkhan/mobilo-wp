import React from 'react';
import { Droppable, Draggable } from 'react-beautiful-dnd';
import Element from './Element';

const EditForm = (props: any) => {

    const { data } = props;

    return (
        <>
            <h3 style={{ fontWeight: 'bolder' }}>Order Form Elements</h3>
            <p>Click and drag the elements you want on your order form into position.</p>
            <div className="form-elements">
                {Object.keys(data['formElements']).map((columnId: any, index: any) => {
                    const column = data['formElements'][columnId];

                    // Future Feature, we will hide for now
                    if (columnId === 'wooWidgets')
                        return (<div key={index}></div>)
                    else
                        return (
                            <div
                                style={{
                                    display: "flex",
                                    flexWrap: 'wrap'
                                }}
                                key={columnId}>
                                <h4 style={{ marginTop: '10px' }}>{column.title}</h4>
                                <p>{column.desc}</p>
                                <div style={{
                                    marginTop: 8,
                                    width: '100%'
                                }}>
                                    <Droppable
                                        // direction="horizontal"
                                        droppableId={columnId}
                                        key={index}
                                        type={columnId !== 'tableElements' ? "HEADER-FOOTER" : "DEFAULT"}
                                    >
                                        {(provided, snapshot) => {

                                            return (
                                                <div
                                                    className={`draggable-items ${columnId}`}
                                                    {...provided.droppableProps}
                                                    ref={provided.innerRef}
                                                    style={{
                                                        display: 'flex',
                                                        flexWrap: 'wrap',
                                                    }}
                                                >

                                                    {column['itemIds'].map((itemId: any, index: any) => {
                                                        const item = data['items'][itemId];

                                                        if (typeof item === 'undefined')
                                                            return (<div key={index}></div>);
                                                        else
                                                            return (
                                                                <Draggable
                                                                    key={itemId}
                                                                    draggableId={itemId}
                                                                    index={index}
                                                                >
                                                                    {(provided, snapshot) => {

                                                                        return (
                                                                            <div
                                                                                ref={provided.innerRef}
                                                                                {...provided.draggableProps}
                                                                                {...provided.dragHandleProps}
                                                                                style={{

                                                                                    textAlign: "center",
                                                                                    marginBottom: '10px',
                                                                                    backgroundColor: snapshot.isDragging ? "#0071a1" : "#fff",
                                                                                    color: snapshot.isDragging ? "#fff" : "#525252",
                                                                                    border: "1px solid #525252",
                                                                                    padding: "6px 16px",
                                                                                    marginRight: '10px',
                                                                                    borderRadius: '2px',
                                                                                    ...provided.draggableProps.style
                                                                                }}
                                                                            >
                                                                                <Element item={item} />
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
                                    </Droppable>
                                </div>
                            </div>
                        );
                })}
            </div>
        </>)
}

export default EditForm;