<?php
require_once __DIR__ . '/App.php';
require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/Lib.php';
require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/Service.php';
require_once __DIR__ . '/File.php';
require_once __DIR__ . '/Session.php';
require_once __DIR__ . '/Http.php';
require_once __DIR__ . '/lib/jwt/JWT.php';
require_once __DIR__ . '/lib/jwt/ExpiredException.php';
require_once __DIR__ . '/lib/jwt/BeforeValidException.php';
require_once __DIR__ . '/lib/jwt/SignatureInvalidException.php';

require_once __DIR__ . '/provider/cloudflare/Cloudflare.php';



use JayDream\Config;
use JayDream\Session;

Config::init();
Session::init();

if(Config::$framework == "gnuboard") {
    include_once (Config::$ROOT . "/common.php");
}