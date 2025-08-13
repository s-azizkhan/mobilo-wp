import React from 'react';
import { Tabs, Form, Affix } from 'antd';
import { TableOutlined, SettingOutlined, ReadOutlined } from '@ant-design/icons';
import EditForm from './EditForm';
import FormSettings from './FormSettings';
import Locations from './Locations';

import { defaults } from '../../store/reducers/dragAndDropReducer';

// Redux
import { connect } from 'react-redux';

const { TabPane } = Tabs;


// Update saved data
const updateData = (savedData: any, defaultData: any) => {
    savedData.items = defaultData.items;
    return savedData;
}

const RightContent = (props: any) => {

    const { data } = props;
    const updatedData = updateData(data, defaults);

    return (
        <div className="right-content" style={{ position: 'relative' }}>
            <Affix offsetTop={100} style={{ position: 'absolute', left: '0px' }}>
                <Tabs type="card">
                    <TabPane tab={<><TableOutlined />Edit Form</>} key="1" >
                        <EditForm data={updatedData} />
                    </TabPane>
                    <TabPane tab={<><SettingOutlined />Settings</>} key="2">
                        <Form className="form-settings">
                            <FormSettings />
                        </Form>
                    </TabPane>
                    <TabPane tab={<><ReadOutlined />Locations</>} key="3">
                        <Locations />
                    </TabPane>
                </Tabs>
            </Affix>
        </div >
    )
};

const mapStateToProps = (store: any, props: any) => ({
    data: store.dragAndDrop
});
export default connect(mapStateToProps)(RightContent);