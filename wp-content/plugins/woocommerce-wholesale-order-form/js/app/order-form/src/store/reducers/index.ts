import { combineReducers } from 'redux';

// Reducers
import orderFormReducer from './orderFormReducer';
import orderFormDataReducer from './orderFormDataReducer';

const reducers = combineReducers({
    orderForm: orderFormReducer,
    orderFormData: orderFormDataReducer
});

export default reducers;