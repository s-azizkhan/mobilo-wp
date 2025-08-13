import { all } from "redux-saga/effects";

// Sagas
import * as orderForm from "./orderFormSaga";
import * as orderFormData from "./orderFormDataSaga";
import * as productList from "./productListSaga";

export default function* rootSaga() {
    yield all([...orderForm.actionListener]);
    yield all([...orderFormData.actionListener]);
    yield all([...productList.actionListener]);
}