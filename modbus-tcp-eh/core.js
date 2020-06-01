const modbus = require('jsmodbus');
const net = require('net')
const datetime = require('node-datetime');
const query = require('./pgsqlquery');
const pg = require('./coreQuery');
var jwt = require('jwt-simple');
const isOnline = require('is-online');
var exec = require('child_process').exec;
// ----- GLOBAL CONFIG
var hostWebsocket = 'http://localhost';
var portWebsocket = '1010';
var poolingInterval = 1000; //ms
var loggerInterval = 60; //second

// ----- MQTT GOIOT
const {
    getmqttClient
} = require('./mqttnya')

// ----- JWT API
var server_url = 'http://203.166.207.50/api/server-uji';
var uid = '123456789';
var secretapi = 'secretapi'; //ms
var send_api_interval = 5; //second

const {
    sendJwt
} = require('./testJwt')

var moment = require('moment')

// async function getGlobalSetting() {
//     const gs = `SELECT * FROM global_settings ORDER BY id DESC limit 1 `;
//     var globalSetting = await pg.getQuery(gs);
//     return globalSetting;
// }





// ================== WEBSOCKET
const axios = require('axios');

function sendSocket(controllerData, host) {
    axios.post(host + ':' + portWebsocket + '/eh-water', controllerData)
        .then(function (response) {
            console.log(response.data);
        })
        .catch(function (error) {
            console.log("WEBSOCKET ERROR ! ");
        });
};

function sendGatewayStatus(status = {}, host) {
    axios.post(host + ':' + portWebsocket + '/eh-gateway-status', status)
        .then(function (response) {
            console.log(status);
        })
        .catch(function (error) {
            console.log("WEBSOCKET ERROR ! ");
        });
};


// ----- FIX VALUE
function fix_val(val, del = 2) {
    if (val != undefined || val != null) {
        var rounded = val.toFixed(del).toString().replace('.', "."); // Round Number
        return numberWithCommas(rounded); // Output Result
    }else{
        return '-';
    }

}

function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function sendAlarm(req, host) {
    console.log(req);
    axios.post(host + ':' + portWebsocket + '/eh-water-alarm', req)
        .then(function (response) {
            // console.log(status);
        })
        .catch(function (error) {
            console.log(error);
        });
};



// ----- SPREAD JSMODBUS
const InfiniteLoop = require('infinite-loop');

