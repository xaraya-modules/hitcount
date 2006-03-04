<?php
/**
 * Hitcount
 *
 * @package modules
 * @copyright (C) 2002-2005 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Hitcount Module
 * @link http://xaraya.com/index.php/release/177.html
 * @author Hitcount Module Development Team
 */
 
/**
 * modify configuration
 */
function hitcount_admin_modifyconfig()
{
    // Security Check
    if (!xarSecurityCheck('AdminHitcount')) return;

    if (!xarVarFetch('phase', 'str:1:100', $phase, 'modify', XARVAR_NOT_REQUIRED)) return;

    switch (strtolower($phase)) {
        case 'modify':
        default: 
            
            // Quick Data Array
            $data['authid'] = xarSecGenAuthKey();
            $data['numitems'] = xarModGetVar('hitcount','numitems');
            if (empty($data['numitems'])) {
                $data['numitems'] = 10;
            }
            $data['numstats'] = xarModGetVar('hitcount','numstats');
            if (empty($data['numstats'])) {
                $data['numstats'] = 100;
            }
            $data['showtitle'] = xarModGetVar('hitcount','showtitle');
            if (!empty($data['showtitle'])) {
                $data['showtitle'] = 1;
            }
            break;

        case 'update':
            if (!xarVarFetch('countadmin', 'checkbox', $countadmin, false, XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('numitems', 'int', $numitems, 10, XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('numstats', 'int', $numstats, 100, XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('showtitle', 'checkbox', $showtitle, false, XARVAR_NOT_REQUIRED)) return;
            // Confirm authorisation code
            if (!xarSecConfirmAuthKey()) return; 
            // Update module variables
            xarModSetVar('hitcount', 'countadmin', $countadmin);
            xarModSetVar('hitcount', 'numitems', $numitems);
            xarModSetVar('hitcount', 'numstats', $numstats);
            xarModSetVar('hitcount', 'showtitle', $showtitle);
            xarResponseRedirect(xarModURL('hitcount', 'admin', 'modifyconfig')); 
            // Return
            return true;

            break;
    } 

    return $data;
} 

?>