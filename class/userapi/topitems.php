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
use xarSession;
use xarMod;
use xarSecurity;
use xarDB;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * hitcount userapi topitems function
 * @extends MethodClass<UserApi>
 */
class TopitemsMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * get the list of items with top N hits for a module
     * @param array<mixed> $args
     * @var mixed $modname name of the module you want items from
     * @var mixed $itemtype item type of the items (only 1 type supported per call)
     * @var mixed $numitems number of items to return
     * @var mixed $startnum start at this number (1-based)
     * @return array|void Array('itemid' => $itemid, 'hits' => $hits)
     */
    public function __invoke(array $args = [])
    {
        // Get arguments from argument array
        extract($args);

        // Argument check
        if (!isset($modname)) {
            xarSession::setVar('errormsg', _MODARGSERROR);
            return;
        }
        $modid = xarMod::getRegId($modname);
        if (empty($modid)) {
            xarSession::setVar('errormsg', _MODARGSERROR);
            return;
        }
        if (empty($itemtype)) {
            $itemtype = 0;
        }

        // Security check
        if (!xarSecurity::check('ViewHitcountItems', 1, 'Item', "$modname:$itemtype:All")) {
            return;
        }

        // Database information
        $dbconn = $this->db()->getConn();
        $xartable = & $this->db()->getTables();
        $hitcounttable = $xartable['hitcount'];

        // Get items
        $query = "SELECT itemid, hits
                FROM $hitcounttable
                WHERE module_id = ?
                  AND itemtype = ?
                ORDER BY hits DESC";
        $bindvars = [(int) $modid, (int) $itemtype];

        if (!isset($numitems) || !is_numeric($numitems)) {
            $numitems = 10;
        }
        if (!isset($startnum) || !is_numeric($startnum)) {
            $startnum = 1;
        }

        //$result = $dbconn->Execute($query);
        $result = $dbconn->SelectLimit($query, $numitems, $startnum - 1, $bindvars);
        if (!$result) {
            return;
        }

        $topitems = [];
        while (!$result->EOF) {
            [$id, $hits] = $result->fields;
            $topitems[] = ['itemid' => $id, 'hits' => $hits];
            $result->MoveNext();
        }
        $result->close();

        return $topitems;
    }
}
