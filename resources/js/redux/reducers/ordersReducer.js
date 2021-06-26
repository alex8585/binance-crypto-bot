import * as types from "../types";

const initialState = {
    page: 1,
    status:'trade',
    ordersArr: [],
    data: null,
    loading: true,
    direction:'DESC',
    sort: 'buy_time',
    soldTime: null,
}

export const ordersReducer = (state = initialState, action) => {
    switch(action.type) {
        case types.SHOW_ORDERS_LOADER:
            return {...state, loading:true}
        case types.HIDE_ORDERS_LOADER: 
            return {...state, loading:false}
        case types.FETCH_ORDERS:
            return {
                    ...state, 
                    data: action.payload,
                    ordersArr:action.payload.data
                }
        case types.SET_STATUS:
            return {
                ...state, 
                status: action.payload,
                page:1
            }
        case types.SET_PAGE:
            return {...state, page: action.payload}
        case  types.SET_SORT:
            return {
                ...state, 
                sort: action.payload.sort,
                direction: action.payload.direction
            }
        case  types.ORDERS_SOLD:
            console.log(action.payload);
                return {
                    ...state, 
                    soldTime: action.payload
                } 
            
        default: 
        return state;
    }
    
}