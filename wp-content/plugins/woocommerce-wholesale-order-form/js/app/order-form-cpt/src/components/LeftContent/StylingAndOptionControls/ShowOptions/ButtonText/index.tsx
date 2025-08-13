import React, { useEffect, useState } from 'react';
import { Input } from 'antd';


const ButtonText = (props: any) => {

    const { styling, setStyles, id, target, updateStyling, getPropValue } = props;
    const [buttonTextValue, setButtonTextValue] = useState(
        getPropValue({ styling, id, target, style: 'buttonText', extra: '' }) || ''
    );

    useEffect(() => {

        setButtonTextValue(
            getPropValue({ styling, id, target, style: 'buttonText', extra: '' }) || ''
        )

    }, [id])

    return (
        <div className="button-text">
            <label htmlFor="button-text">Button Text:</label>
            <Input
                placeholder="Button Text"
                value={buttonTextValue ? buttonTextValue : ''}
                onChange={(e) => {
                    const { value } = e.target;
                    updateStyling({
                        setStyles,
                        styling,
                        id,
                        target,
                        toUpdate: {
                            buttonText: value
                        }
                    });
                    setButtonTextValue(value);
                }} />
        </div>
    );

}

export default ButtonText;