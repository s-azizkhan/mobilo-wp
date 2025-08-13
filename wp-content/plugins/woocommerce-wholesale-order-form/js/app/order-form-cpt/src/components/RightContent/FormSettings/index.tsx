import React, { useEffect } from 'react'
import { bindActionCreators, Dispatch } from "redux";
import { connect } from 'react-redux';
import { Collapse, Form, Radio, Checkbox, InputNumber, Select } from 'antd';
import { LoadingOutlined } from '@ant-design/icons';

// Actions
import { orderFormActions } from '../../../store/actions';

const { Panel } = Collapse;

const { getOrderFormSettings, setOrderFormSettingsData } = orderFormActions;

const DisplayOption = (props: any) => {

    const { item, settingsData, setOrderFormSettingsData } = props;

    const setSettingState = (meta_key: any, meta_value: any) => {
        setOrderFormSettingsData({
            [meta_key]: meta_value
        });
    }

    switch (item.type) {
        case 'radio':
            return (<>
                <Form.Item className={item.id} style={{ whiteSpace: 'unset' }} name={item.id} label={item.title}>
                    <Radio.Group>
                        {
                            Object.keys(item['options']).map((data: any, index: any) => {
                                return <Radio key={index} value={data}>{item['options'][data]}</Radio>
                            })
                        }
                    </Radio.Group>
                </Form.Item>
            </>);
        case 'checkbox':
            return (<>
                <Form.Item name={item.id} label={item.title}>
                    <Checkbox.Group>
                        <Checkbox value={item.id}>{item.desc}</Checkbox>
                    </Checkbox.Group>
                </Form.Item>
            </>);
        case 'number':
            return (<>
                <Form.Item label={item.title}>
                    <InputNumber />
                </Form.Item>
            </>);
        case 'select':
            return (<>
                <Form.Item label={item.title}>
                    <Select
                        defaultValue={settingsData[item.id] || item.default}
                        onChange={(value: any) => setSettingState(item.id, value)}
                    >
                        {
                            Object.keys(item['options']).map((data: any, index: any) => {
                                return <Select.Option key={index} value={data}>{item['options'][data]}</Select.Option>
                            })
                        }
                    </Select>
                </Form.Item>
            </>);
        case 'wwof_image_dimension':
            return (<>
                <Form.Item label={item.title}>
                    <InputNumber value={item['default'].width} />x<InputNumber value={item['default'].height} />px
                </Form.Item>
            </>)
        default:
            return (<></>);
    }

}

const DisplaySettings = (props: any) => {

    const { settings, settingsData, setOrderFormSettingsData } = props;

    const propsToPass = {
        settingsData,
        setOrderFormSettingsData
    }

    const options = Object.keys(settings).map((index: any) => {

        const item = settings[index];

        return <DisplayOption item={item} key={index} {...propsToPass} />

    });

    return Object.values(settings).length > 0 ? (<>{options}</>) : (<LoadingOutlined />);

}

const FormSettings = (props: any) => {

    const { actions, orderForm } = props;
    const { getOrderFormSettings, setOrderFormSettingsData } = actions;

    const propsToPass = {
        settingsData: orderForm.settingsData,
        setOrderFormSettingsData
    }

    useEffect(() => {
        getOrderFormSettings();
    }, []);

    return (
        <DisplaySettings settings={orderForm.settings} {...propsToPass} />
    )
}

const mapStateToProps = (store: any, props: any) => ({
    orderForm: store.orderForm
});
const mapDispatchToProps = (dispatch: Dispatch) => ({
    actions: bindActionCreators({
        getOrderFormSettings,
        setOrderFormSettingsData
    }, dispatch)
});

export default connect(mapStateToProps, mapDispatchToProps)(FormSettings);