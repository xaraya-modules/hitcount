<?php

/**
 * @package modules\hitcount
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Hitcount\UserGui;


use Xaraya\Modules\Hitcount\UserGui;
use Xaraya\Modules\Hitcount\UserApi;
use Xaraya\Modules\Hitcount\AdminApi;
use Xaraya\Modules\MethodClass;
use xarVar;
use xarMod;
use xarSecurity;
use xarModVars;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * hitcount user display function
 * @extends MethodClass<UserGui>
 */
class DisplayMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * add a hit for a specific item, and display the hitcount (= display hook)
     * (use $this->var()->setCached('Hooks.hitcount','save', 1) to tell hitcount *not*
     * to display the hit count, but to save it in 'Hooks.hitcount', 'value')
     * @param array<mixed> $args
     * @var mixed $objectid ID of the item this hitcount is for
     * @var mixed $extrainfo may contain itemtype
     * @return string|void output with hitcount information
     * @see UserGui::display()
     */
    public function __invoke(array $args = [])
    {
        extract($args);
        /** @var UserApi $userapi */
        $userapi = $this->userapi();
        /** @var AdminApi $adminapi */
        $adminapi = $this->adminapi();

        // Load API
        if (!$this->mod()->apiLoad('hitcount', 'admin')) {
            return;
        }

        // When called via hooks, modname will be empty, but we get it from the
        // extrainfo or from the current module
        if (empty($args['modname']) || !is_string($args['modname'])) {
            if (isset($extrainfo) && is_array($extrainfo) &&
                isset($extrainfo['module']) && is_string($extrainfo['module'])) {
                $args['modname'] = $extrainfo['module'];
            } else {
                $args['modname'] = $this->mod()->getName();
            }
        }
        if (!isset($args['itemtype']) || !is_numeric($args['itemtype'])) {
            if (isset($extrainfo) && is_array($extrainfo) &&
                 isset($extrainfo['itemtype']) && is_numeric($extrainfo['itemtype'])) {
                $args['itemtype'] = $extrainfo['itemtype'];
            } else {
                $args['itemtype'] = 0;
            }
        }
        if ($this->var()->isCached('Hooks.hitcount', 'nocount') ||
            ($this->sec()->checkAccess('AdminHitcount', 0) && $this->mod()->getVar('countadmin') == false)) {
            $hitcount = $userapi->get($args);
        } else {
            $hitcount = $adminapi->update($args);
        }

        // @fixme: this function should return output to a template, not directly as a string!
        if (isset($hitcount)) {
            // Display current hitcount or set the cached variable
            if (!$this->var()->isCached('Hooks.hitcount', 'save') ||
                $this->var()->getCached('Hooks.hitcount', 'save') == false) {
                return '(' . $hitcount . ' ' . $this->ml('Reads') . ')';
            } else {
                $this->var()->setCached('Hooks.hitcount', 'value', $hitcount);
            }
        }

        return '';
    }
}
