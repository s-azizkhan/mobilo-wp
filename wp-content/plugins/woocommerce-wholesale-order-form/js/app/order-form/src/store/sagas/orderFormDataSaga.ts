import { takeEvery, put, call } from "redux-saga/effects";
import { EOrderFormDataActionTypes } from '../actions/orderFormDataActions';
import { orderFormDataActions } from '../actions';

import axios from 'axios';

declare var Options: any;

export function* fetchOrderFormData(action: any) {

    try {

        const { id, successCB, failCB } = action.payload;

        const response = yield call(() =>
            axios.get(`${Options.site_url}/wp-json/wwof/v1/order_forms/${id}`)
        );

        if (response && response.data) {

            yield put(orderFormDataActions.setOrderFormData({
                formTitle: {
                    [id]: response.data.title
                },
                formFooter: {
                    [id]: response.data.meta.editor_area.formFooter
                },
                formHeader: {
                    [id]: response.data.meta.editor_area.formHeader
                },
                formTable: {
                    [id]: response.data.meta.editor_area.formTable
                },
                formStyles: {
                    [id]: response.data.meta.styles
                },
                formSettings: {
                    [id]: response.data.meta.settings
                }
            }));

            if (typeof successCB === "function")
                successCB(response);

        } else {

            if (typeof failCB === "function")
                failCB();

        }

    } catch (e) {
        console.log(e)
    }

}

export function* updateSelectedProducts(action: any) {

    try {

        const { selected, orderFormData, orderFormId, product } = action.payload;
        let updatedSelectedProducts: any = orderFormData.formSelectedProducts || {};

        if (selected) {

            if (typeof product !== 'undefined') {

                if (
                    typeof updatedSelectedProducts[orderFormId] !== 'undefined' &&
                    typeof updatedSelectedProducts[orderFormId][product.id] !== 'undefined'
                ) {

                    updatedSelectedProducts = {
                        ...updatedSelectedProducts,
                        [orderFormId]: {
                            ...updatedSelectedProducts[orderFormId],
                            [product.id]: {
                                ...updatedSelectedProducts[orderFormId][product.id],
                                productID: product.id,
                                productTitle: product.name,
                                productType: product.type
                            }
                        }
                    };

                } else {

                    updatedSelectedProducts = {
                        ...updatedSelectedProducts,
                        [orderFormId]: {
                            ...updatedSelectedProducts[orderFormId],
                            [product.id]: {
                                productID: product.id,
                                productTitle: product.name,
                                productType: product.type
                            }
                        }
                    };

                }

                if (typeof orderFormData.formPagination[orderFormId] !== 'undefined') {
                    yield put(orderFormDataActions.setPageSelectedAll({
                        orderFormId,
                        data: {
                            [orderFormData.formPagination[orderFormId].active_page]: false
                        }
                    }));
                }

            } else if (
                typeof orderFormData.formProducts[orderFormId] !== 'undefined' &&
                typeof orderFormData.formProducts[orderFormId]['products'] !== 'undefined'
            ) {

                orderFormData.formProducts[orderFormId]['products'].map((product: any, key: number) => {

                    if (
                        typeof updatedSelectedProducts[orderFormId] !== 'undefined' &&
                        typeof updatedSelectedProducts[orderFormId][product.id] !== 'undefined'
                    ) {

                        updatedSelectedProducts = {
                            ...updatedSelectedProducts,
                            [orderFormId]: {
                                ...updatedSelectedProducts[orderFormId],
                                [product.id]: {
                                    ...updatedSelectedProducts[orderFormId][product.id],
                                    productID: product.id,
                                    productTitle: product.name,
                                    productType: product.type,
                                }
                            }
                        };

                    } else {

                        updatedSelectedProducts = {
                            ...updatedSelectedProducts,
                            [orderFormId]: {
                                ...updatedSelectedProducts[orderFormId],
                                [product.id]: {
                                    productID: product.id,
                                    productTitle: product.name,
                                    productType: product.type,
                                }
                            }
                        };
                    }

                });

                if (typeof orderFormData.formPagination[orderFormId] !== 'undefined') {
                    yield put(orderFormDataActions.setPageSelectedAll({
                        orderFormId,
                        data: {
                            [orderFormData.formPagination[orderFormId].active_page]: selected
                        }
                    }));
                }

            }

        } else {

            let formSelectedProductsCopy = updatedSelectedProducts[orderFormId];

            // Unselect product checkbox
            if (typeof product !== 'undefined') {

                delete formSelectedProductsCopy[product.id];

                if (typeof orderFormData.formPagination[orderFormId] !== 'undefined') {
                    yield put(orderFormDataActions.setPageSelectedAll({
                        orderFormId,
                        data: {
                            [orderFormData.formPagination[orderFormId].active_page]: false
                        }
                    }));
                }

                // Unselect All checkbox
            } else if (
                typeof orderFormData.formProducts[orderFormId] !== 'undefined' &&
                typeof orderFormData.formProducts[orderFormId]['products'] !== 'undefined'
            ) {

                orderFormData.formProducts[orderFormId]['products'].map((product: any, key: number) => {
                    delete formSelectedProductsCopy[product.id];
                });

                if (typeof orderFormData.formPagination[orderFormId] !== 'undefined') {
                    yield put(orderFormDataActions.setPageSelectedAll({
                        orderFormId,
                        data: {
                            [orderFormData.formPagination[orderFormId].active_page]: selected
                        }
                    }));
                }

            }

            updatedSelectedProducts = {
                [orderFormId]: {
                    ...formSelectedProductsCopy
                }
            };

        }

        yield put(orderFormDataActions.setSelectedProductsToAddToCart(updatedSelectedProducts));

    } catch (e) {
        console.log(e)
    }

}

export const actionListener = [
    takeEvery(EOrderFormDataActionTypes.FETCH_ORDER_FORM_DATA, fetchOrderFormData),
    takeEvery(EOrderFormDataActionTypes.UPDATE_ORDER_FORM_SELECTED_PRODUCTS, updateSelectedProducts),
];