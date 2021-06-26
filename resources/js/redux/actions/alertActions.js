import * as types from '../types';

export function showAlert(text) {
    return dispatch => {
        dispatch({
            type:types.SHOW_ALERT,
            payload:text
        })
    }
}

export function hideAlert() {
    return {
        type:types.HIDE_ALERT
    }
}

