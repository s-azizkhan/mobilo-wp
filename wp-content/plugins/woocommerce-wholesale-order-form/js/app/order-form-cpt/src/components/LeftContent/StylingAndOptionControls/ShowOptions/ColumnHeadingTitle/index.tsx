import React, { useEffect, useState } from 'react';
import { Input } from 'antd';


const ColumnHeadingTitle = (props: any) => {

    const { styling, setStyles, id, target, updateStyling, getPropValue } = props;
    const [columnTitle, setColumnTitle] = useState(
        getPropValue({ styling, id, target, style: 'columnHeading', extra: '' }) || ''
    );

    useEffect(() => {

        setColumnTitle(
            getPropValue({ styling, id, target, style: 'columnHeading', extra: '' }) || ''
        )

    }, [id])

    return (
        <div className="table-column-heading">
            <label htmlFor="table-column-heading">Column Heading:</label>
            <Input
                placeholder="Column Heading"
                value={columnTitle}
                onChange={(e) => {
                    const { value } = e.target;
                    updateStyling({
                        setStyles,
                        styling,
                        id,
                        target,
                        toUpdate: {
                            columnHeading: value
                        }
                    });
                    setColumnTitle(value);
                }} />
        </div>
    )
}

export default ColumnHeadingTitle;