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
use sys;
use Exception;

sys::import('xaraya.modules.method');

/**
 * hitcount adminapi deleteall function
 */
class DeleteallMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * delete all hitcount items for a module - hook for ('module','remove','API')
     * @param mixed $args ['objectid'] ID of the object (must be the module name here !!)
     * @param mixed $args ['extrainfo'] extra information
     * @return bool|void true on success, false on failure
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        // When called via hooks, we should get the real module name from objectid
        // here, because the current module is probably going to be 'modules' !!!
        if (!isset($objectid) || !is_string($objectid)) {
            $msg = xarML(
                'Invalid #(1) for #(2) function #(3)() in module #(4)',
                'object ID (= module name)',
                'admin',
                'deleteall',
                'Hitcount'
            );
            throw new Exception($msg);
        }

        $modid = xarMod::getRegId($objectid);
        if (empty($modid)) {
            $msg = xarML(
                'Invalid #(1) for #(2) function #(3)() in module #(4)',
                'module ID',
                'admin',
                'deleteall',
                'Hitcount'
            );
            throw new Exception($msg);
        }

        // TODO: re-evaluate this for hook calls !!
        // Security check - important to do this as early on as possible to
        // avoid potential security holes or just too much wasted processing
        if (!xarSecurity::check('DeleteHitcountItem', 1, 'Item', "$objectid:All:All")) {
            return;
        }

        $dbconn = xarDB::getConn();
        $xartable = & xarDB::getTables();
        $hitcounttable = $xartable['hitcount'];

        // FIXME: delete only for a particular module + itemtype (e.g. dd object, articles pubtype, ...)

        $query = "DELETE FROM $hitcounttable
                WHERE module_id = ?";
        $result = $dbconn->Execute($query, [(int) $modid]);
        if (!$result) {
            return;
        }

        // hmmm, I think we'll skip calling more hooks here... :-)

        // Return the extra info
        if (!isset($extrainfo)) {
            $extrainfo = [];
        }
        return $extrainfo;
    }
}
