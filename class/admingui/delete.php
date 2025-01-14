<?php

/**
 * @package modules\hitcount
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Hitcount\AdminGui;


use Xaraya\Modules\Hitcount\AdminGui;
use Xaraya\Modules\MethodClass;
use xarSecurity;
use xarVar;
use xarMod;
use xarSec;
use xarController;
use sys;
use Exception;

sys::import('xaraya.modules.method');

/**
 * hitcount admin delete function
 * @extends MethodClass<AdminGui>
 */
class DeleteMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Delete hit counts of module items
     * @param array<mixed> $args
     *     int modid
     *     int itemtype
     *     int itemid
     *     str confirm When empty the confirmation page is shown
     * @return bool|string|void True on success of deletion
     */
    public function __invoke(array $args = [])
    {
        // Security Check
        if (!$this->checkAccess('AdminHitcount')) {
            return;
        }

        if (!$this->fetch('modid', 'isset', $modid, null, xarVar::DONT_SET)) {
            return;
        }
        if (!$this->fetch('itemtype', 'isset', $itemtype, null, xarVar::DONT_SET)) {
            return;
        }
        if (!$this->fetch('itemid', 'isset', $itemid, null, xarVar::DONT_SET)) {
            return;
        }
        if (!$this->fetch('confirm', 'str:1:', $confirm, '', xarVar::NOT_REQUIRED)) {
            return;
        }

        // Check for confirmation.
        if (empty($confirm)) {
            $data = [];
            $data['modid'] = $modid;
            $data['itemtype'] = $itemtype;
            $data['itemid'] = $itemid;

            $what = '';
            if (!empty($modid)) {
                $modinfo = xarMod::getInfo($modid);
                if (empty($itemtype)) {
                    $data['modname'] = ucwords($modinfo['displayname']);
                } else {
                    // Get the list of all item types for this module (if any)
                    try {
                        $mytypes = xarMod::apiFunc($modinfo['name'], 'user', 'getitemtypes');
                    } catch (Exception $e) {
                        $mytypes = [];
                    }
                    if (isset($mytypes) && !empty($mytypes[$itemtype])) {
                        $data['modname'] = ucwords($modinfo['displayname']) . ' ' . $itemtype . ' - ' . $mytypes[$itemtype]['label'];
                    } else {
                        $data['modname'] = ucwords($modinfo['displayname']) . ' ' . $itemtype;
                    }
                }
            }
            // Generate a one-time authorisation code for this operation
            $data['authid'] = $this->genAuthKey();
            // Return the template variables defined in this function
            return $data;
        }

        if (!$this->confirmAuthKey()) {
            return xarController::badRequest('bad_author', $this->getContext());
        }
        if (!xarMod::apiFunc(
            'hitcount',
            'admin',
            'delete',
            ['modid' => $modid,
                'itemtype' => $itemtype,
                'itemid' => $itemid,
                'confirm' => $confirm, ]
        )) {
            return;
        }
        $this->redirect($this->getUrl('admin', 'view'));
        return true;
    }
}
