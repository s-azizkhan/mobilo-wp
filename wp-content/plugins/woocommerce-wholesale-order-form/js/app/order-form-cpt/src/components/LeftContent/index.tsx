import React, { useState, useEffect, useRef } from 'react';
import { Form, Input, Button, notification, PageHeader, message, Tooltip, Modal } from 'antd';
import { Redirect } from 'react-router-dom'
import OrderFormContent from './OrderFormContent';
import StylingAndOptionControls from './StylingAndOptionControls';

import copy from "copy-to-clipboard";
import { CopyOutlined, ExclamationCircleOutlined } from '@ant-design/icons';

// Redux
import { bindActionCreators, Dispatch } from "redux";
import { connect } from 'react-redux';
import { orderFormActions, stylingActions, dragAndDropActions, productActions } from '../../store/actions';

// Helpers
import { usePageInfo } from "../../helpers/usePageInfo";

const { confirm } = Modal;

const { fetchProducts } = productActions;
const { setDndData, resetDndData } = dragAndDropActions;
const { setStyles, setShowStyling } = stylingActions;
const { addNewOrderForm, editOrderForm, fetchOrderForm, setOrderFormSettingsData, deleteOrderForm } = orderFormActions;

interface IOrderFormData {
    id: number,
    status: string,
    type: string,
    title: string,
    content: string
}

