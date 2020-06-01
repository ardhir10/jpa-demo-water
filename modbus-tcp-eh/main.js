const axios = require('axios');
var InfiniteLoop = require('infinite-loop');
var il = new InfiniteLoop();
var datetime = require('node-datetime');

const controller = require('./controller');
const query = require('./pgsqlquery');
const socket = require('./socket-setting.js');


var counter = 0;
async function addOne() {
    counter++;
    for (var key in controller) {
        async function getData(callBack) {
            let device = key;
            let dataPM1 = {};
            let dt = datetime.create();
            let dateTime = dt.format('Y-m-d H:M:00');
            let dateTime2 = dt.format('Y-m-d H:M:S');
            let request = controller[key];
            try {
                let response = await axios.post('http://localhost:3000/getTags', request)
                // DISIMPEN AJA DULU
                // if (typeof response.data[device].energy_kwh_total === 'undefined') {
                //     process.exit();
                // }


                //===== INSERT AREA
                // PM1 
                dataPM1['datetime'] = dateTime;
                dataPM1['device_id'] = device;
                dataPM1['energy_kwh_total'] = response.data[device].energy_kwh_total;
                dataPM1['created_at'] = dateTime;

                callBack(dataPM1);

                // socket.sendSocket(response.data, dateTime, device)
                // if (counter % 60 === 0) {
                //     await query.insert('logs', dataPM1, function (res) {
                //         // console.log(res + ' (' + device + ')');
                //         callBack(res + ' (' + device + ')')
                //     });
                // }
            } catch (error) {
                callBack(error);
                process.exit();
            }

        }

        getData(function (res) {
            if (res != '') {
                console.log(res);
            } else {
                process.exit();
            }
        })
    }
    console.log('=============');
}
il.add(addOne);
il.setInterval(1000).run();