import { takeEvery, put, call } from "redux-saga/effects"
import * as types from "./types"

import { showOrdersLoader, hideOrdersLoader } from "./actions/ordersActions"
import { showAlert } from "./actions/alertActions"

export function* sagaWatcher() {
  yield takeEvery(types.REQUEST_ORDERS, sagaGetOrders)
  yield takeEvery(types.SELL_ORDERS, sagaSellOrders)
}

function* sagaSellOrders(params) {
  try {
    let ids = params.payload

    yield put(showOrdersLoader())
    const response = yield axios.post("/sellorders", { ids })
    console.log(response.data)
    yield put({
      type: types.ORDERS_SOLD,
      payload: response.data.sold_time,
    })

    yield put(hideOrdersLoader())
  } catch (e) {
    //console.log(e);
    yield put(showAlert(e.toString() + ' "sellorders"'))
    yield put(hideOrdersLoader())
  }
}

function* sagaGetOrders(params) {
  try {
    let requestParams = params.payload
    yield put(showOrdersLoader())
    const response = yield axios.get("/getorders", {
      params: requestParams,
    })
    //console.log(response.data)
    yield put({
      type: types.FETCH_ORDERS,
      payload: response.data,
    })
    yield put(hideOrdersLoader())
  } catch (e) {
    //console.log(e);
    yield put(showAlert(e.toString() + ' "getorders"'))
    yield put(hideOrdersLoader())
  }
}
