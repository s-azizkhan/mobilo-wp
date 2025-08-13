import React, { useState, useEffect } from 'react';
import { InputNumber, Radio } from 'antd';

const Width = (props: any) => {

    const { styling, setStyles, id, target, updateStyling, getStyleValue } = props;

    const [type, setType] = useState(
        getStyleValue({ styling, id, target, style: 'width', extra: 'type' }) || 'percentage'
    );

    const [width, setWidth] = useState(
        getStyleValue({ styling, id, target, style: 'width', extra: 'value' }) || 100
    );

    useEffect(() => {

        setWidth(
            getStyleValue({ styling, id, target, style: 'width', extra: 'value' }) || 100
        )

        setType(
            getStyleValue({ styling, id, target, style: 'width', extra: 'type' }) || 'percentage'
        )

    }, [styling.item.id]);

    return (
        <div className="width">
            <label htmlFor="width">{target === 'box' ? 'Box Width' : 'Element Width'}:</label>
            <InputNumber
                style={{ width: 100 }}
                value={width}
                onChange={(value: any) => {
                    updateStyling({
                        setStyles,
                        styling,
                        id,
                        target,
                        toUpdate: {
                            width: {
                                value,
                                type
                            }
                        }
                    });
                    setWidth(value);
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
                            width: {
                                value: width,
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
        </div>
    )
}

export default Width;