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
 * hitcount userapi getitems function
 * @extends MethodClass<UserApi>
 */
class GetitemsMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * get a hitcount for a list of items
     * @param array<mixed> $args
     * @var mixed $modname name of the module you want items from, or
     * @var mixed $modid module id you want items from
     * @var mixed $itemtype item type of the items (only 1 type supported per call)
     * @var mixed $itemids array of item IDs
     * @var mixed $sort string sort by itemid (default) or numhits
     * @var mixed $sortorder string sort order DESC (default) or ASC
     * @var mixed $numitems number of items to return
     * @var mixed $startnum start at this number (1-based)
     * @return array|void $array[$itemid] = $hits;
     */
    public function __invoke(array $args = [])
    {
        // Get arguments from argument array
        extract($args);

        // Argument check
        if (!isset($modname) && !isset($modid)) {
            xarSession::setVar('errormsg', _MODARGSERROR);
            return;
        }
        if (!empty($modname)) {
            $modid = xarMod::getRegId($modname);
        }
        if (empty($modid)) {
            xarSession::setVar('errormsg', _MODARGSERROR);
            return;
        } elseif (empty($modname)) {
            $modinfo = xarMod::getInfo($modid);
            $modname = $modinfo['name'];
        }
        if (empty($itemtype)) {
            $itemtype = 0;
        }
        if (empty($sort)) {
            $sort = 'itemid';
        }
        if (empty($sortorder)) {
            $sortorder = 'DESC';
        }
        if (empty($startnum)) {
            $startnum = 1;
        }

        if (!isset($itemids)) {
            $itemids = [];
        }

        // Security check
        if (count($itemids) > 0) {
            foreach ($itemids as $itemid) {
                if (!xarSecurity::check('ViewHitcountItems', 1, 'Item', "$modname:$itemtype:$itemid")) {
                    return;
                }
            }
        } else {
            if (!xarSecurity::check('ViewHitcountItems', 1, 'Item', "$modname:$itemtype:All")) {
                return;
            }
        }

        // Database information
        $dbconn = xarDB::getConn();
        $xartable = & xarDB::getTables();
        $hitcounttable = $xartable['hitcount'];

        // Get items
        $bindvars = [];
        $query = "SELECT itemid, hits, lasthit 
                FROM $hitcounttable
                WHERE module_id = ?
                  AND itemtype = ?";
        $bindvars[] = (int) $modid;
        $bindvars[] = (int) $itemtype;
        if (count($itemids) > 0) {
            $bindmarkers = '?' . str_repeat(',?', count($itemids) - 1);
            $query .= " AND itemid IN ($bindmarkers)";
            foreach ($itemids as $itemid) {
                $bindvars[] = (int) $itemid;
            }
        }
        if ($sort == 'numhits') {
            $query .= " ORDER BY hits $sortorder, itemid DESC";
        } else {
            $query .= " ORDER BY itemid $sortorder";
        }

        if (!empty($numitems) && !empty($startnum)) {
            $result = $dbconn->SelectLimit($query, $numitems, $startnum - 1, $bindvars);
        } else {
            $result = $dbconn->Execute($query, $bindvars);
        }
        if (!$result) {
            return;
        }

        $hitlist = [];
        while (!$result->EOF) {
            [$id, $hits] = $result->fields;
            $hitlist[$id] = $hits;
            $result->MoveNext();
        }
        $result->close();

        return $hitlist;
    }
}