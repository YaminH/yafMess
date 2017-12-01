<?php
/**
 * Created by PhpStorm.
 * User: HanYaMin
 * Date: 2017/11/22
 * Time: 13:58
 */
define("APP_PATH", realpath(dirname(__FILE__).'/../'));
$app = new Yaf_Application(APP_PATH."/conf/application.ini");
$app->run();