import React from 'react';
import { dragAndDropItems } from '../../helpers/dragDropItems';
import PrintItem from './PrintItem';
import { readableStyles } from '../../helpers/readableStyles';

const DisplayItems = (props: any) => {

    const { dataRows, styles, products, orderFormId, fetchProducts } = props;
    const data: any = dragAndDropItems;

    if (dataRows === undefined)
        return (<></>);
    else
        return (
            <div
                style={{
                    // border: '1px solid #eee',
                    // background: "#efefef",
                    // padding: 10,
                    display: 'grid',
                    gridGap: '10px',
                }}
            >
                {Object.keys(dataRows['rows']).map((row: any, index: any) => {

                    const rowId = dataRows['rows'][row]['rowId'];
                    const rowStyles = styles[rowId] !== undefined ? styles[rowId] : {};
                    const columns = dataRows['rows'][row]['columns'];

                    return (
                        <div
                            key={index}
                            className={`row ${rowId}`}
                            style={{
                                ...readableStyles({ styles: rowStyles['box'] })
                            }}
                        >
                            <div
                                style={{
                                    display: 'flex',
                                    flexDirection: 'row',
                                }}
                            >
                                {Object.keys(columns).map((col: any, index: any) => {
                                    const colId = columns[col]['colId'];
                                    const colStyles = styles[colId] !== undefined ? styles[colId] : {};
                                    const items = dataRows['rows'][row]['columns'][col]['itemIds'];
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
                                            className={`col ${col}`}
                                            key={index}
                                            style={{
                                                flex: hasCustomWidth ? 'none' : 1,
                                                margin: '2px'
                                            }}
                                        >
                                            <div
                                                className={`drop-item ${items.length > 0 ? 'has-items' : 'no-item'}`}
                                                style={{
                                                    display: 'flex',
                                                    flexWrap: 'wrap',
                                                    flex: '1 1 0%',
                                                    ...readableColStyles
                                                }}
                                            >
                                                {items.map((itemKey: any, index: any) => {

                                                    const item = data['items'][itemKey];
                                                    const printItemProps = { item, styles: readableStyles({ styles: colStyles['element'] }), properties: colStyles['props'], products, orderFormId, fetchProducts }

                                                    return (
                                                        <div
                                                            key={index}
                                                            className={`col ${col} item`}
                                                            style={{
                                                                display: 'flex',
                                                                ...readableColStyles,
                                                                width: '100%',
                                                                height: '100%',
                                                            }}
                                                        >
                                                            <PrintItem {...printItemProps} />
                                                        </div>
                                                    );

                                                })}
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>

                        </div>
                    )
                })}
            </div >
        );
}

export default DisplayItems;