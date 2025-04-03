<?php
require_once __DIR__ . '/App.php';
require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/Lib.php';
require_once __DIR__ . '/Model.php';

use JayDream\Config;
use JayDream\App;

Config::init();

$jd = new App();