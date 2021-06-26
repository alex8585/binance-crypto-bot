import { Alert, Col, Row, Container } from "react-bootstrap"

import { connect } from "react-redux"
import { useEffect } from "react"
import styles from "./index.css"
import {
  fetchOrders,
  setStatus,
  setPage,
  setSort,
  sellOrders,
} from "../../redux/actions/ordersActions"

import { Pagination } from "react-laravel-paginex"

import Spinner from "../Common/Spinner"
import OrderRow from "../OrderRow"
import React, { useState } from "react"

const Orders = ({
  loading,
  alert,
  data,
  page,
  orders,
  status,
  direction,
  sort,
  setSort,
  soldTime,
  fetchOrders,
  setStatus,
  setPage,
  sellOrders,
}) => {
  useEffect(() => {
    fetchOrders({ status, page, direction, sort })
  }, [status, page, direction, sort, soldTime])

  function getData(data) {
    setPage(data.page)
  }

  if (alert) {
    return alert
  }
  if (loading) {
    return <Spinner></Spinner>
  }

  return (
    <>
      <h2
        style={{ display: "block", marginRight: "20px", marginBottom: "20px" }}
      >
        Orders
      </h2>

      <a
        className="btn btn-secondary back-button btn-filter"
        onClick={() => {
          setStatus("all")
        }}
        href="#"
        role="button"
      >
        <i className="fa fa-long-arrow-alt-left"></i>
        Все
      </a>

      <a
        className="btn btn-secondary back-button btn-filter"
        onClick={() => {
          setStatus("trade")
        }}
        href="#"
        role="button"
      >
        <i className="fa fa-long-arrow-alt-left "></i>
        Куплены
      </a>

      <a
        className="btn btn-secondary back-button btn-filter"
        onClick={() => {
          setStatus("complete")
        }}
        href="#"
        role="button"
      >
        <i className="fa fa-long-arrow-alt-left"></i>
        Завершены
      </a>

      <table className="table ">
        <thead>
          <tr>
            <th scope="col">
              <a
                href="#"
                onClick={() => {
                  setSort("buy_time", direction)
                }}
              >
                {" "}
                Дата покупки
              </a>
            </th>

            <th scope="col">
              <a
                href="#"
                onClick={() => {
                  setSort("buy_time", direction)
                }}
              >
                {" "}
                Время покупки
              </a>
            </th>

            {status == "complete" && (
              <>
                <th scope="col">
                  <a
                    href="#"
                    onClick={() => {
                      setSort("sell_time", direction)
                    }}
                  >
                    {" "}
                    Дата продажи
                  </a>
                </th>

                <th scope="col">
                  <a
                    href="#"
                    onClick={() => {
                      setSort("sell_time", direction)
                    }}
                  >
                    {" "}
                    Время продажи
                  </a>
                </th>
              </>
            )}

            <th scope="col">
              {" "}
              <a
                href="#"
                onClick={() => {
                  setSort("symbol", direction)
                }}
              >
                {" "}
                Название пары
              </a>
            </th>
            <th scope="col">
              <a
                href="#"
                onClick={() => {
                  setSort("order_hour", direction)
                }}
              >
                {" "}
                Circle
              </a>
            </th>
            <th scope="col">
              <a
                href="#"
                onClick={() => {
                  setSort("buy_price", direction)
                }}
              >
                {" "}
                Buy price
              </a>
            </th>

            {status == "trade" && (
              <>
                <th scope="col">Current percent </th>
              </>
            )}

            {status == "complete" && (
              <>
                <th scope="col">
                  <a
                    href="#"
                    onClick={() => {
                      setSort("sell_price", direction)
                    }}
                  >
                    {" "}
                    Sell price
                  </a>{" "}
                </th>
                <th scope="col">
                  <a
                    href="#"
                    onClick={() => {
                      setSort("sell_percent", direction)
                    }}
                  >
                    {" "}
                    Sold percent
                  </a>{" "}
                </th>
              </>
            )}

            <th scope="col"> Анализ объема</th>

            {status == "trade" && (
              <>
                <th scope="col">Actions </th>
              </>
            )}
          </tr>
        </thead>
        <tbody>
          {orders.map((row) => (
            <OrderRow
              sellOrders={sellOrders}
              status={status}
              row={row}
              key={row.id}
            ></OrderRow>
          ))}
        </tbody>
      </table>
      {status == "trade" && data.allOpenOrdersIds.length > 0 && (
        <>
          <Row>
            <Col xs={6}>
              <div
                className={
                  data.totalPercent >= 0
                    ? "text-success total-percent"
                    : "text-danger total-percent"
                }
              >
                Прибыль: {data.totalPercent.toFixed(5)}%
              </div>
            </Col>

            <Col xs={6}>
              <a
                className="btn btn-danger sell-all"
                onClick={() => {
                  sellOrders(data.allOpenOrdersIds)
                }}
                href="#"
                role="button"
              >
                Продать все
              </a>
            </Col>
          </Row>
        </>
      )}

      <br></br>
      {data.last_page > 1 && <Pagination changePage={getData} data={data} />}
    </>
  )
}

const mapStateToProps = (state) => {
  return {
    page: state.orders.page,
    loading: state.orders.loading,
    orders: state.orders.ordersArr,
    status: state.orders.status,
    data: state.orders.data,
    direction: state.orders.direction,
    sort: state.orders.sort,
    soldTime: state.orders.soldTime,
    alert: state.alerts.alert,
  }
}

const actions = {
  fetchOrders,
  setStatus,
  setPage,
  setSort,
  sellOrders,
}

export default connect(mapStateToProps, actions)(Orders)
