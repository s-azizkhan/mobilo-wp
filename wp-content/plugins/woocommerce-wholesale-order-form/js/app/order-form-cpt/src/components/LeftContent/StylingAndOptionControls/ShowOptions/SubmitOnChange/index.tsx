import React, { useEffect, useState } from 'react';
import { Checkbox } from 'antd';

const SubmitOnChange = (props: any) => {

    const { styling, setStyles, id, target, updateStyling, getPropValue } = props;
    const [value, setValue] = useState(
        getPropValue({ styling, id, target, style: 'submitOnChange', extra: '' }) || true
    );

    useEffect(() => {

        setValue(
            getPropValue({ styling, id, target, style: 'submitOnChange', extra: '' }) || true
        )

    }, [id])

    return (
        <div className="submit-on-change">
            <Checkbox
                checked={value}
                onChange={(e: any) => {
                    updateStyling({
                        setStyles,
                        styling,
                        id,
                        target,
                        toUpdate: {
                            submitOnChange: e.target.checked
                        }
                    });
                    setValue(e.target.checked)
                }}
            >
                Submit On Change
            </Checkbox>
        </div>
    );

}

export default SubmitOnChange;