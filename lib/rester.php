<?php
define('__RESTER__', TRUE);

global $current_rester;

//-------------------------------------------------------------------------------
/// include classes
//-------------------------------------------------------------------------------
require_once dirname(__FILE__) . '/core/common.lib.php';
require_once dirname(__FILE__) . '/core/cfg.class.php';
require_once dirname(__FILE__) . '/core/session.class.php';
require_once dirname(__FILE__) . '/core/rester_response.class.php';
require_once dirname(__FILE__) . '/core/rester_verify.class.php';
require_once dirname(__FILE__) . '/core/rester_config.class.php';
require_once dirname(__FILE__) . '/rester.class.php';

//-------------------------------------------------------------------------------
/// Include lib files
//-------------------------------------------------------------------------------
foreach (glob(dirname(__FILE__) . '/../exten_lib/lib.*.php') as $filename)
{
    include_once $filename;
}

// -----------------------------------------------------------------------------
/// catch 되지 않은 예외에 대한 처리함수
// -----------------------------------------------------------------------------
set_exception_handler(function($e) {
    rester_response::failed(rester_response::code_system_error, 'System error!!');
    rester_response::error_trace(explode("\n",$e));
    rester_response::run();
});

//-------------------------------------------------------------------------------
/// set php.ini
//-------------------------------------------------------------------------------
set_time_limit(0);
ini_set("session.use_trans_sid", 0); // PHPSESSID 를 자동으로 넘기지 않음
ini_set("url_rewriter.tags","");     // 링크에 PHPSESSID 가 따라다니는것을 무력화
ini_set("default_socket_timeout",500);

ini_set("memory_limit", "1000M");     // 메모리 용량 설정.
ini_set("post_max_size","1000M");
ini_set("upload_max_filesize","1000M");

//-------------------------------------------------------------------------------
/// Set the global variables [_POST / _GET / _COOKIE]
/// initial a post and a get variables.
/// if not support short global variables, will be available.
//-------------------------------------------------------------------------------
if (isset($HTTP_POST_VARS) && !isset($_POST))
{
    $_POST   = &$HTTP_POST_VARS;
    $_GET    = &$HTTP_GET_VARS;
    $_SERVER = &$HTTP_SERVER_VARS;
    $_COOKIE = &$HTTP_COOKIE_VARS;
    $_ENV    = &$HTTP_ENV_VARS;
    $_FILES  = &$HTTP_POST_FILES;
    if (!isset($_SESSION))
        $_SESSION = &$HTTP_SESSION_VARS;
}

// force to set register globals off
// http://kldp.org/node/90787
if(ini_get('register_globals'))
{
    foreach($_GET as $key => $value) { unset($$key); }
    foreach($_POST as $key => $value) { unset($$key); }
    foreach($_COOKIE as $key => $value) { unset($$key); }
}

function stripslashes_deep($value)
{
    $value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
    return $value;
}

//-------------------------------------------------------------------------------
/// if get magic quotes gpc is on, set off
/// set magic_quotes_gpc off
//-------------------------------------------------------------------------------
if (get_magic_quotes_gpc())
{

    $_POST = array_map('stripslashes_deep', $_POST);
    $_GET = array_map('stripslashes_deep', $_GET);
    $_COOKIE = array_map('stripslashes_deep', $_COOKIE);
    $_REQUEST = array_map('stripslashes_deep', $_REQUEST);
}

//-------------------------------------------------------------------------------
/// add slashes
//-------------------------------------------------------------------------------
if(is_array($_POST)) array_walk_recursive($_POST, function(&$item){ $item = addslashes($item); });
if(is_array($_GET)) array_walk_recursive($_GET, function(&$item){ $item = addslashes($item); });
if(is_array($_COOKIE)) array_walk_recursive($_COOKIE, function(&$item){ $item = addslashes($item); });

try
{
    // config init
    cfg::init();

    // 오류출력설정
    if (cfg::debug_mode())
        error_reporting(E_ALL ^ (E_NOTICE | E_STRICT | E_WARNING | E_DEPRECATED));
    else
        error_reporting(0);

    // timezone 설정
    date_default_timezone_set(cfg::timezone());

    $rester = new rester(cfg::module(), cfg::proc(), cfg::method(), cfg::request_body());
    $rester->set_public_access();
    $current_rester = $rester;
    rester_response::body($rester->run());
}
catch (Exception $e)
{
    rester_response::failed(sprintf("%02s",$e->getCode()),$e->getMessage());
    rester_response::error_trace(explode("\n",$e->getTraceAsString()));
}
rester_response::run();
