import { EProductActionTypes } from '../actions/productActions';

const defaults = {
    products: [],
    categories: [],
    variations: []
};

export default function (state: any = defaults, action: any) {

    switch (action.type) {

        case EProductActionTypes.SET_PRODUCTS:

            return {
                ...state,
                products: action.payload
            }

        case EProductActionTypes.SET_CATEGORIES:

            return {
                ...state,
                categories: action.payload
            }

        case EProductActionTypes.SET_VARIATIONS:

            return {
                ...state,
                variations: action.payload
            }
        default:
            return state

    }

}