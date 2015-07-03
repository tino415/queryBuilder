<?php

namespace tests;

include "vendor/autoload.php";

class Config {
    public static $config;
}

Config::$config = include __DIR__ . '/config/config.php';
