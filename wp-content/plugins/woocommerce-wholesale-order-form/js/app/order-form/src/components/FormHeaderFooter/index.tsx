import React from 'react';
import DisplayItems from './DisplayItems';

import { bindActionCreators, Dispatch } from "redux";
import { connect } from 'react-redux';

// Actions
import { orderFormActions } from '../../store/actions';
const { fetchProducts } = orderFormActions;

declare var Options: any;

const FormHeaderFooter = (props: any) => {

    const { orderFormId, section, formHeader, formFooter, orderFormData, actions } = props;
    const { fetchProducts } = actions;

    return (

        <div style={{
            display: "flex",
            flexDirection: 'column',
        }} className={`${section === 'formHeader' ? 'form-header' : 'form-footer'}`}>
            <div
                style={{
                    margin: '4px 0px',
                    position: 'relative'
                }}
            >
                <DisplayItems
                    orderFormId={orderFormId}
                    products={props.products}
                    styles={props.formStyles}
                    dataRows={section === 'formHeader' ? formHeader : formFooter}
                    fetchProducts={
                        (args: any) => {
                            fetchProducts({
                                search: '',
                                category: '',
                                active_page: orderFormData.formPagination[orderFormId]['active_page'] || 1,
                                searching: 'no',
                                sort_order: '',
                                show_all: false,
                                attributes: { id: orderFormId },
                                wholesale_role: Options.wholesale_role,
                                per_page: orderFormData.formSettings[orderFormId]['products_per_page'] || 10,
                                form_settings: orderFormData.formSettings[orderFormId],
                                ...args
                            })
                        }
                    }
                />
            </div>
        </div >
    );
}

const mapStateToProps = (store: any, props: any) => ({
    orderFormData: store.orderFormData,
    formHeader: store.orderFormData.formHeader[props.orderFormId],
    formFooter: store.orderFormData.formFooter[props.orderFormId],
    formStyles: store.orderFormData.formStyles[props.orderFormId]
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
    actions: bindActionCreators({
        fetchProducts
    }, dispatch)
});

export default connect(mapStateToProps, mapDispatchToProps)(FormHeaderFooter);