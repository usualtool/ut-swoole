<?php
ini_set("error_reporting","E_ALL & ~E_NOTICE");
ini_set('magic_quotes_gpc',0);
define('UTF_ROOT', dirname(dirname(dirname(dirname(dirname(__FILE__))))));
define('APP_ROOT', dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/app');
define('PUB_PATH', APP_ROOT.'/modules/ut-frame');
define('PUB_TEMP', PUB_PATH.'/skin');
session_start();
require_once UTF_ROOT.'/autoload.php';
use usualtool\Lib\Inc;
use usualtool\Lib\Route;
use usualtool\Lib\Temp;
use usualtool\Lib\Debug;
$config=Inc::GetConfig();
if($request->get):
    foreach ($request->get as $key => $val):
        $_GET[$key] = $val;
    endforeach;
else:
    foreach(Route::Analy($request->server["path_info"]) as $key=>$val):
        $_GET[$key]=$val;
    endforeach;
endif;
$m=empty($_GET["m"]) ? $config["DEFAULT_MOD"] : Inc::SqlCheck($_GET["m"]);
$p=empty($_GET["p"]) ? $config["DEFAULT_PAGE"] : Inc::SqlCheck(str_replace(".php","",$_GET["p"]));
$modpath=APP_ROOT."/modules/".$m;
$endpath=Inc::TempEndPath();
$frontwork=APP_ROOT."/formwork/".$config["FORMWORK_FRONT"];
$adminwork=APP_ROOT."/formwork/".$config["FORMWORK_ADMIN"];
$isdevelop=Inc::Contain("app/dev",Inc::CurPageUrl());
if($config["FORMWORK_ADMIN"]!='0' && $isdevelop):
    $skin=$adminwork."/skin/".$m;
    $cache=$skin."/cache";
elseif($config["FORMWORK_FRONT"]!='0' && !$isdevelop):
    $skin=$frontwork."/skin/".$m;
    $cache=$skin."/cache";
else:
    $skin=$modpath."/skin";
    $cache=$modpath."/cache";
endif;
$app=new Temp(
    $config["TEMPCACHE"],
    $skin."/".$endpath,
    $cache."/".$endpath
);
$app->Runin(array("appname","appurl","module","page"),array($config["APPNAME"],$config["APPURL"],$m,$p));
$app->Runin(array("lang","thelang"),array(explode(",",$config["LANG_OPTION"]),$config["LANG"]));
if(!empty($_COOKIE['Language'])):
    $language=Inc::SqlCheck($_COOKIE['Language']);
else:
    if($config["LANG"]=="big5"):
        $language="zh";
        setcookie("Language","zh");
        setcookie("chinaspeak","big5");
    else:
        $language=$config["LANG"];
        setcookie("Language",$config["LANG"]);
        setcookie("chinaspeak","");
    endif;
endif;
$app->Runin(array("editor"),array($config["EDITOR"]));
$app->Runin("pubtemp",PUB_TEMP."/front");
$app->Runin("formwork",$frontwork."/skin/ut-frame/front");
$modfile=$modpath."/front/".$p.".php";
if(Inc::SearchFile($modfile)):
    require_once $modfile;
else:
    echo"Swoole Error:No Page.";
endif;
if($config["DEBUG"]):
    Debug::Debug($config["DEBUG_BAR"]);
endif;
