module.exports.sendSocket = function sendSocket(msg, dateTime, device) {
    var optionsIo = {
        'method': 'POST',
        'url': 'https://localhost:8080/eh-water',
        'headers': {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            "data": msg
        })
    };
    request(optionsIo, function (error, response) {
        if (error) throw new Error(error);
        console.log(dateTime+ ' Websocket:'+device);
    });
}