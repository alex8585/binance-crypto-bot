#!/usr/bin/node

const puppeteer = require('puppeteer');

var args = process.argv.slice(2);
let symbol = args[0] ? args[0].toUpperCase() : 'ETHUSDT';


let scrape = async (symbol) => {
    
    const timeout = millis => new Promise(resolve => setTimeout(resolve, millis))
    let url = `https://www.tradingview.com/symbols/${symbol}/technicals/`;

    const browser = await puppeteer.launch({
        headless: true,
        //slowMo:10,
        args: [ 
            '--no-sandbox'
           // '--proxy-server='+proxies[0],
            //'--disable-extensions-except=' + CRX_PATH,
            //'--load-extension='+ CRX_PATH
        ]  
    });
	
    const page = await browser.newPage();
    page.setExtraHTTPHeaders({ DNT: "1" });
    await page.setDefaultNavigationTimeout(30000)
    await page.goto(url);

    try{
        await page.waitForSelector('#technicals-root div div div div div div div div div',{timeout : 1000});
    } catch(e) {
        browser.close();
        return {minute5:'', hour:''};
    }



    let day = await page.evaluate( () => { 
        return document.querySelector('#technicals-root').innerText;
    });




    let hour = day;

    await page.evaluate(() => {
        document.querySelector('#technicals-root div div div div div div div div:nth-child(4)').click(); 
    });

    for(let i=0; i<100; i++) {
        hour = await page.evaluate( () => {
            return document.querySelector('#technicals-root').innerText; 
        });
       
        if(hour != day) { break; }

        await timeout(10);
    }





    let minute5 = hour;

    await page.evaluate(() => {
        document.querySelector('#technicals-root div div div div div div div div:nth-child(2)').click(); 
    });


    for(let i=0; i<100; i++) {
        minute5 = await page.evaluate( () => {
            return document.querySelector('#technicals-root').innerText; 
        });
       
        if(minute5 != hour) { break; }

        await timeout(10);
    }




    browser.close();
    return {
        minute5,
        hour
    };
};



scrape(symbol).then((result) => {
    //console.log(result);
   
    let returnData = { 
        symbol
    }

    if(!result.minute5 || !result.hour) {
        returnData.error = "page not found";

        console.log(JSON.stringify(returnData));
        return;
    }


    const hourStrings = result.hour.split('\n');
    const minute5Strings = result.minute5.split('\n');

    returnData.hour = hourStrings[20];
    returnData.minute5 = minute5Strings[20];
        
    
    console.log(JSON.stringify(returnData));




    // let oscillators = {
    //     "txt": strings[7],
    //     "sell": strings[8],
    //     "neutral": strings[10],
    //     "buy": strings[12],
    // };

    // let summary = {
    //     "txt": strings[20],
    //     "sell": strings[21],
    //     "neutral": strings[23],
    //     "buy": strings[25],
    // }

    // let moving_averages = {
    //     "txt": strings[33],
    //     "sell": strings[34],
    //     "neutral": strings[36],
    //     "buy": strings[38],
    // }

    // let result = {
    //     symbol,
    //     oscillators,
    //     summary,
    //     moving_averages,
    // }

    //console.log(JSON.stringify(txtNew));

});

