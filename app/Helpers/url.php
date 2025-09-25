<?php

function asset(string $path): string
{
    return $_ENV['APP_URL'] . '/public/' . ltrim($path, '/');
}
