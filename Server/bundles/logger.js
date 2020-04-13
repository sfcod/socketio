const winston = require('winston');
const args = require('./args');
const isDevEnv = (process.env.NODE_ENV || '') === 'dev'

const logger = new (winston.Logger)({
    transports: [
        new winston.transports.File({
            filename: args.runtime + '/all-logs.log',
            level: isDevEnv ? 'info' : 'error',
            handleExceptions: true,
            json: true,
            maxsize: 5242880, // 5MB
            maxFiles: 5,
            colorize: false,
        }),
        // new winston.transports.Console({
        //     level: isDevEnv ? 'info' : 'error',
        //     handleExceptions: true,
        //     json: false,
        //     colorize: true,
        // })
    ],
    exceptionHandlers: [
        new winston.transports.File({filename: args.runtime + '/exceptions.log'})
    ],
    exitOnError: false
});

module.exports = logger;
