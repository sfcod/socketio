const AccessIO = require('./access-io');
const logger = require('./logger');
const util = require('util');

class RedisIO {
    constructor(nsp, io, sub, pub, channels) {
        this.nsp = nsp;
        this.io = io;
        this.sub = sub;
        this.pub = pub;
        this.channels = channels;
    }

    /**
     * Get event from data
     * @param data
     */
    parseEvent(data) {
        return Object.assign({name: '', data: {}}, JSON.parse(data));
    };

    /**
     * Init all events on '*'
     * @param socket
     * @return {*}
     */
    wildcard(socket) {
        let Emitter = require('events').EventEmitter;
        let emit = Emitter.prototype.emit;
        let onevent = socket.onevent;
        socket.onevent = function (packet) {
            let args = packet.data || [];
            onevent.call(this, packet);    // original call
            emit.apply(this, ["*"].concat(args));      // additional call to catch-all
        };
        return socket;
    };

    getIoNsp(channel) {
        return channel.replace(this.nsp, '');
    }

    /**
     * on connection
     * @param channel
     * @param data
     */
    on(channel, data) {
        let nsp = '/' + this.getIoNsp(channel);
        let nspio = this.io.of(nsp);

        nspio.on('connection', (socket) => {
            socket.access = new AccessIO(socket);

            socket = this.wildcard(socket);

            const token = socket.handshake.query[`${process.env.SOCKET_IO_AUTH_TOKEN_NAME || 'token'}`] || undefined;
            const event = (name) => {
                const data = token ? {...{socketId: socket.id}, token} : {socketId: socket.id};

                this.pub.publish(channel + '.io', JSON.stringify({
                    name: name,
                    data: data
                }));

                logger.info('event', {
                    name: name,
                    data: data
                });
            }

            event('connection');

            socket.on('disconnect', () => {
                event('disconnect');
            });

            socket.on('*', (name, data = {}) => {
                data = {...data, socketId: socket.id};
                if (true === socket.access.can(name)) {
                    // Moved to controlled php handlers
                    // switch (name) {
                    //     case 'join' :
                    //         socket.join(data.room);
                    //         break;
                    //     case 'leave':
                    //         socket.leave(data.room);
                    //         break;
                    // }
                    // Send to php server all events
                    data = token ? {...data, token} : data;
                    this.pub.publish(channel + '.io', JSON.stringify({
                        name: name,
                        data: data
                    }));

                    logger.info('wildcard', {
                        name: name,
                        data: data,
                        nsp: nsp,
                    });
                } else {
                    throw new Error(util.format('Socket %s "can not get access/speed limit", nsp: %s, name: %s, data: %s', socket.id, nsp, name, JSON.stringify(data)));
                }
            });
        });
    };

    /**
     * Emit event to exist connection
     * @param channel
     * @param data
     */
    emit(channel, data) {
        let event = this.parseEvent(data),
            room = event.data.room,
            nsp = '';

        switch (event.name) {
            case 'join' :
                nsp = event.data.socketId.indexOf('#') === -1 ? "/" : event.data.socketId.split('#')[0];
                if (event.data.socketId && this.io.of(nsp).connected[event.data.socketId]) {
                    this.io.of(nsp).connected[event.data.socketId].join(room);
                }
                break;
            case 'leave':
                nsp = event.data.socketId.indexOf('#') === -1 ? "/" : event.data.socketId.split('#')[0];
                if (event.data.socketId && this.io.of(nsp).connected[event.data.socketId]) {
                    this.io.of(nsp).connected[event.data.socketId].leave(room);
                }
                break;
            default:
                nsp = '/' + this.getIoNsp(channel);
                if (room) {
                    delete event.data.room;
                    delete event.data.socketId;
                    this.io.of(nsp).to(room).emit(event.name, event.data);
                } else {
                    this.io.of(nsp).emit(event.name, event.data);
                }
        }

        logger.info('emit', {
            nsp: nsp,
            name: event.name,
            room: room || '',
            data: event.data
        });
    };

    /**
     * List redis/socket.io
     */
    listen() {
        for (let i = 0; i < this.channels.length; i++) {
            this.sub.subscribe(this.channels[i]);
            this.on(this.channels[i], JSON.stringify({}));
        }

        this.sub.on("message", (channel, data) => {
            this.emit(channel, data);
        });
    }

}

module.exports = RedisIO;
