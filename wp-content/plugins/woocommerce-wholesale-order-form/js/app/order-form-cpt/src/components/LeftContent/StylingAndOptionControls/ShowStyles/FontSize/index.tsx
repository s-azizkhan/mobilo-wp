import React, { useEffect, useState } from 'react';
import { InputNumber, Radio } from 'antd';

const FontSize = (props: any) => {

    const { styling, setStyles, id, target, updateStyling, getStyleValue } = props;
    const [type, setType] = useState(
        getStyleValue({ styling, id, target, style: 'fontSize', extra: 'type' }) || 'percentage'
    );

    const [fontSize, setFontSize] = useState(
        getStyleValue({ styling, id, target, style: 'fontSize', extra: 'value' }) || ''
    );

    useEffect(() => {

        setType(
            getStyleValue({ styling, id, target, style: 'fontSize', extra: 'type' }) || 'percentage'
        )

        setFontSize(
            getStyleValue({ styling, id, target, style: 'fontSize', extra: 'value' }) || ''
        )

    }, [id])

    return (
        <div className="font-size">
            <label htmlFor="font-size">Font Size:</label>
            <InputNumber
                style={{ width: 100 }}
                min={1}
                defaultValue={1}
                value={fontSize}
                onChange={(value: any) => {
                    updateStyling({
                        setStyles,
                        styling,
                        id,
                        target,
                        toUpdate: {
                            fontSize: {
                                value,
                                type
                            }
                        }
                    });
                    setFontSize(value);
                }}
            />
            <Radio.Group
                onChange={(e) => {
                    updateStyling({
                        setStyles,
                        styling,
                        id,
                        target,
                        toUpdate: {
                            fontSize: {
                                value: fontSize,
                                type: e.target.value
                            }
                        }
                    });
                    setType(e.target.value);
                }}
                value={type}
                style={{ display: 'inline-flex' }}
            >
                <Radio value="percentage">%</Radio>
                <Radio value="pixels">px</Radio>
            </Radio.Group>
        </div >
    )
}

export default FontSize;