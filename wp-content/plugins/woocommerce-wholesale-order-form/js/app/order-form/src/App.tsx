import React, { useEffect } from 'react';

import FormHeaderFooter from './components/FormHeaderFooter';
import FormTable from './components/FormTable';

import { Tooltip } from 'antd';
import { InfoCircleOutlined } from '@ant-design/icons';

import './styles.js';

import { bindActionCreators, Dispatch } from "redux";
import { connect } from 'react-redux';

// Actions
import { orderFormActions, orderFormDataActions } from './store/actions/';

const { fetchProducts, fetchCategories } = orderFormActions;
const { fetchOrderFormData } = orderFormDataActions;

declare var Options: any;

const App = (props: any) => {

  const { orderForm, orderFormData, attributes } = props;
  const { fetchProducts, fetchCategories, fetchOrderFormData } = props.actions;

  const postId = parseInt(attributes.id);

  useEffect(() => {

    try {

      // Fetch Order Form
      if (attributes.id !== undefined && postId > 0) {

        fetchOrderFormData({
          id: postId,
          successCB: (response: any) => { },
          failCB: () => {
            console.log('Data cannot be fetched!');
          }
        });

      }

    } catch (error) {
      console.log(error)
    }

  }, [attributes.id]);


  useEffect(() => {

    try {

      if (typeof orderFormData.formSettings[postId] !== 'undefined') {

        let sort_by = 'date';
        if (
          typeof orderFormData.formSettings[postId].sort_by !== 'undefined' &&
          orderFormData.formSettings[postId].sort_by !== 'default'
        )
          sort_by = orderFormData.formSettings[postId].sort_by;

        fetchProducts({
          sort_order: orderFormData.formSettings[postId].sort_order || 'desc',
          sort_by,
          search: orderForm.search,
          category: orderForm.selected_category,
          active_page: 1,
          searching: 'no',
          products: attributes.products || "",
          categories: attributes.categories || "",
          show_all: orderForm.show_all,
          attributes,
          wholesale_role: Options.wholesale_role,
          per_page: orderFormData.formSettings[postId]['products_per_page'] || 10,
          form_settings: orderFormData.formSettings[postId]
        });
      }

    } catch (error) {
      console.log(error)
    }

  }, [orderFormData.formSettings[postId]]);

  useEffect(() => {

    try {

      fetchCategories({
        categories: orderForm.categories
      });

    } catch (error) {
      console.log(error)
    }

  }, []);

  const OrderFormTitle = () => {

    if (
      typeof orderFormData !== 'undefined' &&
      typeof orderFormData.formTitle !== 'undefined' &&
      typeof orderFormData.formTitle[postId] !== 'undefined'
    ) {

      // Show tooltip for draft order form.
      if (
        typeof attributes.post_status !== 'undefined' &&
        attributes.post_status === 'draft'
      )
        return (
          <h2>
            {orderFormData.formTitle[postId]}
            <Tooltip title='This Order Form is in "Draft" status. This form will only be visible only for admin user.'>
              <InfoCircleOutlined style={{ fontSize: '18px', marginLeft: '10px', color: 'red' }} />
            </Tooltip>
          </h2>
        )
      else
        return (
          <h2>{orderFormData.formTitle[postId]}</h2>
        )
    } else return (<></>)

  };

  if (attributes.id !== undefined && postId > 0) {

    return (
      <>
        <OrderFormTitle />
        {Object.keys(orderFormData.formHeader).length > 0 ? <FormHeaderFooter section="formHeader" orderFormId={postId} /> : ''}
        {Object.keys(orderFormData.formTable).length > 0 ? <FormTable orderFormId={postId} /> : ''}
        {Object.keys(orderFormData.formFooter).length > 0 ? <FormHeaderFooter section="formFooter" orderFormId={postId} /> : ''}
      </>
    )

  } else return (<></>);

}

const mapStateToProps = (store: any, props: any) => ({
  orderForm: store.orderForm,
  orderFormData: store.orderFormData,
  attributes: props.attributes
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators({
    fetchProducts,
    fetchCategories,
    fetchOrderFormData
  }, dispatch)
});

export default connect(mapStateToProps, mapDispatchToProps)(App);