<?php

/**
 * @package modules\hitcount
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Hitcount\AdminApi;

use Xaraya\Modules\MethodClass;
use xarMod;
use xarSecurity;
use xarDB;
use xarModHooks;
use sys;
use Exception;

sys::import('xaraya.modules.method');

/**
 * hitcount adminapi create function
 */
class CreateMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * create a new hitcount item - hook for ('item','create','API')
     * @param mixed $args ['objectid'] ID of the object
     * @param mixed $args ['extrainfo'] extra information
     * @param mixed $args ['modname'] name of the calling module (not used in hook calls)
     * @param mixed $args ['itemtype'] optional item type for the item (not used in hook calls)
     * @param mixed $args ['hits'] optional hit count for the item (not used in hook calls)
     * @return int|void hitcount item ID on success, void on failure
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        if (!isset($objectid) || !is_numeric($objectid)) {
            $msg = xarML(
                'Invalid #(1) for #(2) function #(3)() in module #(4)',
                'object ID',
                'admin',
                'create',
                'Hitcount'
            );
            throw new Exception($msg);
        }

        // When called via hooks, modname will be empty, but we get it from the
        // extrainfo or from the current module
        if (empty($modname) || !is_string($modname)) {
            if (isset($extrainfo) && is_array($extrainfo) &&
                isset($extrainfo['module']) && is_string($extrainfo['module'])) {
                $modname = $extrainfo['module'];
            } else {
                $modname = xarMod::getName();
            }
        }
        $modid = xarMod::getRegId($modname);
        if (empty($modid)) {
            $msg = xarML(
                'Invalid #(1) for #(2) function #(3)() in module #(4)',
                'module name',
                'admin',
                'create',
                'Hitcount'
            );
            throw new Exception($msg);
        }

        if (!isset($itemtype) || !is_numeric($itemtype)) {
            if (isset($extrainfo['itemtype']) && is_numeric($extrainfo['itemtype'])) {
                $itemtype = $extrainfo['itemtype'];
            } else {
                $itemtype = 0;
            }
        }

        // TODO: re-evaluate this for hook calls !!
        // Security check - important to do this as early on as possible to
        // avoid potential security holes or just too much wasted processing
        if (!xarSecurity::check('ReadHitcountItem', 1, 'Item', "$modname:$itemtype:$objectid")) {
            return;
        }

        $dbconn = xarDB::getConn();
        $xartable = & xarDB::getTables();
        $hitcounttable = $xartable['hitcount'];

        // Get a new hitcount ID
        $nextId = $dbconn->GenId($hitcounttable);
        // Create new hitcount
        if (!isset($hits) || !is_numeric($hits)) {
            if (isset($extrainfo['hits']) && is_numeric($extrainfo['hits'])) {
                $hits = $extrainfo['hits'];
            } else {
                $hits = 0;
            }
        }
        $query = "INSERT INTO $hitcounttable(id,
                                           module_id,
                                           itemtype,
                                           itemid,
                                           hits,
                                           lasthit)
                VALUES (?,?,?,?,?,?)";
        $bindvars = [$nextId, $modid, $itemtype, $objectid, $hits, time()];

        $result = $dbconn->Execute($query, $bindvars);
        if (!$result) {
            return;
        }

        $hcid = $dbconn->PO_Insert_ID($hitcounttable, 'id');

        // hmmm, I think we'll skip calling more hooks here... :-)
        //xarModHooks::call('item', 'create', $hcid, 'id');

        // Return the extra info with the id of the newly created item
        // (not that this will be of any used when called via hooks, but
        // who knows where else this might be used)
        if (!isset($extrainfo)) {
            $extrainfo = [];
        }
        $extrainfo['hcid'] = $hcid;
        return $extrainfo;
    }
}
