<?php
/**
 *  Crappy memcache client.
 *  http://forum.slicehost.com/comments.php?DiscussionID=916
 */
define("__MEMHOST","localhost");
define("__MEMPORT",11211);
class clsMem extends Memcache {
    static private $m_objMem = NULL;
    static function getMem() {
        if (self::$m_objMem == NULL) {
            self::$m_objMem = new Memcache;
            // connect to the memcached on some 
            //host __MEMHOST running it om __MEMPORT
            self::$m_objMem->connect(__MEMHOST, __MEMPORT) 
                        or die ("Dave's not here, man.");
        }
        return self::$m_objMem;
    }
    static function cache_or_get($cachekey, $create_item_func, $timeout=600){
        if ($cacheitem = clsMem::getMem()->get($cachekey) ) {
            return $cacheitem;
        }
        else {
            $cacheitem = $create_item_func();
            if (clsMem::getMem()->set($cachekey, $cacheitem, false, $timeout) ) {
                return $cacheitem;
            }
            else {
                //maybe we should log something here, or send an email to an administrator
                return $cacheitem;
            }
        }
    }
}
