import { takeEvery, put, call } from "redux-saga/effects";
import { EMinRequirementsTypes } from '../actions/minimumRequirementNoticeActions';
import { minRequirements } from '../actions';

// Helpers
import axios from 'axios';

declare var Options: any;

export function* wwppRequiredVersions(action: any) {

    const { successCB, failCB } = action.payload;

    try {

        const qs = require('qs');
        const response = yield call(() =>
            axios.post(Options.ajax, qs.stringify({
                'action': 'wwpp_minimum_requirement'
            }))
        );

        if (response && response.data.status === 'success') {

            if (typeof successCB === "function")
                successCB(response.data);

        } else {

            if (typeof successCB === "function")
                failCB(response.data);

        }

    } catch (e) {
        console.log(e)
    }

}

export function* removeMinimumWWPPFailMessage(action: any) {

    const { successCB, failCB } = action.payload;

    try {

        const qs = require('qs');
        const response = yield call(() =>
            axios.post(Options.ajax, qs.stringify({
                'action': 'remove_wwpp_minimum_requirement_message'
            }))
        );

        if (response && response.data.status === 'success') {

            if (typeof successCB === "function")
                successCB(response.data);

        } else {

            if (typeof successCB === "function")
                failCB(response.data);

        }

    } catch (e) {
        console.log(e)
    }

}

export const actionListener = [
    takeEvery(EMinRequirementsTypes.WWPP_REQUIRED_VERSIONS, wwppRequiredVersions),
    takeEvery(EMinRequirementsTypes.REMOVE_WWPP_MIN_FAIL_MESSAGE, removeMinimumWWPPFailMessage),
];