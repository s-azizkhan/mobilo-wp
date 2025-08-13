import React, { useState } from 'react';
import { Tooltip, InputNumber } from 'antd';
import { LinkOutlined, DisconnectOutlined } from '@ant-design/icons';

const Margin = (props: any) => {

    const { styling, setStyles, id, target, updateStyling } = props;
    const [linkMargin, setLinkMargin] = useState(true);

    const margins: any = { Top: 'marginTop', Right: 'marginRight', Bottom: 'marginBottom', Left: 'marginLeft' };

    const inputNumbers = Object.keys(margins).map((key: any) => {

        let margin = 0;
        if (
            typeof styling.styles[id] !== 'undefined' &&
            typeof styling.styles[id][target] !== 'undefined' &&
            styling.styles[id][target][margins[key]] > 0
        )
            margin = styling.styles[id][target][margins[key]];

        return <InputNumber
            placeholder={key}
            key={key}
            onChange={(val: any) =>
                linkMargin ?
                    updateStyling({
                        setStyles,
                        styling,
                        id,
                        target,
                        toUpdate: {
                            marginTop: val,
                            marginRight: val,
                            marginBottom: val,
                            marginLeft: val
                        }
                    })
                    :
                    updateStyling({
                        setStyles,
                        styling,
                        id,
                        target,
                        toUpdate: {
                            [margins[key]]: val
                        }
                    })
            }
            value={margin}
        />
    })

    return (
        <div className="margin">
            <label htmlFor="margin">Margin:</label>
            {inputNumbers}
            {
                linkMargin ?
                    <Tooltip title="Linked">
                        <LinkOutlined
                            onClick={() => {
                                setLinkMargin(false)
                            }}
                            style={{ color: '#0071a1' }}
                        />
                    </Tooltip> :
                    <Tooltip title="Not Linked">
                        <DisconnectOutlined
                            onClick={() => {
                                setLinkMargin(true)
                                updateStyling({
                                    setStyles,
                                    styling,
                                    id,
                                    target,
                                    toUpdate: {
                                        marginTop: styling.styles[id][target]['marginTop'],
                                        marginRight: styling.styles[id][target]['marginTop'],
                                        marginBottom: styling.styles[id][target]['marginTop'],
                                        marginLeft: styling.styles[id][target]['marginTop']
                                    }
                                })
                            }}
                        />
                    </Tooltip>
            }
        </div>
    )
}

export default Margin;