import { combineReducers } from 'redux';

// Reducers
import orderFormReducer from './orderFormReducer';
import dragAndDropReducer from './dragAndDropReducer';
import stylingReducer from './stylingReducer';
import productsReducer from './productsReducer';
import paginationReducer from './paginationReducer';

const reducers = combineReducers({
    orderForm: orderFormReducer,
    dragAndDrop: dragAndDropReducer,
    styling: stylingReducer,
    products: productsReducer,
    pagination: paginationReducer
});

export default reducers;