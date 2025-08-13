import { EPaginationActionTypes } from '../actions/paginationActions';

const defaults = {
    active_page: 1,
    per_page: 12,
    total_products: 0,
    total_page: 0
};

export default function (state: any = defaults, action: any) {

    switch (action.type) {

        case EPaginationActionTypes.SET_PAGINATION_STATE:

            return {
                ...state,
                ...action.payload
            }

        default:
            return state

    }

}