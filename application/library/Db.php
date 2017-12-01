<?php
/**
 * Created by PhpStorm.
 * User: HanYaMin
 * Date: 2017/11/22
 * Time: 15:59
 */
class Db
{
    private static $db;
    public static $dsn='mysql:host=localhost;dbname=mess;';
    public static $user='root';
    public static $pass='root';

    private function __construct(){}
    private function __clone(){}

    public static function getDb(){
        if(is_null(self::$db)){
            self::$db=new PDO(self::$dsn,self::$user,self::$pass,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES'utf8';"));
        }
        return self::$db;
    }
}