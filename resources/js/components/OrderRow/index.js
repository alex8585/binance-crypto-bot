import React from "react"
import PropTypes from "prop-types"
import styles from "./index.css"

const OrdersRow = ({ status, row, sellOrders }) => {
  return (
    <tr>
      <td>{row.buy_time_d}</td>
      <td>{row.buy_time_t}</td>
      {status == "complete" && (
        <>
          <td>{row.sell_time_d}</td>
          <td>{row.sell_time_t}</td>
        </>
      )}
      <td>{row.symbol}</td>
      <td>{row.order_hour}</td>
      <td>
        <div className="text-danger">{row.averaged_at ? "Average" : ""}</div>
        {(+row.buy_price).toFixed(6)}
      </td>

      {status == "trade" && (
        <td className={row.cur_percent >= 0 ? "text-success" : "text-danger"}>
          {row.cur_percent}
        </td>
      )}

      {status == "complete" && (
        <>
          <td> {row.sell_price.toFixed(6)} </td>
          <td
            className={row.sell_percent >= 0 ? "text-success" : "text-danger"}
          >
            {" "}
            {row.sell_percent}
          </td>
        </>
      )}

      {row.order_books_id && (
        <td>
          <a
            className="btn btn-primary back-button"
            href={"orderbook/" + row.order_books_id}
            role="button"
          >
            <i className="fa fa-long-arrow-alt-left"></i>
            View
          </a>
        </td>
      )}

      {!row.order_books_id && (
        <td>
          <a
            className="btn btn-primary back-button disabled"
            aria-disabled="true"
            href="#"
            role="button"
          >
            <i className="fa fa-long-arrow-alt-left"></i>
            View
          </a>
        </td>
      )}

      {status == "trade" && (
        <td>
          <a
            className="btn btn-danger"
            onClick={() => {
              sellOrders(row.id)
            }}
            href="#"
            role="button"
          >
            Продать
          </a>
        </td>
      )}
    </tr>
  )
}

export default OrdersRow
