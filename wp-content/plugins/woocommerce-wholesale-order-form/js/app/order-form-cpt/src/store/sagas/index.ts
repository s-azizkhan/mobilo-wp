import { all } from "redux-saga/effects";

// Sagas
import * as orderForm from "./orderFormSaga";
import * as dragAndDrop from "./dragAndDropSaga";
import * as products from "./productsSaga";
import * as minRequirements from "./minimumRequirementNoticeSaga";

export default function* rootSaga() {
    yield all([...orderForm.actionListener]);
    yield all([...dragAndDrop.actionListener]);
    yield all([...products.actionListener]);
    yield all([...minRequirements.actionListener]);
}