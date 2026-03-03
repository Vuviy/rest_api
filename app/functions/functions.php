<?php

function config()
{
    return require __DIR__ . '/../config/database.php';
}

function rateLimitingConfig()
{
    return require __DIR__ . '/../config/rate_limiting.php';
}