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
use xarModVars;
use xarController;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * hitcount admin main function
 * @extends MethodClass<AdminGui>
 */
class MainMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Add a standard screen upon entry to the module.
     * @return bool|array|void true on success of redirect
     * @see AdminGui::main()
     */
    public function __invoke(array $args = [])
    {
        if (!$this->sec()->checkAccess('ManageHitcount')) {
            return;
        }

        if ($this->mod('modules')->getVar('disableoverview') == 0) {
            return [];
        } else {
            $this->ctl()->redirect($this->mod()->getURL('admin', 'view'));
        }
        return true;
    }
}
