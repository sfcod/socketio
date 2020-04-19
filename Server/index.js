const args = require('./bundles/args');
const server = require('./bundles/server');
const io = require('socket.io')(server);
const fs = require('fs');
const auth = require('./bundles/auth');
const redis = require("redis");
const subscriber = redis.createClient(JSON.parse(args.sub));
const publisher = redis.createClient(JSON.parse(args.pub));
const RedisIO = require('./bundles/redis-io');

// Create log dir
if (!fs.existsSync(args.runtime)) {
    fs.mkdirSync(args.runtime);
}

// Add auth middleware
io.use(auth);

// Run redis.io
(new RedisIO(args.nsp, io, subscriber, publisher, args.channels.split(',')))
    .listen();


server.listen(args.server.split(':')[1], args.server.split(':')[0]);

