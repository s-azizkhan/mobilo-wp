import React, { useEffect, useState } from 'react';
import { Checkbox } from 'antd';

const SubmitOnEnter = (props: any) => {

    const { styling, setStyles, id, target, updateStyling, getPropValue } = props;
    const [value, setValue] = useState(
        getPropValue({ styling, id, target, style: 'submitOnEnter', extra: '' }) || true
    );

    useEffect(() => {

        setValue(
            getPropValue({ styling, id, target, style: 'submitOnEnter', extra: '' }) || true
        )

    }, [id])

    return (
        <div className="submit-on-enter">
            <Checkbox
                checked={value}
                onChange={(e: any) => {
                    updateStyling({
                        setStyles,
                        styling,
                        id,
                        target,
                        toUpdate: {
                            submitOnEnter: e.target.checked
                        }
                    });
                    setValue(e.target.checked)
                }}
            >
                Submit On Enter
            </Checkbox>
        </div>
    );

}

export default SubmitOnEnter;