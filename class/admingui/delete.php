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
use Xaraya\Modules\Hitcount\AdminApi;
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
     * @see AdminGui::delete()
     */
    public function __invoke(array $args = [])
    {
        /** @var AdminApi $adminapi */
        $adminapi = $this->adminapi();
        // Security Check
        if (!$this->sec()->checkAccess('AdminHitcount')) {
            return;
        }

        if (!$this->var()->check('modid', $modid)) {
            return;
        }
        if (!$this->var()->check('itemtype', $itemtype)) {
            return;
        }
        if (!$this->var()->check('itemid', $itemid)) {
            return;
        }
        if (!$this->var()->find('confirm', $confirm, 'str:1:', '')) {
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
                $modinfo = $this->mod()->getInfo($modid);
                if (empty($itemtype)) {
                    $data['modname'] = ucwords($modinfo['displayname']);
                } else {
                    // Get the list of all item types for this module (if any)
                    try {
                        $mytypes = $this->mod()->apiFunc($modinfo['name'], 'user', 'getitemtypes');
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
            $data['authid'] = $this->sec()->genAuthKey();
            // Return the template variables defined in this function
            return $data;
        }

        if (!$this->sec()->confirmAuthKey()) {
            return $this->ctl()->badRequest('bad_author');
        }
        if (!$adminapi->delete(['modid' => $modid,
                'itemtype' => $itemtype,
                'itemid' => $itemid,
                'confirm' => $confirm, ]
        )) {
            return;
        }
        $this->ctl()->redirect($this->mod()->getURL('admin', 'view'));
        return true;
    }
}