const LeftBox = (props: any) => {

    const { styling, actions, orderForm } = props;
    const { pagination } = orderForm;

    const [deleted, setDeleted] = useState(false);
    const [toolTip, setToolTip] = useState('Copy');
    const [shortcode, setShortcode] = useState('');
    const [orderFormID, setOrderFormID] = useState(null);
    const [orderFormStatus, setOrderFormStatus] = useState('');
    const [orderFormData, setOrderFormData] = useState<IOrderFormData>({
        id: 0,
        status: '',
        type: '',
        title: '',
        content: ''
    });

    const { pageType, editPath, postID, pathName } = usePageInfo(orderFormID);

    const [form] = Form.useForm();
    const formEl = useRef(form);
    const validateMessages = {
        required: '${label} is required!'
    };

    const onFinish = (data: any) => {

        data.form_elements = props.data.formElements;
        data.editor_area = props.data.editorArea;
        data.styles = styling.styles;
        data.settings = props.orderForm.settingsData;
        data.status = orderFormStatus;

        if (pageType === 'add-new') {

            actions.addNewOrderForm({
                data,
                successCB: (data: any) => {
                    message.success('Order Form Added!');
                    setOrderFormID(data.id);
                },
                failCB: () => {
                    message.error('Error Adding Order Form!');
                }
            });

        } else if (pageType === 'edit' && orderFormData.id > 0) {

            data.id = orderFormData.id;

            actions.editOrderForm({
                data,
                successCB: () => {

                    setOrderFormData({
                        ...orderFormData,
                        status: orderFormStatus
                    });

                    if (orderFormStatus === 'draft')
                        message.success('Order Form Saved as Draft!');
                    else
                        message.success('Order Form Saved!');

                },
                failCB: () => {
                    message.error('Error Updating Order Form!');
                }
            });

        }
    };

    useEffect(() => {

        if (pageType === 'edit' && postID > 0) {

            actions.fetchOrderForm({
                id: postID,
                successCB: (data: any) => {

                    setOrderFormStatus(data.status)

                    form.setFieldsValue({
                        title: data.title
                    });

                    setOrderFormData(data);
                    setShortcode(data.content);

                    actions.setDndData({
                        ...props.data,
                        formElements: {
                            ...props.data.formElements,
                            ...data.meta.form_elements,
                        },
                        editorArea: {
                            ...props.data.editorArea,
                            ...data.meta.editor_area,
                        }
                    });

                    actions.setStyles({
                        ...styling,
                        styles: {
                            ...styling.styles,
                            ...data.meta.styles
                        }
                    });

                    // Order Form Settings
                    actions.setOrderFormSettingsData(data.meta.settings)

                },
                failCB: () => {
                    notification['error']({
                        message: 'Data cannot be fetched!'
                    });
                }
            });

        } else {
            // Order Form Settings
            actions.setOrderFormSettingsData({})
        }

    }, [actions]);

    const copyToClipboard = (value: string) => {
        copy(value);
        setToolTip('Copied');
    }

    useEffect(() => {

        actions.fetchProducts({
            per_page: 10,
            sort_order: orderForm.settingsData.sort_order || '',
            sort_by: orderForm.settingsData.sort_by || '',
            cart_subtotal_tax: orderForm.settingsData.cart_subtotal_tax || 'incl'
        });

    }, [orderForm.settingsData]);

    const confirmDelete = (post_id: any) => {

        actions.setShowStyling({ show: false });

        confirm({
            title: `Do you want to delete this form?`,
            icon: <ExclamationCircleOutlined />,
            centered: true,
            onOk() {
                actions.deleteOrderForm({
                    post_id,
                    pagination,
                    successCB: () => {
                        notification['success']({
                            message: 'Deleted Successfully!'
                        })
                        setDeleted(true)

                        // Reset dragged items in the editor once the form is deleted
                        actions.resetDndData();

                    },
                    failCB: () => notification['error']({
                        message: 'Unable Failed!'
                    }),
                });
            },
            onCancel() { },
        });

    }

    if (deleted)
        return <Redirect to={`${pathName}?page=order-forms`} />

    return orderFormID === null ? (
        <div className="left-content">
            <PageHeader
                onBack={() => {
                    actions.setShowStyling({ show: false });
                    actions.resetDndData();
                    window.history.back()
                }}
                className="page-header"
                title={pageType === 'add-new' ? 'Add Order Form' : 'Edit Order Form'}
                subTitle={
                    <>
                        {pageType === 'add-new' || (pageType === 'edit' && orderFormStatus === 'draft') ?
                            <Button
                                onClick={() => {
                                    actions.setShowStyling({ show: false });
                                    formEl.current.submit()
                                    setOrderFormStatus('draft')
                                }}
                                style={{ background: '#f3f5f6', color: "#0071a1", border: "1px solid #0071a1", fontWeight: "bold", marginRight: '10px' }}>
                                Save as Draft
                        </Button>
                            : ''}
                        <Button
                            onClick={() => {
                                actions.setShowStyling({ show: false });
                                setOrderFormStatus('publish')
                                formEl.current.submit()
                            }} style={{ background: '#f3f5f6', color: "#0071a1", border: "1px solid #0071a1", fontWeight: "bold" }}>
                            {pageType === 'add-new' || orderFormData.status === 'draft' ? 'Publish' : 'Update'}
                        </Button>
                        {pageType === 'edit' ?
                            <Button
                                danger
                                type="link"
                                onClick={() => confirmDelete(postID)}>
                                Delete Form
                          </Button> : ''}
                    </>
                }
            />

            <Form id="order-form" ref={formEl} style={{ marginTop: '20px' }} form={form} name="nest-messages" onFinish={onFinish} validateMessages={validateMessages} >
                <Form.Item label='Form Title' name='title' rules={[{ required: true }]} style={{ margin: '10px' }}>
                    <Input placeholder='Form Title' className="order-form-field textbox title" />
                </Form.Item>
                <Form.Item
                    style={{ display: shortcode ? "block" : "none" }}
                >
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
                                        onClick={() => copyToClipboard(shortcode)}
                                    />
                                </Tooltip>
                            </div>
                        )}
                        value={shortcode || '[wwof_product_listing beta="true"]'}
                        className="order-form-field textbox shortcode"
                    />
                </Form.Item>
                <Form.Item>
                    <OrderFormContent />
                    <StylingAndOptionControls />
                </Form.Item>
            </Form>
        </div>
    ) : <Redirect to={editPath} />;
}

const mapStateToProps = (store: any, props: any) => ({
    data: store.dragAndDrop,
    orderForm: store.orderForm,
    styling: store.styling
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
    actions: bindActionCreators({
        addNewOrderForm,
        editOrderForm,
        fetchOrderForm,
        setStyles,
        setDndData,
        resetDndData,
        setOrderFormSettingsData,
        fetchProducts,
        deleteOrderForm,
        setShowStyling
    }, dispatch)
});

export default connect(mapStateToProps, mapDispatchToProps)(LeftBox);