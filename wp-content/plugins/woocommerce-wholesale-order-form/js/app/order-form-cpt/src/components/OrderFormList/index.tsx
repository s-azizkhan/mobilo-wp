import React, { useEffect, useState } from 'react';
import { Table, Tag, Tooltip, Input, notification, Button, Empty, Modal, Select } from 'antd';
import {
    Link,
    useLocation
} from "react-router-dom";

import copy from "copy-to-clipboard";
import { CopyOutlined, FormOutlined, DeleteOutlined, ExclamationCircleOutlined, QuestionCircleOutlined } from '@ant-design/icons';

import { bindActionCreators, Dispatch } from "redux";
import { connect } from 'react-redux';

// Actions
import { orderFormActions } from '../../store/actions';
const { fetchOrderForms, deleteOrderForm, setPage } = orderFormActions;

const { confirm } = Modal;
const { Option } = Select;

const OrderFormLocations = (props: any) => {

    const { locations } = props;
    const [selectedLocation, setSelectedLocation] = useState('');

    const handleChange = (permalink: string) => {
        setSelectedLocation(permalink);
    }

    const Options = locations.map((location: any, key: any) => {
        return <Option key={key} value={location.permalink}>{location.post_title}</Option>
    });

    if (locations.length > 0)
        return (
            <>
                <Select style={{ width: 300 }} onChange={(permalink: string) => handleChange(permalink)}>
                    {Options}
                </Select>
                {
                    selectedLocation ?
                        <>
                            <a style={{ marginLeft: '10px' }} href={selectedLocation} target="blaink">Visit Link</a>
                            <a style={{ marginLeft: '10px' }} href="#" onClick={() => { setSelectedLocation('') }}>Clear</a></>
                        : ''
                }
            </>
        );
    else
        return (<></>)

}

const OrderFormList = (props: any) => {

    const [toolTip, setToolTip] = useState('Copy');
    const [selectedRowKeys, setSelectedRowKeys] = useState([]);

    const pathName = useLocation().pathname;
    const params = useLocation().search;
    const siteURL = params.length > 0 ? `${pathName}${params}&` : `${pathName}?`;

    const { orderForm, actions } = props;
    const { data } = orderForm;
    const { pagination } = orderForm;

    const copyToClipboard = (value: string) => {
        copy(value);
        setToolTip('Copied');
    }

    const dataSource = data.length > 0 ? data.map((data: any) => {

        return {
            key: data.id,
            name: data.title,
            shortcode:
                <Input
                    disabled={false}
                    addonAfter={(
                        <div
                            onMouseLeave={() =>
                                setTimeout(() => setToolTip('Copy'), 200)
                            }
                        >
                            <Tooltip
                                title={toolTip}
                            >
                                <CopyOutlined
                                    onClick={() => copyToClipboard(data.content)}
                                />
                            </Tooltip>
                        </div>
                    )}
                    value={data.content}
                />,
            locations: <OrderFormLocations locations={data.locations} />,
            status: data.status === 'draft' ? <Tag color="#aaaaaa">Draft</Tag> : <Tag color="#91c67f">Publish</Tag>
        };

    }) : [];

    const confirmDelete = (items: any) => {

        confirm({
            title: `Do you want to delete ${Array.isArray(items) && items.length > 1 ? 'these forms' : 'this form'}?`,
            icon: <ExclamationCircleOutlined />,
            centered: true,
            // content: 'Some descriptions',
            onOk() {
                actions.deleteOrderForm({
                    post_id: items,
                    pagination,
                    successCB: () => {
                        notification['success']({
                            message: 'Deleted Successfully!'
                        })

                        setSelectedRowKeys([])
                    },
                    failCB: () => notification['error']({
                        message: 'Unable Failed!'
                    }),
                });
            },
            onCancel() {
                console.log('Cancel');
            },
        });

    }

    const columns = [
        {
            title: 'Order Form Name',
            dataIndex: 'name',
            key: 'name',
            className: 'name',
            render: (text: any, record: any, index: any) => (<Link to={`${siteURL}sub-page=edit&post=${record.key}`} style={{ marginRight: 16 }}>{text}</Link>)
        },
        {
            title: 'Shortcode',
            dataIndex: 'shortcode',
            key: 'shortcode',
        },
        {
            title: () => (<>Locations <Tooltip title="Locations of the order forms."><QuestionCircleOutlined /></Tooltip></>),
            dataIndex: 'locations',
            key: 'locations',
            className: 'locations',
        },
        {
            title: 'Status',
            dataIndex: 'status',
            key: 'status',
        },
        {
            title: 'Action',
            dataIndex: '',
            key: 'x',
            render: (data: any) => {

                return (
                    <>
                        <Link to={`${siteURL}sub-page=edit&post=${data.key}`} style={{ marginRight: 16 }}>
                            <Tooltip title="Edit">
                                <FormOutlined style={{ fontSize: '18px', color: "#0071a1" }} />
                            </Tooltip>
                        </Link>

                        <Tooltip title="Delete">
                            <DeleteOutlined onClick={() => confirmDelete(data.key)} style={{ fontSize: '18px', color: "#FF0000" }} />
                        </Tooltip>

                    </>
                )
            },
        },
    ];

    const rowSelection = {
        selectedRowKeys,
        onChange: (selectedRowKeys: any) => {
            setSelectedRowKeys(selectedRowKeys);
        }
    };

    useEffect(() => {
        actions.fetchOrderForms({ page: pagination.page });
    }, []);

    return (
        <>
            <div style={{ marginBottom: 8, height: '32px' }}>
                <Button danger style={{ display: selectedRowKeys.length === 0 ? 'none' : 'block' }} onClick={() => confirmDelete(selectedRowKeys)}>Delete</Button>
            </div>
            <Table
                className="order-forms"
                rowSelection={rowSelection}
                dataSource={dataSource}
                columns={columns}
                loading={orderForm.loading}
                bordered={true}
                pagination={{
                    current: pagination.page,
                    total: pagination.total,
                    pageSize: pagination.pageSize,
                    showTotal: (total: number, range: any) => `${range[0]} - ${range[1]} of ${total} items`,
                    onChange: (page: number) => {
                        actions.setPage({ page })
                        actions.fetchOrderForms({ page })
                    }
                }}
                locale={{
                    emptyText: (<Empty description={false} />)
                }} />
            <div style={{ marginTop: '-55px', height: '32px' }}>
                <Button danger style={{ display: selectedRowKeys.length === 0 ? 'none' : 'block' }} onClick={() => confirmDelete(selectedRowKeys)}>Delete</Button>
            </div>
        </>
    );
};

const mapStateToProps = (store: any, props: any) => ({
    orderForm: store.orderForm
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
    actions: bindActionCreators({
        fetchOrderForms,
        deleteOrderForm,
        setPage
    }, dispatch)
});

export default connect(mapStateToProps, mapDispatchToProps)(OrderFormList);