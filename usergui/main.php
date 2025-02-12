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
use Xaraya\Modules\MethodClass;
use xarSecurity;
use xarMod;
use xarModVars;
use xarController;
use xarTpl;
use xarVar;
use sys;
use Exception;

sys::import('xaraya.modules.method');

/**
 * hitcount user main function
 * @extends MethodClass<UserGui>
 */
class MainMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * the main user function (nothing interesting here - might be removed)
     * @see UserGui::main()
     */
    public function __invoke(array $args = [])
    {
        /** @var UserApi $userapi */
        $userapi = $this->userapi();
        // Security Check
        if (!$this->sec()->checkAccess('ViewHitcountItems')) {
            return;
        }

        // Load API
        if (!$this->mod()->apiLoad('hitcount', 'user')) {
            return;
        }

        $data['title'] = $this->ml('Modules we are currently counting display hits for : (test)');
        $data['moditems'] = [];
        $moduleList = [];

        $numitems = $this->mod()->getVar('numitems');
        if (empty($numitems)) {
            $numitems = 10;
        }
        $modlist = $userapi->getmodules();
        foreach ($modlist as $modid => $itemtypes) {
            $modinfo = $this->mod()->getInfo($modid);
            // Get the list of all item types for this module (if any)
            try {
                $mytypes = $this->mod()->apiFunc($modinfo['name'], 'user', 'getitemtypes');
            } catch (Exception $e) {
                $mytypes = [];
            }
            if (!isset($moduleList[$modinfo['displayname']]['modid'])) {
                $moduleList[$modinfo['displayname']]['modid'] = $modid;
            }

            $mod = & $moduleList[$modinfo['displayname']];
            $mod['numitems'] = 0;
            $mod['numhits']  = 0;
            $mod['tophits']  = [];
            $mod['toplinks'] = [];

            foreach ($itemtypes as $itemtype => $stats) {
                $moditem = [];
                $mod['numitems'] += $moditem['numitems'] = $stats['items'];
                $mod['numhits'] += $moditem['numhits'] = $stats['hits'];
                if ($itemtype == 0) {
                    $moditem['name'] = ucwords($modinfo['displayname']);
                    $moditem['link'] = $this->ctl()->getModuleURL($modinfo['name'], 'user', 'main');
                } else {
                    if (isset($mytypes) && !empty($mytypes[$itemtype])) {
                        $moditem['name'] = $mytypes[$itemtype]['label'];
                        $moditem['link'] = $mytypes[$itemtype]['url'];
                    } else {
                        $moditem['name'] = ucwords($modinfo['displayname']) . ' ' . $itemtype;
                        $moditem['link'] = $this->ctl()->getModuleURL($modinfo['name'], 'user', 'view', ['itemtype' => $itemtype]);
                    }
                }
                $moditem['tophits'] = $userapi->topitems(['modname'  => $modinfo['name'],
                        'itemtype' => $itemtype,
                        'numitems' => $numitems, ]
                );
                foreach ($moditem['tophits'] as $tophit) {
                    $mod['tophits']["$tophit[hits]:$tophit[itemid]"]['itemtype'] = $itemtype;
                    $mod['tophits']["$tophit[hits]:$tophit[itemid]"]['itemid'] = $tophit['itemid'];
                    $mod['tophits']["$tophit[hits]:$tophit[itemid]"]['hits'] = $tophit['hits'];
                }


                if (isset($moditem['tophits']) && count($moditem['tophits']) > 0) {
                    $itemids = [];
                    $itemid2hits = [];
                    foreach ($moditem['tophits'] as $tophit) {
                        $itemids[] = $tophit['itemid'];
                        $itemid2hits[$tophit['itemid']] = $tophit['hits'];
                    }

                    try {
                        $moditem['toplinks'] = $this->mod()->apiFunc(
                            $modinfo['name'],
                            'user',
                            'getitemlinks',
                            ['itemtype' => $itemtype,
                                'itemids' => $itemids]
                        );
                    } catch (Exception $e) {
                        $moditem['toplinks'] = [];
                    }
                    if (!empty($moditem['toplinks'])) {
                        foreach ($moditem['toplinks'] as $itemid => $toplink) {
                            if (!isset($itemid2hits[$itemid])) {
                                continue;
                            }
                            $moditem['toplinks'][$itemid]['hits'] = $itemid2hits[$itemid];
                        }
                    } else {
                        $moditem['toplinks'] = [];
                    }

                    foreach ($moditem['toplinks'] as $itemid => $toplink) {
                        $mod['toplinks']["$toplink[hits]:$itemid"]['itemtype'] = $itemtype;
                        $mod['toplinks']["$toplink[hits]:$itemid"]['itemid']   = $itemid;
                        $mod['toplinks']["$toplink[hits]:$itemid"]['url']      = $toplink['url'];
                        $mod['toplinks']["$toplink[hits]:$itemid"]['title']    = $toplink['title'];
                        $mod['toplinks']["$toplink[hits]:$itemid"]['label']    = $toplink['label'];
                        $mod['toplinks']["$toplink[hits]:$itemid"]['hits']     = $toplink['hits'];
                    }
                }
                $data['moditems'][] = $moditem;
            }
        }

        // Sort the toplinks / tophits by most hits -> least and newest --> oldest
        foreach ($moduleList as $modName => $module) {
            uksort($module['tophits'], 'strnatcasecmp');
            $moduleList[$modName]['tophits'] = array_reverse($module['tophits']);

            uksort($module['toplinks'], 'strnatcasecmp');
            $moduleList[$modName]['toplinks'] = array_reverse($module['toplinks']);
        }

        $data['moduleList'] = $moduleList;

        $this->tpl()->setPageTitle($this->var()->prep($this->ml('Top Items')));

        // Return output
        return $data;
    }
}
