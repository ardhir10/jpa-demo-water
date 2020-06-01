// ================== SET API SERVICE
const datetime = require('node-datetime');
const modbus = require('jsmodbus');
const net = require('net')
const express = require('express')
var cors = require('cors')

const app = express()
const port = 3000
var bodyParser = require('body-parser');
app.use(cors());
app.use(bodyParser.urlencoded({
    extended: true
}));
app.use(bodyParser.json());


app.post('/test-address', (req, res, next) => {
    const socket = new net.Socket()
    const options = {
        'host': req.body.options.host,
        'port': req.body.options.port
    }
    let dt = datetime.create();
    const client = new modbus.client.TCP(socket)
    let tagAddress = req.body.tags;
    var deviceName = req.body.options.deviceId;
    socket.on('connect', function () {
        async function getData() {
            let result = {}
            let deviceRes = {}
            let valuemodbus;
            for (var key in tagAddress) {
                let resp = await client.readHoldingRegisters(tagAddress[key], 2)
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
            }
            deviceRes[deviceName] = result
            res.send(deviceRes);
            // socket.end()
        }
        getData();
    })
    socket.on('error', () => {
        res.send(false);
        socket.end()
    })

    socket.connect(options)
});

app.post('/test-connection', (req, res, next) => {
    const socket = new net.Socket()
    const options = {
        'host': req.body.host,
        'port': req.body.port
    }
    let dt = datetime.create();

    socket.on('error', () => {
        res.send({
            'status': '502',
            'msg': req.body.host + ':' + req.body.port + ' Bad Gateway !'
        });
        console.log({
            'status': '502',
            'msg': req.body.host + ':' + req.body.port + ' Bad Gateway !'
        });
        socket.end()
    })

    socket.on('connect', () => {
        res.send({
            'status': '200',
            'msg': req.body.host + ':' + req.body.port + ' Connection Succesfully !'
        });
        console.log({
            'status': '200',
            'msg': req.body.host + ':' + req.body.port + ' Connection Succesfully !'
        });
        socket.end()
    })

    socket.connect(options)

});

app.get('/restart', (req, res, next) => {
    res.send({
        'status': '200',
        'msg': 'Gateway Restarted Successfully !'
    })
    process.exit();
});






app.listen(port, () => console.log(`Server ModBus listening on port ${port}!`))

module.exports.server = app;