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
use xarController;
use xarModVars;
use sys;
use Exception;

sys::import('xaraya.modules.method');

/**
 * hitcount admin view function
 * @extends MethodClass<AdminGui>
 */
class ViewMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * View statistics about hitcount
     * @return array|void
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
        if (!$this->fetch('sort', 'isset', $sort, null, xarVar::DONT_SET)) {
            return;
        }
        if (!$this->fetch('sortorder', 'isset', $sortorder, null, xarVar::DONT_SET)) {
            return;
        }
        if (!$this->fetch('startnum', 'isset', $startnum, 1, xarVar::NOT_REQUIRED)) {
            return;
        }

        $data = [];

        $modlist = xarMod::apiFunc('hitcount', 'user', 'getmodules');

        if (empty($modid)) {
            $data['moditems'] = [];
            $data['numitems'] = 0;
            $data['numhits'] = 0;
            foreach ($modlist as $modid => $itemtypes) {
                $modinfo = xarMod::getInfo($modid);
                // Get the list of all item types for this module (if any)
                try {
                    $mytypes = xarMod::apiFunc($modinfo['name'], 'user', 'getitemtypes');
                } catch (Exception $e) {
                    $mytypes = [];
                }
                foreach ($itemtypes as $itemtype => $stats) {
                    $moditem = [];
                    $moditem['numitems'] = $stats['items'];
                    $moditem['numhits'] = $stats['hits'];
                    if ($itemtype == 0) {
                        $moditem['name'] = ucwords($modinfo['displayname']);
                        //    $moditem['link'] = xarController::URL($modinfo['name'],'user','main');
                    } else {
                        if (isset($mytypes) && !empty($mytypes[$itemtype])) {
                            $moditem['name'] = ucwords($modinfo['displayname']) . ' ' . $itemtype . ' - ' . $mytypes[$itemtype]['label'];
                            //    $moditem['link'] = $mytypes[$itemtype]['url'];
                        } else {
                            $moditem['name'] = ucwords($modinfo['displayname']) . ' ' . $itemtype;
                            //    $moditem['link'] = xarController::URL($modinfo['name'],'user','view',array('itemtype' => $itemtype));
                        }
                    }
                    $moditem['link'] = $this->getUrl(
                        'admin',
                        'view',
                        ['modid' => $modid,
                            'itemtype' => empty($itemtype) ? null : $itemtype, ]
                    );
                    $moditem['delete'] = $this->getUrl(
                        'admin',
                        'delete',
                        ['modid' => $modid,
                            'itemtype' => empty($itemtype) ? null : $itemtype, ]
                    );
                    $data['moditems'][] = $moditem;
                    $data['numitems'] += $moditem['numitems'];
                    $data['numhits'] += $moditem['numhits'];
                }
            }
            $data['delete'] = $this->getUrl('admin', 'delete');
        } else {
            $modinfo = xarMod::getInfo($modid);
            if (empty($itemtype)) {
                $data['modname'] = ucwords($modinfo['displayname']);
                $itemtype = null;
                if (isset($modlist[$modid][0])) {
                    $stats = $modlist[$modid][0];
                }
            } else {
                // Get the list of all item types for this module (if any)
                try {
                    $mytypes = xarMod::apiFunc($modinfo['name'], 'user', 'getitemtypes');
                } catch (Exception $e) {
                    $mytypes = [];
                }
                if (isset($mytypes) && !empty($mytypes[$itemtype])) {
                    $data['modname'] = ucwords($modinfo['displayname']) . ' ' . $itemtype . ' - ' . $mytypes[$itemtype]['label'];
                    //    $data['modlink'] = $mytypes[$itemtype]['url'];
                } else {
                    $data['modname'] = ucwords($modinfo['displayname']) . ' ' . $itemtype;
                    //    $data['modlink'] = xarController::URL($modinfo['name'],'user','view',array('itemtype' => $itemtype));
                }
                if (isset($modlist[$modid][$itemtype])) {
                    $stats = $modlist[$modid][$itemtype];
                }
            }
            if (isset($stats)) {
                $data['numitems'] = $stats['items'];
                $data['numhits'] = $stats['hits'];
            } else {
                $data['numitems'] = 0;
                $data['numhits'] = '';
            }
            $numstats = $this->getModVar('numstats');
            if (empty($numstats)) {
                $numstats = 100;
            }
            // pager
            $data['startnum'] = $startnum;
            $data['total'] = $data['numitems'];
            $data['urltemplate'] = $this->getUrl(
                'admin',
                'view',
                ['modid' => $modid,
                    'itemtype' => $itemtype,
                    'sort' => $sort,
                    'sortorder' => $sortorder,
                    'startnum' => '%%', ]
            );
            $data['itemsperpage'] = $numstats;

            $data['modid'] = $modid;
            $getitems = xarMod::apiFunc(
                'hitcount',
                'user',
                'getitems',
                ['modid' => $modid,
                    'itemtype' => $itemtype,
                    'numitems' => $numstats,
                    'startnum' => $startnum,
                    'sort' => $sort,
                    'sortorder' => $sortorder,
                ]
            );
            $showtitle = $this->getModVar('showtitle');
            if (!empty($showtitle)) {
                $itemids = array_keys($getitems);
                try {
                    $itemlinks = xarMod::apiFunc(
                        $modinfo['name'],
                        'user',
                        'getitemlinks',
                        ['itemtype' => $itemtype,
                            'itemids' => $itemids]
                    );
                } catch (Exception $e) {
                    $itemlinks = [];
                }
            } else {
                $itemlinks = [];
            }
            $data['moditems'] = [];
            foreach ($getitems as $itemid => $numhits) {
                $data['moditems'][$itemid] = [];
                $data['moditems'][$itemid]['numhits'] = $numhits;
                $data['moditems'][$itemid]['delete'] = $this->getUrl(
                    'admin',
                    'delete',
                    ['modid' => $modid,
                        'itemtype' => $itemtype,
                        'itemid' => $itemid, ]
                );
                if (isset($itemlinks[$itemid])) {
                    $data['moditems'][$itemid]['link'] = $itemlinks[$itemid]['url'];
                    $data['moditems'][$itemid]['title'] = $itemlinks[$itemid]['label'];
                }
            }
            unset($getitems);
            unset($itemlinks);
            $data['delete'] = $this->getUrl(
                'admin',
                'delete',
                ['modid' => $modid,
                    'itemtype' => $itemtype, ]
            );
            $data['sortlink'] = [];
            if (empty($sortorder) || $sortorder == 'ASC') {
                $sortorder = 'DESC';
            } else {
                $sortorder = 'ASC';
            }
            //        if (empty($sort) || $sort == 'itemid') {
            //             $data['sortlink']['itemid'] = '';
            //
            //        } else {
            $data['sortlink']['itemid'] = $this->getUrl(
                'admin',
                'view',
                ['modid' => $modid,
                    'itemtype' => $itemtype,
                    'sortorder' => $sortorder,
                ]
            );

            //        }
            //       if (!empty($sort) && $sort == 'numhits') {
            //            $data['sortlink']['numhits'] = '';
            //       } else {
            $data['sortlink']['numhits'] = $this->getUrl(
                'admin',
                'view',
                ['modid' => $modid,
                    'itemtype' => $itemtype,
                    'sort' => 'numhits',
                    'sortorder' => $sortorder,
                ]
            );
            //       }
            //       $data['sortorder'] = $sortorder;
        }

        return $data;
    }
}
