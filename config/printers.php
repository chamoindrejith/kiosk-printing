<?php

return [
    'timeout' => env('PRINTER_TIMEOUT', 30),
    'connect_timeout' => env('PRINTER_CONNECT_TIMEOUT', 10),
    'poll_interval' => env('PRINTER_POLL_INTERVAL', 5),
    'max_retries' => env('PRINTER_MAX_RETRIES', 3),
    'allow_raw_pdf' => env('PRINTER_ALLOW_RAW_PDF', false),
    'default_cups_queue' => env('PRINTER_DEFAULT_CUPS_QUEUE'),
    'status_max_polls' => env('PRINTER_STATUS_MAX_POLLS', 30),
    'unknown_status_limit' => env('PRINTER_UNKNOWN_STATUS_LIMIT', 3),
];