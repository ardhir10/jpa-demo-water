
const datetime = require('node-datetime');

let dt = datetime.create('2019-12-09 06:28:23');
// let dateTime = dt.format('Y-m-d H:M:S');
// let dateTimeKlh = dt.format('Y-m-d H:M:00');

// var pastTime = dt.epoch();
// console.log(pastTime);
 

var moment = require('moment')

var created = moment('2019-09-06 02:51:49').unix();
let value = 1589186100*1000 ;
let now = moment(value).format("Y-MM-DD HH:mm:ss");


// console.log(created);
console.log(now);
