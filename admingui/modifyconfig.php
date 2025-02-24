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
     * @see AdminGui::modifyconfig()
     */
    public function __invoke(array $args = [])
    {
        // Security Check
        if (!$this->sec()->checkAccess('AdminHitcount')) {
            return;
        }

        $this->var()->find('phase', $phase, 'str:1:100', 'modify');
        $this->var()->find('tab', $data['tab'], 'str:1:100', 'general');

        $data['module_settings'] = $this->mod()->apiFunc('base', 'admin', 'getmodulesettings', ['module' => 'hitcount']);
        $data['module_settings']->setFieldList('items_per_page, use_module_alias, module_alias_name, enable_short_urls');
        $data['module_settings']->getItem();

        switch (strtolower($phase)) {
            case 'modify':
            default:
                switch ($data['tab']) {
                    case 'general':
                        // Quick Data Array
                        $data['authid'] = $this->sec()->genAuthKey();
                        $data['numitems'] = $this->mod()->getVar('numitems');
                        if (empty($data['numitems'])) {
                            $data['numitems'] = 10;
                        }
                        $data['numstats'] = $this->mod()->getVar('numstats');
                        if (empty($data['numstats'])) {
                            $data['numstats'] = 100;
                        }
                        $data['showtitle'] = $this->mod()->getVar('showtitle');
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
                if (!$this->sec()->confirmAuthKey()) {
                    return $this->ctl()->badRequest('bad_author');
                }
                switch ($data['tab']) {
                    case 'general':
                        $this->var()->find('countadmin', $countadmin, 'checkbox', false);
                        $this->var()->find('numitems', $numitems, 'int', 10);
                        $this->var()->find('numstats', $numstats, 'int', 100);
                        $this->var()->find('showtitle', $showtitle, 'checkbox', false);

                        $isvalid = $data['module_settings']->checkInput();
                        if (!$isvalid) {
                            $data['context'] ??= $this->getContext();
                            return $this->tpl()->module('eventhub', 'admin', 'modifyconfig', $data);
                        } else {
                            $itemid = $data['module_settings']->updateItem();
                        }

                        // Update module variables
                        $this->mod()->setVar('countadmin', $countadmin);
                        $this->mod()->setVar('numitems', $numitems);
                        $this->mod()->setVar('numstats', $numstats);
                        $this->mod()->setVar('showtitle', $showtitle);
                        $this->ctl()->redirect($this->mod()->getURL('admin', 'modifyconfig'));
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
