
var mqtt = require('mqtt')

const getmqttClient = function () {
    return new Promise((resolve, reject) => {
        if (global.mqttkita !== undefined) {
            return resolve(global.mqttkita)
        }
        global.mqttkita = mqtt.connect("", {
            host:"broker.goiot.id",
            protocol:"mqtt",
            clientId: '5eb263e0ac09b50815596240#device1#',
            username: 'ardhi-jpa',
            password: '5eb26354a44e97082cc4ccfa',
            port:1883,
        })
        global.mqttkita.on('connect', function () {
            return resolve(global.mqttkita)

        })
        global.mqttkita.on('error', function (err) {
            reject(err)
        })
    })


}

module.exports.getmqttClient = getmqttClient;