function ModbusRead(iterator, optns, addressList) {
    let il = {},
        socket = {},
        options = {},
        client = {},
        dataInsert = {},
        logging = true;

    il[iterator] = new InfiniteLoop();
    socket[iterator] = new net.Socket()
    options[iterator] = {
        'host': optns.host,
        'port': optns.port
    }

    client[iterator] = new modbus.client.TCP(socket[iterator])
    socket[iterator].on('connect', async function () {


        // ----- GET GLOBAL SETTING
        const gs = `SELECT * FROM global_settings ORDER BY id DESC limit 1 `;
        var globalSetting = await pg.getQuery(gs);
        hostWebsocket = (globalSetting[0].websocket_host == null) ? hostWebsocket : globalSetting[0].websocket_host; //ms
        portWebsocket = (globalSetting[0].websocket_port == null) ? portWebsocket : globalSetting[0].websocket_port; //ms
        poolingInterval = (globalSetting[0].websocket_pool_interval == null) ? poolingInterval : globalSetting[0].websocket_pool_interval; //ms
        loggerInterval = (globalSetting[0].db_log_interval == null) ? loggerInterval : globalSetting[0].db_log_interval; //ms

        // ----- GET API SETTING
        const apisetting = `SELECT * FROM api_settings ORDER BY id DESC limit 1 `;
        var apisettings = await pg.getQuery(apisetting);
        server_url = (apisettings[0].server_url == null) ? server_url : apisettings[0].server_url; //ms
        uid = (apisettings[0].uid == null) ? uid : apisettings[0].uid; //ms
        secretapi = (apisettings[0].jwt_secret == null) ? jwt_secret : apisettings[0].jwt_secret; //ms
        send_api_interval = (apisettings[0].send_interval == null) ? send_interval : apisettings[0].send_interval; //ms
   



        var counter = 0;
        async function getData() {
            counter++;
            let dt = datetime.create();
            let dateTime = dt.format('Y-m-d H:M:S');
            let dateTimeKlh = dt.format('Y-m-d H:M:00');

            let result = {};
            let deviceRes = {}
            for (var key in addressList) {
                try {
                    let resp = await client[iterator].readHoldingRegisters(addressList[key], 2)
                    var strArray = key.split(":");
                    switch (strArray[1]) {
                        case 'FloatBE':
                            valuemodbus = resp.response._body._valuesAsBuffer.readFloatBE();
                            break;

                        case 'Int16BE':
                            valuemodbus = resp.response._body._valuesAsBuffer.readInt16BE();
                            break;

                        default:
                            valuemodbus = resp.response._body._valuesAsBuffer.readFloatBE();
                            break;
                    }
                    result[strArray[0]] = valuemodbus
                } catch (error) {
                    logging = false;
                    sendGatewayStatus({
                        'status': 'disconnected'
                    }, hostWebsocket);
                    console.log('Error');
                }

            }
            result['tstamp'] = dateTime;
            deviceRes[iterator] = result

            


            // ----- INSERT DATA
            // ----- KOLOM DAN NAMA TAG YANG DIBACA HARUS SAMA DENGAN DATABASE SENSORS->sensor_name
            dataInsert['tstamp'] = dateTime;
            dataInsert['ph'] = deviceRes[iterator].ph;
            dataInsert['tss'] = deviceRes[iterator].tss;
            dataInsert['amonia'] = deviceRes[iterator].amonia;
            dataInsert['cod'] = deviceRes[iterator].cod;
            dataInsert['flow_meter'] = deviceRes[iterator].flow_meter;
            dataInsert['controller_name'] = iterator;


            // ----- KLHK INIT DATA
            var timestamp = moment(dateTimeKlh).unix();
            let payload = {
                "ph": dataInsert['ph'],
                "tss": dataInsert['tss'],
                "amonia": dataInsert['amonia'],
                "amonia": dataInsert['amonia'],
                "flow_meter": dataInsert['flow_meter'],
                "uid": uid,
                "datetime": timestamp,
            }
            let token = jwt.encode(payload, secretapi);
            let encode_payload = { 'token': token };

            // ----- CHECK INTERNET
            let statusKoneksi = await isOnline();
            if (statusKoneksi) {

                // ----- Kirim data yang error
                const queryErrorApi = `SELECT * FROM fail_api_logs`;
                let errorData = await pg.getQuery(queryErrorApi);
                // ----- Jika ada data yang error kirim klh kembali
                if (errorData.length>0) {
                    for (const key in errorData) {
                        let errdata = errorData[key];
                        // ----- Kirim Ke KLH
                        let datetimeError = errdata.created_at;
                        let payloadError = errdata.decode_payload;
                        sendJwt(datetimeError, server_url, payloadError, secretapi)
                        const queryDel = `DELETE FROM fail_api_logs where id = ` + errdata.id;
                        var Delete = await pg.getQuery(queryDel);
                    }
                }
                



                // ----- Send Goiot
                // if (counter % loggerInterval === 0) {
                //     try {
                //         const mqttKita = await getmqttClient()
                //         let dataGoiot = [{
                //             "tag": "ph_EH",
                //             "value": String(deviceRes[iterator].ph),
                //             "time": dateTime
                //         }, {
                //             "tag": "tss_EH",
                //             "value": String(deviceRes[iterator].tss),
                //             "time": dateTime
                //         },
                //         {
                //             "tag": "amonia_EH",
                //             "value": String(deviceRes[iterator].amonia),
                //             "time": dateTime
                //         }, {
                //             "tag": "cod_EH",
                //             "value": String(deviceRes[iterator].cod),
                //             "time": dateTime
                //         }, {
                //             "tag": "flow_meter_EH",
                //             "value": String(deviceRes[iterator].flow_meter),
                //             "time": dateTime
                //         }];
                //         // mqttKita.publish("v2/5eb263e0ac09b50815596240/device1/direct/ph_EH", String(deviceRes[iterator].ph))
                //         mqttKita.publish("v2/5eb263e0ac09b50815596240/device1/json", JSON.stringify(dataGoiot))
                //         console.log("Goiot Send Success !");
                //         // [{"tag":"my_tag_1","value":0, "time":"yyyy-MM-dd HH:mm:ss"},{"tag":"my_tag_2"," value":0,"time":"yyyy-MM-dd HH:mm:ss"}]
                //     } catch (error) {
                //         console.log("Goiot Send Failed !");
                //         console.trace(error)
                //         // process.exit(1)
                //     }
                // }

                // ----- SEND KLHK
                if (counter % send_api_interval === 0) {
                    sendJwt(dateTime, server_url, payload, secretapi)
                }
            }else{
                // ----- Simpan Fail Api Logs
                if (counter % send_api_interval === 0) {
                    let dataFailApi = {};
                    dataFailApi['created_at'] = dateTime;
                    dataFailApi['encode_payload'] = encode_payload;
                    dataFailApi['decode_payload'] = payload;
                    // console.log(dataApi);
                    query.insert('fail_api_logs', dataFailApi, function (res) {
                        console.log(res + ' (KLHK FAIL API LOGS :' + dateTime + ')');
                    });
                    console.log("Koneksi  Mati : API TIDAK DIKIRIM !");
                }
                
            }
            

            // -----  ALARM
            function checkFormula(pv, formula, sp) {
                switch (formula) {
                    case '==':
                        return (pv == sp) ? true : false;
                        break;
                    case '>':
                        return (pv > sp) ? true : false;
                        break;
                    case '>=':
                        return (pv >= sp) ? true : false;
                        break;
                    case '<':
                        return (pv < sp) ? true : false;
                        break;

                    case '<=':
                        return (pv <= sp) ? true : false;
                        break;

                    default:
                        return false;
                        break;
                }
            }


            const queryGetAlarmSetting = `SELECT * FROM alarm_settings AS als `;
            var alarmSettings = await pg.getQuery(queryGetAlarmSetting);
            for (const key in alarmSettings) {
                let alarmSetting = alarmSettings[key];
                let dataAlarm = {};

                if ('ph' === alarmSetting['sensor']) {
                    if (checkFormula(deviceRes[iterator].ph, alarmSetting['formula'], alarmSetting['sp'])) {
                        if (alarmSetting['status'] != 1) {
                            let update = `UPDATE alarm_settings SET status=1 where id = ` + alarmSetting['id'];
                            await pg.getQuery(update);
                            dataAlarm['tstamp'] = dateTime;
                            dataAlarm['text'] = alarmSetting['text'];
                            // KIRIM NOTIF ALARM
                            sendAlarm(dataAlarm, hostWebsocket);
                            query.insert('alarms', dataAlarm, function (res) {
                                console.log(res + ' (' + alarmSetting['text'] + ')');
                            });
                        }
                    } else {
                        let updateNormal = `UPDATE alarm_settings SET status=0 where id = ` + alarmSetting['id'];
                        await pg.getQuery(updateNormal);
                    }
                }

                if ('tss' === alarmSetting['sensor']) {
                    if (checkFormula(deviceRes[iterator].tss, alarmSetting['formula'], alarmSetting['sp'])) {
                        if (alarmSetting['status'] != 1) {
                            let update = `UPDATE alarm_settings SET status=1 where id = ` + alarmSetting['id'];
                            await pg.getQuery(update);
                            dataAlarm['tstamp'] = dateTime;
                            dataAlarm['text'] = alarmSetting['text'];
                            // KIRIM NOTIF ALARM
                            sendAlarm(dataAlarm, hostWebsocket);

                            // SIMPAN ALARM KE DATABASE
                            query.insert('alarms', dataAlarm, function (res) {
                                console.log(res + ' (' + alarmSetting['text'] + ')');
                            });
                        }
                    } else {
                        let updateNormal = `UPDATE alarm_settings SET status=0 where id = ` + alarmSetting['id'];
                        await pg.getQuery(updateNormal);
                    }
                }

                if ('amonia' === alarmSetting['sensor']) {
                    if (checkFormula(deviceRes[iterator].amonia, alarmSetting['formula'], alarmSetting['sp'])) {
                        if (alarmSetting['status'] != 1) {
                            let update = `UPDATE alarm_settings SET status=1 where id = ` + alarmSetting['id'];
                            await pg.getQuery(update);
                            dataAlarm['tstamp'] = dateTime;
                            dataAlarm['text'] = alarmSetting['text'];
                            // KIRIM NOTIF ALARM
                            sendAlarm(dataAlarm, hostWebsocket);

                            // SIMPAN ALARM KE DATABASE
                            query.insert('alarms', dataAlarm, function (res) {
                                console.log(res + ' (' + alarmSetting['text'] + ')');
                            });
                        }
                    } else {
                        let updateNormal = `UPDATE alarm_settings SET status=0 where id = ` + alarmSetting['id'];
                        await pg.getQuery(updateNormal);
                    }
                }

                if ('cod' === alarmSetting['sensor']) {
                    if (checkFormula(deviceRes[iterator].cod, alarmSetting['formula'], alarmSetting['sp'])) {
                        if (alarmSetting['status'] != 1) {
                            let update = `UPDATE alarm_settings SET status=1 where id = ` + alarmSetting['id'];
                            await pg.getQuery(update);
                            dataAlarm['tstamp'] = dateTime;
                            dataAlarm['text'] = alarmSetting['text'];
                            // KIRIM NOTIF ALARM
                            sendAlarm(dataAlarm, hostWebsocket);

                            // SIMPAN ALARM KE DATABASE
                            query.insert('alarms', dataAlarm, function (res) {
                                console.log(res + ' (' + alarmSetting['text'] + ')');
                            });
                        }
                    } else {
                        let updateNormal = `UPDATE alarm_settings SET status=0 where id = ` + alarmSetting['id'];
                        await pg.getQuery(updateNormal);
                    }
                }

                if ('flow_meter' === alarmSetting['sensor']) {
                    if (checkFormula(deviceRes[iterator].flow_meter, alarmSetting['formula'], alarmSetting['sp'])) {
                        if (alarmSetting['status'] != 1) {
                            let update = `UPDATE alarm_settings SET status=1 where id = ` + alarmSetting['id'];
                            await pg.getQuery(update);
                            dataAlarm['tstamp'] = dateTime;
                            dataAlarm['text'] = alarmSetting['text'];
                            // KIRIM NOTIF ALARM
                            sendAlarm(dataAlarm, hostWebsocket);

                            // SIMPAN ALARM KE DATABASE
                            query.insert('alarms', dataAlarm, function (res) {
                                console.log(res + ' (' + alarmSetting['text'] + ')');
                            });
                        }
                    } else {
                        let updateNormal = `UPDATE alarm_settings SET status=0 where id = ` + alarmSetting['id'];
                        await pg.getQuery(updateNormal);
                    }
                }

            }

        

            // ------  INSERT KE DATABASE
            if (counter % loggerInterval === 0) {
                
                // ----- KIRIM SMS
                let text = 'SPARING ' + uid + ' ' + dateTime + ' ' + fix_val(deviceRes[iterator].ph,2) + ' ' + fix_val(deviceRes[iterator].cod,2) + ' ' + fix_val(deviceRes[iterator].tss,2) + ' ' + fix_val(deviceRes[iterator].amonia,2) + ' ' + fix_val(deviceRes[iterator].flow_meter,2) + ' ' ;
                let command = exec('gammu --sendsms TEXT 081315709107 -text "' + text +'"');
                console.log(text);
 
                command.stdout.on('data', function (data) {
                    console.log('' + data);
                });

                if (logging) {
                    await query.insert('logs', dataInsert, function (res) {
                        console.log(res + ' (' + iterator + ')');
                    });
                }
            }

            // -----  KIRIM WEBSOCKET
            deviceRes[iterator]['controller'] = iterator
            sendSocket(deviceRes[iterator], hostWebsocket);
            if (logging) {
                sendGatewayStatus({
                    'status': 'device-connect'
                }, hostWebsocket);
                sendGatewayStatus({
                    'status': 'socket-connect'
                }, hostWebsocket);
            }

            console.log('----------------------')
            console.log('\n')
        }

        async function Pooling() {
            getData();
        }

        il[iterator].add(Pooling);
        il[iterator].setInterval(poolingInterval).run();
    })
    socket[iterator].on('error', (err) => {
        console.log("Gagal Koneksi " + optns.deviceId + ":" + err.errno)
        sendGatewayStatus({
            'status': 'device-disconnect'
        }, hostWebsocket);
        // process.exit();
    });
    socket[iterator].connect(options[iterator])
}


// ----- READ CONTROLLER
var controller = require('./coreController');
controller.getController((c) => {
    for (var i in c) {
        ModbusRead(i, c[i].options, c[i].tags);
    }
});


// ----- SOCKET IO
const socket = require('socket.io-client')('http://localhost:1010', {
    query: "from=Gateway"
});
socket.on('connect', function () {
    // console.log('Socket connected...');
});



// ----- RESTFULL TESTING
require('./coreApi');