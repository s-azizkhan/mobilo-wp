import { EOrderFormActionTypes } from '../actions/orderFormActions';

export const defaults = {
    data: [],
    loading: false,
    currentPage: 'home',
    pagination: {
        page: 1,
        defaultCurrent: 1,
        totalPages: 0,
        total: 0,
        pageSize: 10
    },
    settings: {},
    settingsData: {},
    cartSubtotal: ''
};

export default function (state: any = defaults, action: any) {

    switch (action.type) {

        case EOrderFormActionTypes.SET_LOADING:

            return {
                ...state,
                loading: !state.loading
            }

        case EOrderFormActionTypes.SET_ORDER_FORM_DATA:

            const { data, pagination } = action.payload;

            return {
                ...state,
                data,
                pagination: {
                    ...state.pagination,
                    ...pagination
                }
            }

        case EOrderFormActionTypes.SET_CART_SUBTOTAL:

            return {
                ...state,
                cartSubtotal: action.payload
            }

        case EOrderFormActionTypes.SET_PAGE:

            const { page } = action.payload;

            return {
                ...state,
                pagination: {
                    ...state.pagination,
                    page
                }
            }

        case EOrderFormActionTypes.SET_ORDER_FORM_SETTINGS:

            return {
                ...state,
                settings: {
                    ...state.settings,
                    ...action.payload
                }
            };

        case EOrderFormActionTypes.SET_ORDER_FORM_SETTINGS_DATA:

            return {
                ...state,
                settingsData: {
                    ...state.settingsData,
                    ...action.payload
                }
            };

        default:
            return state


    }

}