import React, { useState } from 'react';
import { Tooltip, InputNumber } from 'antd';
import { LinkOutlined, DisconnectOutlined } from '@ant-design/icons';

const Padding = (props: any) => {

    const { styling, setStyles, id, target, updateStyling } = props;
    const [linkPadding, setLinkPadding] = useState(true);

    const paddings: any = { Top: 'paddingTop', Right: 'paddingRight', Bottom: 'paddingBottom', Left: 'paddingLeft' };

    const inputNumbers = Object.keys(paddings).map((key: any) => {

        let padding = 0
        if (
            typeof styling.styles[id] !== 'undefined' &&
            typeof styling.styles[id][target] !== 'undefined' &&
            styling.styles[id][target][paddings[key]] > 0
        )
            padding = styling.styles[id][target][paddings[key]];

        return <InputNumber
            placeholder={key}
            key={key}
            onChange={(val: any) =>
                linkPadding ?
                    updateStyling({
                        setStyles,
                        styling,
                        id,
                        target,
                        toUpdate: {
                            paddingTop: val,
                            paddingRight: val,
                            paddingBottom: val,
                            paddingLeft: val
                        }
                    })
                    :
                    updateStyling({
                        setStyles,
                        styling,
                        id,
                        target,
                        toUpdate: {
                            [paddings[key]]: val
                        }
                    })
            }
            value={padding}
        />
    })

    return (
        <div className="padding">
            <label htmlFor="padding">Padding:</label>
            {inputNumbers}
            {
                linkPadding ?
                    <Tooltip title="Linked">
                        <LinkOutlined
                            onClick={() => {
                                setLinkPadding(false)
                            }}
                            style={{ color: '#0071a1' }}
                        />
                    </Tooltip> :
                    <Tooltip title="Not Linked">
                        <DisconnectOutlined
                            onClick={() => {
                                setLinkPadding(true)
                                updateStyling({
                                    setStyles,
                                    styling,
                                    id,
                                    target,
                                    toUpdate: {
                                        paddingTop: styling.styles[id][target]['paddingTop'],
                                        paddingRight: styling.styles[id][target]['paddingTop'],
                                        paddingBottom: styling.styles[id][target]['paddingTop'],
                                        paddingLeft: styling.styles[id][target]['paddingTop']
                                    }
                                })
                            }}
                        />
                    </Tooltip>
            }
        </div>
    )
}

export default Padding;