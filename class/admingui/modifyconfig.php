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
use xarModVars;
use xarController;
use xarTpl;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * hitcount admin modifyconfig function
 * @extends MethodClass<AdminGui>
 */
class ModifyconfigMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * modify configuration
     * @param string phase
     * @return array|string|void
     */
    public function __invoke(array $args = [])
    {
        // Security Check
        if (!$this->checkAccess('AdminHitcount')) {
            return;
        }

        if (!$this->fetch('phase', 'str:1:100', $phase, 'modify', xarVar::NOT_REQUIRED)) {
            return;
        }
        if (!$this->fetch('tab', 'str:1:100', $data['tab'], 'general', xarVar::NOT_REQUIRED)) {
            return;
        }

        $data['module_settings'] = xarMod::apiFunc('base', 'admin', 'getmodulesettings', ['module' => 'hitcount']);
        $data['module_settings']->setFieldList('items_per_page, use_module_alias, module_alias_name, enable_short_urls');
        $data['module_settings']->getItem();

        switch (strtolower($phase)) {
            case 'modify':
            default:
                switch ($data['tab']) {
                    case 'general':
                        // Quick Data Array
                        $data['authid'] = $this->genAuthKey();
                        $data['numitems'] = $this->getModVar('numitems');
                        if (empty($data['numitems'])) {
                            $data['numitems'] = 10;
                        }
                        $data['numstats'] = $this->getModVar('numstats');
                        if (empty($data['numstats'])) {
                            $data['numstats'] = 100;
                        }
                        $data['showtitle'] = $this->getModVar('showtitle');
                        if (!empty($data['showtitle'])) {
                            $data['showtitle'] = 1;
                        }
                        break;
                    case 'tab2':
                        break;
                    case 'tab3':
                        break;
                    default:
                        break;
                }
                break;
            case 'update':
                // Confirm authorisation code
                if (!$this->confirmAuthKey()) {
                    return xarController::badRequest('bad_author', $this->getContext());
                }
                switch ($data['tab']) {
                    case 'general':
                        if (!$this->fetch('countadmin', 'checkbox', $countadmin, false, xarVar::NOT_REQUIRED)) {
                            return;
                        }
                        if (!$this->fetch('numitems', 'int', $numitems, 10, xarVar::NOT_REQUIRED)) {
                            return;
                        }
                        if (!$this->fetch('numstats', 'int', $numstats, 100, xarVar::NOT_REQUIRED)) {
                            return;
                        }
                        if (!$this->fetch('showtitle', 'checkbox', $showtitle, false, xarVar::NOT_REQUIRED)) {
                            return;
                        }

                        $isvalid = $data['module_settings']->checkInput();
                        if (!$isvalid) {
                            $data['context'] ??= $this->getContext();
                            return xarTpl::module('eventhub', 'admin', 'modifyconfig', $data);
                        } else {
                            $itemid = $data['module_settings']->updateItem();
                        }

                        // Update module variables
                        $this->setModVar('countadmin', $countadmin);
                        $this->setModVar('numitems', $numitems);
                        $this->setModVar('numstats', $numstats);
                        $this->setModVar('showtitle', $showtitle);
                        $this->redirect($this->getUrl('admin', 'modifyconfig'));
                        // Return
                        return true;
                    case 'tab2':
                        break;
                    case 'tab3':
                        break;
                    default:
                        break;
                }
                break;
        }

        return $data;
    }
}
