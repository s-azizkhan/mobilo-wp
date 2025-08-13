import React from 'react'

import HeaderFooterSection from './HeaderFooterSection';
import TableSection from './TableSection';

// Redux
import { connect } from 'react-redux';

const readableStyles = (props: any) => {

    const { styles } = props;

    let stylesCopy: any = {};
    if (typeof styles === 'undefined') return;

    Object.keys(styles).map((style: any, index: any) => {

        switch (style) {
            case 'width':
                if (styles[style].type === 'percentage')
                    stylesCopy[style] = `${styles[style].value}%`;
                else if (styles[style].type === 'pixels')
                    stylesCopy[style] = `${styles[style].value}px`;
                break;
            case 'fontSize':
                if (styles[style].type === 'percentage')
                    stylesCopy[style] = `${styles[style].value}%`;
                else if (styles[style].type === 'pixels')
                    stylesCopy[style] = `${styles[style].value}px`;
                break;
            default:
                stylesCopy[style] = styles[style];
        }

    });

    return stylesCopy;

}

const OrderFormContent = (props: any) => {

    const { data } = props;

    // Loop through section Header, Table, Footer
    return (
        <div style={{
            display: "flex",
            flexDirection: 'column',
        }} className="editor-area">
            {
                Object.keys(data['editorArea']).map((sectionId: any, index: any) => {
                    const column = data['editorArea'][sectionId];
                    return (
                        <div
                            className={`sections ${sectionId}`}
                            style={{
                                margin: '10px',
                                position: 'relative'
                            }}
                            key={sectionId}>

                            <h4 style={{ marginTop: '10px' }}>{column.title}</h4>
                            {
                                sectionId !== 'formTable' ?
                                    <HeaderFooterSection sectionId={sectionId} readableStyles={readableStyles} />
                                    :
                                    <TableSection sectionId={sectionId} readableStyles={readableStyles} />
                            }
                        </div>
                    );
                })
            }
        </div>
    )
}

const mapStateToProps = (store: any) => ({
    data: store.dragAndDrop
});

export default connect(mapStateToProps)(OrderFormContent);