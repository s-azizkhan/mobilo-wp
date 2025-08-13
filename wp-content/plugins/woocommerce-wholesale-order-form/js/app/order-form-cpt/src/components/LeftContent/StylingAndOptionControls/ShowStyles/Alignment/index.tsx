import React, { useEffect, useState } from 'react';
import { Tooltip } from 'antd';
import { AlignLeftOutlined, AlignCenterOutlined, AlignRightOutlined } from '@ant-design/icons';

const Alignment = (props: any) => {

    const { styling, setStyles, id, target, updateStyling, getStyleValue } = props;
    const [alignment, setAlignment] = useState(
        getStyleValue({ styling, id, target, style: 'justifyContent', extra: '' }) || 'flex-start'
    )

    useEffect(() => {

        setAlignment(
            getStyleValue({ styling, id, target, style: 'justifyContent', extra: '' }) || 'flex-start'
        )

    }, [id])

    return (
        <div className="alignment">
            <label htmlFor="alignment">Element Alignment:</label>
            <Tooltip title="Left">
                <AlignLeftOutlined
                    style={{ color: alignment === 'flex-start' ? '#0071a1' : '' }}
                    onClick={() => {
                        updateStyling({
                            setStyles,
                            styling,
                            id,
                            target,
                            toUpdate: { justifyContent: 'flex-start' }
                        });
                        setAlignment('flex-start');
                    }}
                />
            </Tooltip>
            <Tooltip title="Center">
                <AlignCenterOutlined
                    style={{ color: alignment === 'center' ? '#0071a1' : '' }}
                    onClick={() => {
                        updateStyling({
                            setStyles,
                            styling,
                            id,
                            target,
                            toUpdate: { justifyContent: 'center' }
                        });
                        setAlignment('center');
                    }}
                />
            </Tooltip>
            <Tooltip title="Right">
                <AlignRightOutlined
                    style={{ color: alignment === 'flex-end' ? '#0071a1' : '' }}
                    onClick={() => {
                        updateStyling({
                            setStyles,
                            styling,
                            id,
                            target,
                            toUpdate: { justifyContent: 'flex-end' }
                        })
                        setAlignment('flex-end');
                    }}
                />
            </Tooltip>
        </div>
    )
}

export default Alignment;