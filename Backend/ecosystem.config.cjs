module.exports = {
    apps: [
        {
            name: "laravel-queue-default",
            script: "artisan",
            interpreter: "php",
            args: "queue:work --sleep=3 --tries=3 --max-time=3600 --timeout=60",
            watch: false,
            instances: 1,
            autorestart: true,
            max_restarts: 10,
            min_uptime: "10s",
            max_memory_restart: "200M",
            error_file: "storage/logs/queue-default-err.log",
            out_file: "storage/logs/queue-default-out.log",
            log_file: "storage/logs/queue-default-combined.log",
            time: true,
            env: {
                NODE_ENV: "production"
            }
        }
    ],
};
