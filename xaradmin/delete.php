<?php
/**
 * Hitcount Module
 *
 * @package modules
 * @subpackage hitcount module
 * @category Third Party Xaraya Module
 * @version 2.0.0
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://xaraya.com/index.php/release/177.html
 * @author Hitcount Module Development Team
 */

/**
 * Delete hit counts of module items
 * @param int modid
 * @param int itemtype
 * @param int itemid
 * @param str confirm When empty the confirmation page is shown
 * @return bool|string|void True on success of deletion
 */
function hitcount_admin_delete(array $args = [], $context = null)
{
    // Security Check
    if (!xarSecurity::check('AdminHitcount')) {
        return;
    }

    if (!xarVar::fetch('modid', 'isset', $modid, null, xarVar::DONT_SET)) {
        return;
    }
    if (!xarVar::fetch('itemtype', 'isset', $itemtype, null, xarVar::DONT_SET)) {
        return;
    }
    if (!xarVar::fetch('itemid', 'isset', $itemid, null, xarVar::DONT_SET)) {
        return;
    }
    if (!xarVar::fetch('confirm', 'str:1:', $confirm, '', xarVar::NOT_REQUIRED)) {
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
        $data['authid'] = xarSec::genAuthKey();
        // Return the template variables defined in this function
        return $data;
    }

    if (!xarSec::confirmAuthKey()) {
        return xarController::badRequest('bad_author', $context);
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
    xarController::redirect(xarController::URL('hitcount', 'admin', 'view'), null, $context);
    return true;
}
