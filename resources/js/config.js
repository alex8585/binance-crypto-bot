let configObj = {
    'test': {
        'test2': 24,
    }
}



let config = (k) => {
    let obj = configObj;
    let arr = k.split(".");
    while(arr.length && (obj = obj[arr.shift()]));
    return obj;

  
}

export default  config;
 
