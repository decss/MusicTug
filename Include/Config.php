<?php
/*
 * Config file
 */

error_reporting(E_ALL ^E_NOTICE ^E_DEPRECATED);
set_time_limit(300);

/*
 * Temp variables
 */
$queryRef = $_SERVER['HTTP_REFERER'];
if (stristr($queryRef, '?') == TRUE) {
    $queryRef = substr_replace($queryRef, null, stripos($queryRef, '?'));
}

$self      = substr_replace($_SERVER['PHP_SELF'], null, 0, 1);
if (stristr($self, '/')) {
    $sf = substr_replace($self, null, 0, strripos($self, '/') + 1);
} else {
    $sf = $self;
}

if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1') {
    $isLocal = true;
} else {
    $isLocal = false;
}

/*
 * Global variables
 */
define('HOST',       preg_replace("~^(www\.)~", null, $_SERVER['HTTP_HOST']));
define('ROOT',       'http://' . HOST);
define('SF',         $sf);
define('SELF',       $self);
define('Q_STR',      $_SERVER['QUERY_STRING']);
define('Q_REF',      $queryRef);
define('Q_REFERER' , $_SERVER['HTTP_REFERER']);
define('Q_MET',      $_SERVER['REQUEST_METHOD']);
define('IS_LOCAL',   $isLocal);

define('TIME',       time());
define('T1',         microtime(true));
define('DIR_S',      DIRECTORY_SEPARATOR);
define('REQUIRE_METHOD', 'require');

unset($queryRef);
unset($self);
unset($isLocal);

/*
 * Autoload function
 */
function __autoload($className) {
    $path = str_replace("_" , DIRECTORY_SEPARATOR, $className) . '.php';
    $path = 'MusicTug/' . $path;
    if (is_readable($path)) {
        require_once $path;
    }
}

/*
 * Debug function
 * @param mixed $text data to explain (via print_r)
 * @param boolean $exit do exit; or not
 * @return void
 */
function dbg($text, $exit = true)
{
    echo '<pre data-exit="' . $exit . '" style="font-size: 11px; line-height: 11px; margin: 0;">' . "\r\n\r\n";
    echo '>>'; 
    print_r($text);
    echo '<<' . "\r\n";
    echo "\r\n" . '</pre>';
    
    if ($exit == true) {
        exit;
    }
}

