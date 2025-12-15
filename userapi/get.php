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
use Exception;

/**
 * hitcount userapi get function
 * @extends MethodClass<UserApi>
 */
class GetMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * get a hitcount for a specific item
     * @param array<mixed> $args
     * @var mixed $modname name of the module this hitcount is for
     * @var mixed $itemtype item type of the item this hitcount is for
     * @var mixed $objectid ID of the item this hitcount is for
     * @return int|void The corresponding hit count, or void if no hit exists
     * @see UserApi::get()
     */
    public function __invoke(array $args = [])
    {
        // Get arguments from argument array
        extract($args);

        if (!isset($objectid) || !is_numeric($objectid)) {
            $msg = $this->ml(
                'Invalid #(1) for #(2) function #(3)() in module #(4)',
                'object ID',
                'user',
                'get',
                'Hitcount'
            );
            throw new Exception($msg);
        }

        // When called via hooks, modname will be empty, but we get it from the
        // extrainfo or from the current module
        if (empty($modname)) {
            if (isset($extrainfo) && is_array($extrainfo)
                && isset($extrainfo['module']) && is_string($extrainfo['module'])) {
                $modname = $extrainfo['module'];
            } else {
                $modname = $this->req()->getModule();
            }
        }
        $modid = $this->mod()->getRegID($modname);
        if (empty($modid)) {
            $msg = $this->ml(
                'Invalid #(1) for #(2) function #(3)() in module #(4)',
                'module name',
                'user',
                'get',
                'Hitcount'
            );
            throw new Exception($msg);
        }
        if (!isset($itemtype) || !is_numeric($itemtype)) {
            if (isset($extrainfo) && is_array($extrainfo)
                 && isset($extrainfo['itemtype']) && is_numeric($extrainfo['itemtype'])) {
                $itemtype = $extrainfo['itemtype'];
            } else {
                $itemtype = 0;
            }
        }

        // TODO: re-evaluate this for hook calls !!
        // Security check
        if (!$this->sec()->check('ViewHitcountItems', 1, 'Item', "$modname:$itemtype:$objectid")) {
            return;
        }

        // Database information
        $dbconn = $this->db()->getConn();
        $xartable = & $this->db()->getTables();
        $hitcounttable = $xartable['hitcount'];

        // Get items
        $query = "SELECT hits, lasthit 
                FROM $hitcounttable
                WHERE module_id = ?
                  AND itemtype = ?
                  AND itemid = ?";
        $bindvars = [(int) $modid, (int) $itemtype, (int) $objectid];
        $result = $dbconn->Execute($query, $bindvars);
        if (!$result || !$result->first()) {
            return;
        }

        $hits = $result->fields[0];
        $result->close();

        return $hits;
    }
}
