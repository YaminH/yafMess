<?php
/**
 * Created by PhpStorm.
 * User: HanYaMin
 * Date: 2017/11/27
 * Time: 10:22
 */
$memcache = new Memcache();
$memcache->connect('localhost',11211) or die("Could not connect");
//$memcache->set('key','test');
//$get_value=$memcache->get('key');
$memcache->flush();

class Pa{
    protected $aa;
    function __construct()
    {
        $this->aa=11;
        echo "11";
        echo "Pa";
    }
}
class Ca extends Pa {
    public function __construct()
    {
        echo "22";
    }

    public function show(){
        echo "show";
    }
}
$tmp=new Ca();
$tmp->show();