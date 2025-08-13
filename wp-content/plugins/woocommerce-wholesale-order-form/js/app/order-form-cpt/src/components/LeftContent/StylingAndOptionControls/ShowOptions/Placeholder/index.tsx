import React, { useEffect, useState } from 'react';
import { Input } from 'antd';

const Placeholder = (props: any) => {

    const { styling, setStyles, id, target, updateStyling, getPropValue } = props;
    const [placeholderValue, setPlaceholderValue] = useState(
        getPropValue({ styling, id, target, style: 'placeholder', extra: '' }) || ''
    );

    useEffect(() => {

        setPlaceholderValue(
            getPropValue({ styling, id, target, style: 'placeholder', extra: '' }) || ''
        )

    }, [id])

    return (
        <div className="placeholder">
            <label htmlFor="placeholder">Placeholder:</label>
            <Input
                placeholder="Placeholder Text"
                value={placeholderValue ? placeholderValue : ''}
                onChange={(e) => {
                    const { value } = e.target;
                    updateStyling({
                        setStyles,
                        styling,
                        id,
                        target,
                        toUpdate: {
                            placeholder: value
                        }
                    });
                    setPlaceholderValue(value);
                }} />
        </div>
    );

}

export default Placeholder;