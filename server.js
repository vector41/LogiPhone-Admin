const express = require('express');
const app = express();
const http = require('http');
const { Server } = require('socket.io');
const { createServer } = require("http");
const { default: axios } = require('axios');
const server = http.createServer(app);
const httpServer = createServer(app);

const io = require('socket.io')(server, {
    cors: {
        origin: '*',
    },
});

const apiUri = "http://localhost:8000";
// const apiUri = env('APP_URL');

app.get('/', (req, res) => {
    res.send('<h1>Hello world</h1>');
});

io.on('connection', (socket) => {

    console.log('connection');

    socket.on('disconnect', (socket) => {
        console.log('Disconnect');
    });

    socket.on('insertCall', async (param) => {
        let res = await axios.post('http://localhost:8000/api/app/insertCall', {
            callNumber: param.callNumber,
            receiveNumber: param.receiveNumber
        });

        socket.emit('insertCallRes', res);
    })

    socket.on('endCall', async (param) => {
        let res = await axios.post('http://localhost:8000/api/app/endCall', {
            callNumber: param.callNumber,
            receiveNumber: param.receiveNumber,
            date: param.date
        });

        socket.emit('endCallRes', res);
    })

    socket.on('insertSms', async (param) => {
        let res = await axios.post('http://localhost:8000/api/app/endCall', {
            callNumber: param.callNumber,
            receiveNumber: param.receiveNumber,
            content: param.content
        })

        socket.emit('insertSmsRes', res);
    })

    socket.on('getAllUsers', async (param) => {
        let res = await axios.get('http://localhost:8000/api/app/getAllUsers');

        socket.emit('getAllUsersRes', res);
    })

    // socket.on('favorite', async (param) => {
    //     let res = await axios
    // })
});

server.listen(3000, () => {
    console.log('Server is running');
});
