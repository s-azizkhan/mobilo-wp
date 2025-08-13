import { takeEvery, put, call } from "redux-saga/effects";
import { EProductActionTypes } from '../actions/productActions';

import { productActions, orderFormActions } from '../actions';
import { paginationActions } from '../actions';

import axios from 'axios';
declare var Options: any;

export function* fetchProducts(action: any) {

    try {

        const { per_page, sort_order, sort_by, cart_subtotal_tax } = action.payload;

        const qs = require('qs');
        const response = yield call(() =>

            axios.post(Options.ajax, qs.stringify({
                'action': 'wwof_api_get_products',
                'per_page': per_page,
                'sort_order': sort_order,
                'sort_by': sort_by,
                'cart_subtotal_tax': cart_subtotal_tax
            }))
        );

        if (response && response.data) {

            yield put(orderFormActions.setCartSubtotal(response.data.cart_subtotal));
            yield put(productActions.setProducts(response.data.products));
            yield put(productActions.setVariations(response.data.variations));
            yield put(paginationActions.setPaginationState({
                active_page: 1,
                per_page: 12,
                total_products: response.data.total_products
            }));

        } else
            console.log(response)

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

            yield put(
                productActions.setCategories(response.data.categories)
            );

        }

    } catch (e) {
        console.log(e)
    }

}

export const actionListener = [
    takeEvery(EProductActionTypes.FETCH_PRODUCTS, fetchProducts),
    takeEvery(EProductActionTypes.FETCH_CATEGORIES, fetchCategories)
];