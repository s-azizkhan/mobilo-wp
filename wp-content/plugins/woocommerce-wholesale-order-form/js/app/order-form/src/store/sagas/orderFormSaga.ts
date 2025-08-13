import { takeEvery, all, put, call } from "redux-saga/effects";
import { EOrderFormActionTypes } from '../actions/orderFormActions';
import { orderFormActions, orderFormDataActions } from '../actions';

import axios from 'axios';

declare var Options: any;

export function* fetchProducts(action: any) {

    const { search, category, active_page, searching, sort_order, show_all, products, categories, attributes, wholesale_role, per_page, sort_by, form_settings } = action.payload;

    try {

        if (attributes.id !== undefined) {
            yield (
                put(orderFormDataActions.setOrderFormProducts({
                    [attributes.id]: {
                        fetching: true
                    }
                }))
            );
        } else {
            yield (put(orderFormActions.setFetching(true)));
        }

        const qs = require('qs');
        const response = yield call(() =>

            axios.post(Options.ajax, qs.stringify({
                'action': 'wwof_api_get_products',
                'search': search,
                'category': category,
                'page': active_page,
                'searching': searching || 'no',
                'sort_order': sort_order,
                'sort_by': sort_by,
                'products': products,
                'categories': categories,
                'show_all': show_all,
                'wholesale_role': wholesale_role,
                'per_page': per_page,
                'cart_subtotal_tax': form_settings['cart_subtotal_tax'] || 'incl'
            }))
        );

        if (response && response.data) {

            const data = { ...response.data, attributes, active_page, sort_order };
            const total_products = parseInt(response.data.total_products);

            if (
                attributes.id !== undefined &&
                attributes.id !== 0
            ) {

                yield (
                    put(orderFormActions.setAppState({ data }))
                );

                yield (
                    put(orderFormDataActions.setCartSubtotal({
                        [attributes.id]: {
                            cartSubtotal: data.cart_subtotal
                        }
                    }))
                );

                yield (
                    put(orderFormDataActions.setOrderFormProducts({
                        [attributes.id]: {
                            fetching: false,
                            products: response.data.products,
                            variations: response.data.variations
                        }
                    }))
                );

                if (typeof response.data.settings !== 'undefined') {
                    yield (
                        put(orderFormDataActions.setOrderFormPagination({
                            orderFormId: attributes.id,
                            data: {
                                active_page,
                                per_page: per_page,//response.data.settings['wwof_general_products_per_page'],
                                total_products,
                                total_page: response.data.total_page
                            }
                        }))
                    );
                }

            }

        }

    } catch (e) {
        console.log(e)
    }

}

export function* fetchCategories(action: any) {

    const { categories } = action.payload;

    try {

        const qs = require('qs');
        const response = yield call(() =>

            axios.post(Options.ajax, qs.stringify({
                'action': 'wwof_api_get_categories',
                'categories': categories
            }))
        );

        if (response && response.data) {

            const data = { ...response.data };

            yield put(
                orderFormActions.setCategories({ data })
            );

        }

    } catch (e) {
        console.log(e)
    }

}

export function* fetchVariations(action: any) {

    const { product_id, page, search_string, successCB, failCB } = action.payload;

    try {

        const qs = require('qs');
        const response = yield call(() =>

            axios.post(Options.ajax, qs.stringify({
                'action': 'wwof_api_get_variations',
                'product_id': product_id,
                'page': page,
                'search': search_string
            }))

        );

        if (response && response.data.status === 'success') {

            if (typeof successCB === "function")
                successCB(response);

        } else {

            if (typeof successCB === "function")
                failCB();

        }

    } catch (e) {
        console.log(e)
    }

}

export const actionListener = [
    takeEvery(EOrderFormActionTypes.FETCH_PRODUCTS, fetchProducts),
    takeEvery(EOrderFormActionTypes.FETCH_CATEGORIES, fetchCategories),
    takeEvery(EOrderFormActionTypes.FETCH_VARIATIONS, fetchVariations)
];