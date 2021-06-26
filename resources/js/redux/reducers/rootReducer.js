import {combineReducers} from 'redux';

import {ordersReducer} from './ordersReducer';
import {alertReducer} from './alertReducer';

export const rootReducer = combineReducers(
    {
        orders:ordersReducer,
        alerts:alertReducer,
    }
)