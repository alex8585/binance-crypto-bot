<?php

return [

    'binance' => [

        'api_url' => 'https://www.binance.com/api/',

        'tv_parcer_map' => [
            'BTCUSDT' => 'XBTUSDT',
            'LINKUSDT' => '',
            'LENDUSDT' => ''
        ],

        'trade_exclude' => [
            'BTCUSDT',
            'TUSDUSDT',
            'USDCUSDT',
        ],

        'symbols_exclude' => [
            //'BTCUSDT',
            'ETCUSDT',
            'USDSUSDT',
            'NANOUSDT',
            'USDSBUSDT',
            'RLCUSDT',
            'BNTUSDT',
            'USDTDAI',
            'BUSDTRY',
            'ERDUSDT',



            'BKRWUSDT',
            'BUSDUSDT',
            'TUSDUSDT',
            'USDCUSDT',
            'PAXUSDT',
            'AUDUSDT',
            'BIDRUSDT',
            'DAIUSDT',
            'EURUSDT',
            'GBPUSDT',
            'IDRTUSDT',
            'NGNUSDT',
            'RUBUSDT',
            'TRYUSDT',
            'ZARUSDT',
            'UAHUSDT',



            'HOTUSDT',
            'FUNUSDT',
            'IOTAUSDT',
            'BEAMUSDT',



            'KEYUSDT',
            'BELUSDT',
            'SOLUSDT',
            'SOLUUSDT',
            'WINUSDT',
            'VTHOUSDT',
            'VITEUSDT',
            'RSRUSDT'

        ],
        'min_order_price' => '10',
        'tax' => '0.75',
        'tax_percent' => '0.075',

        'trade_mode' => true,
        //'buy_gap_percent' => '0.4',
        'get_order_book_percent' => '0.2',
        'depth_limit' => '1000',
        'quote_volume_filter' => '200000',
    ]

];
