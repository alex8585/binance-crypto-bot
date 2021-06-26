## About crypto trading bot

This is one of my old crypto trading bot for [Binance](https://www.binance.com/en) crypto exchange powered by Laravel and React

I strongly recommend you not to use this bot for trading purpose because this bot dos't trade well. Only when it is a bull market this bot can be able not lose money. And this bot can't define are we in a Bull Market or not.)

## Disclaimer

High Risk Investment

Trading cryptocurrencies carries a high level of risk, and may not be suitable for all investors. Before deciding to trade cryptocurrency you should carefully consider your investment objectives, level of experience, and risk appetite. The possibility exists that you could sustain a loss of some or all of your initial investment and therefore you should not invest money that you cannot afford to lose. You should be aware of all the risks associated with cryptocurrency trading, and seek advice from an independent financial advisor. ICO's, IEO's, STO's and any other form of offering will not guarantee a return on your investment. Since

Any opinions, news, research, analyses, prices, or other information contained on this website is provided as general market commentary, and does not constitute investment advice. The Block Runner will not accept liability for any loss or damage, including without limitation to, any loss of profit, which may arise directly or indirectly from use of or reliance on such information. All opinions expressed on this site are owned by the respective writer and should never be considered as advice in any form.

The Block Runner makes no representation or warranties as to the accuracy and or timelines of the information contained herein. A qualified professional should be consulted before making any financial decisions.

## Installation

- cd this-bot-project-folder

- composer install

- php artisan migrate

- php artisan get_symbols

- To be able Login to bot admin panel you have to create a standard Laravel user on your own. You can allow register page for now or create a user from PHPMyAdmin directly )

## To start trading

In order to start trading, you have to run two processes. Files for systemd are /services/orders_open.service , /services/orders_change.service or you can run their as commands from shell
php artisan orders_open
php artisan orders_change
