<?php

/**
 * @package modules\hitcount
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Hitcount\UserApi;


use Xaraya\Modules\Hitcount\UserApi;
use Xaraya\Modules\MethodClass;
use xarSecurity;
use xarDB;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * hitcount userapi getmodules function
 * @extends MethodClass<UserApi>
 */
class GetmodulesMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * get the list of modules for which we're counting items
     * @return array $array[$modid][$itemtype] = array('items' => $numitems,'hits' => $numhits);
     */
    public function __invoke(array $args = [])
    {
        // Security Check
        if (!$this->sec()->checkAccess('ViewHitcountItems')) {
            return;
        }

        // Database information
        $dbconn = xarDB::getConn();
        $xartable = & xarDB::getTables();
        $hitcounttable = $xartable['hitcount'];
        $modulestable = $xartable['modules'];

        // Get items
        $query = "SELECT m.regid, h.itemtype, COUNT(h.itemid), SUM(h.hits)
                FROM $hitcounttable h INNER JOIN $modulestable m ON m.regid = h.module_id
                GROUP BY m.regid, h.itemtype";

        $result = $dbconn->Execute($query);
        if (!$result) {
            return;
        }

        $modlist = [];
        while (!$result->EOF) {
            [$modid, $itemtype, $numitems, $numhits] = $result->fields;
            $modlist[$modid][$itemtype] = ['items' => $numitems, 'hits' => $numhits];
            $result->MoveNext();
        }
        $result->close();

        return $modlist;
    }
}
