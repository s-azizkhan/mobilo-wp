import { EOrderFormActionTypes } from '../actions/orderFormActions';

const defaults = {
    products: [],
    categories: [],
    variations: [],
    fetching: true,
    settings: [],
    cart_subtotal: '',
    sort_order: '',
    cart_url: '',
    loading_more: false,
    show_all: false,
    fetch_error_msg: '',
    attributes: {
        show_search: '',
        products: [],
        categories: []
    }
};

export default function (state: any = defaults, action: any) {

    switch (action.type) {

        case EOrderFormActionTypes.SET_FETCHING:

            return {
                ...state,
                fetching: action.payload
            }

        case EOrderFormActionTypes.SET_APP_STATE:

            const { products, variations, settings, cart_subtotal, cart_url, message, attributes, sort_order } = action.payload.data;
            const fetching = false;
            const fetch_error_msg = message;

            return {
                ...state,
                products,
                variations,
                settings,
                cart_url,
                cart_subtotal,
                fetch_error_msg,
                fetching,
                attributes,
                sort_order
            };

        case EOrderFormActionTypes.SET_CART_SUBTOTAL:

            return {
                ...state,
                cart_subtotal: action.payload.data
            }

        case EOrderFormActionTypes.SET_CATEGORIES:

            const { categories } = action.payload.data;

            return {
                ...state,
                categories
            };

        case EOrderFormActionTypes.SET_CART_SUB_TOTAL:

            return {
                ...state,
                cart_subtotal: action.payload
            };

        default:
            return state

    }

}