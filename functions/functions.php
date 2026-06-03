<?php

function configDb()
{
    return require __DIR__ . '/../config/database.php';
}

function rateLimitingConfig()
{
    return require __DIR__ . '/../config/rate_limiting.php';
}

function versioningConfig(): array
{
    return require __DIR__ . '/../config/versioning.php';
}
