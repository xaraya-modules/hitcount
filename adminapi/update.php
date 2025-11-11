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

use Xaraya\Modules\Hitcount\AdminApi;
use Xaraya\Modules\Hitcount\UserApi;
use Xaraya\Modules\MethodClass;
use Exception;

/**
 * hitcount adminapi update function
 * @extends MethodClass<AdminApi>
 */
class UpdateMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * update a hitcount item - used by display hook hitcount_user_display
     * @param array<mixed> $args
     * @var mixed $modname name of the calling module (see _user_display)
     * @var mixed $itemtype optional item type for the item (or in extrainfo)
     * @var mixed $objectid ID of the object
     * @var mixed $extrainfo may contain itemtype
     * @var mixed $hits (optional) hit count for the item
     * @return int|void The new hitcount for this item, or void on failure
     * @see AdminApi::update()
     */
    public function __invoke(array $args = [])
    {
        extract($args);
        /** @var UserApi $userapi */
        $userapi = $this->userapi();
        /** @var AdminApi $adminapi */
        $adminapi = $this->adminapi();

        if (!isset($objectid) || !is_numeric($objectid)) {
            $msg = $this->ml(
                'Invalid #(1) for #(2) function #(3)() in module #(4)',
                'object ID',
                'admin',
                'update',
                'Hitcount'
            );
            throw new Exception($msg);
        }

        // When called via hooks, modname will be empty, but we get it from the
        // extrainfo or from the current module
        if (empty($modname) || !is_string($modname)) {
            if (isset($extrainfo) && is_array($extrainfo)
                && isset($extrainfo['module']) && is_string($extrainfo['module'])) {
                $modname = $extrainfo['module'];
            } else {
                $modname = $this->mod()->getName();
            }
        }
        $modid = $this->mod()->getRegID($modname);
        if (empty($modid)) {
            $msg = $this->ml(
                'Invalid #(1) for #(2) function #(3)() in module #(4)',
                'module name',
                'admin',
                'update',
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
        // Security check - important to do this as early on as possible to
        // avoid potential security holes or just too much wasted processing
        if (!$this->sec()->check('ReadHitcountItem', 1, 'Item', "$modname:$itemtype:$objectid")) {
            return;
        }

        if (!$this->mod()->apiLoad('hitcount', 'user')) {
            return;
        }

        // get current hit count
        $oldhits = $userapi->get(
            ['objectid' => $objectid,
                'itemtype' => $itemtype,
                'modname' => $modname, ]
        );

        // create the item if necessary
        if (!isset($oldhits)) {
            $hcid = $adminapi->create(
                ['objectid' => $objectid,
                    'itemtype' => $itemtype,
                    'modname' => $modname, ]
            );
            if (!isset($hcid)) {
                return; // throw back whatever it was that failed
            }
        }

        $dbconn = $this->db()->getConn();
        $xartable = & $this->db()->getTables();
        $hitcounttable = $xartable['hitcount'];

        // set to the new hit count
        $bindvars = [];
        if (!empty($hits) && is_numeric($hits)) {
            $bhits = $hits;
        } else {
            $bhits = 'hits + 1';
            $hits = $oldhits + 1;
        }
        $query = "UPDATE $hitcounttable
                  SET hits = $bhits, lasthit = " . time()
                  . " WHERE module_id = ?
                  AND itemtype = ?
                  AND itemid = ?";
        $bindvars = [(int) $modid, (int) $itemtype, (int) $objectid];
        $result = $dbconn->Execute($query, $bindvars);
        if (!$result) {
            return;
        }

        // Return the new hitcount (give or take a few other hits in the meantime)
        return $hits;
    }
}
