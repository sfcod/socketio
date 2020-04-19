const jwt = require('jsonwebtoken');
const logger = require('./logger');
const fs = require('fs');
const path = require('path');

module.exports = (socket, next) => {
    const token = socket.handshake.query[`${process.env.SOCKET_IO_AUTH_TOKEN_NAME || 'token'}`];
    const callback = (err, decoded) => {
        if (err) {
            logger.info("auth", err);

            return next(err);
        }
        logger.info("auth", decoded);

        return next();
    };

    if (process.env.SOCKET_IO_AUTH_TOKEN_PATH) {
        const cert = fs.readFileSync(path.join(process.cwd(), process.env.SOCKET_IO_AUTH_TOKEN_PATH));
        jwt.verify(token, cert, callback);
    } else if (process.env.SOCKET_IO_AUTH_TOKEN_VALUE) {
        jwt.verify(token, process.env.SOCKET_IO_AUTH_TOKEN_VALUE, callback);
    } else if (token) {
        const err = new Error('SOCKET_IO_AUTH_TOKEN_PATH/SOCKET_IO_AUTH_TOKEN_VALUE was not defined.');
        logger.info("auth", err.message);

        next(err);
    } else {
        next();
    }
}
