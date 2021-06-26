import * as types from '../types';


export function fetchOrders(params) {
    return {
        type: types.REQUEST_ORDERS,
        payload:params
    }

}

export function showOrdersLoader() {
    return {
        type:types.SHOW_ORDERS_LOADER
    }
}

export function hideOrdersLoader() {
    return {
        type:types.HIDE_ORDERS_LOADER
    }
}

export function setStatus(status) {
    return {
        type:types.SET_STATUS,
        payload:status
    }
}

export function setPage(page) {
    return {
        type:types.SET_PAGE,
        payload:page
    }
}

export function setSort(sort, direction) {
    if(direction == 'ASC') {
        direction = 'DESC'
    } else {
        direction = 'ASC'
    }

    return {
        type:types.SET_SORT,
        payload:{
            sort, direction
        }
    }
}

export function sellOrders(ids) {
    if(!Array.isArray(ids)) {
        ids =  [ids];
    }
    return {
        type: types.SELL_ORDERS,
        payload:ids
    }
}
