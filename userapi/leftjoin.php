<?php

/**
 * @package modules\hitcount
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Hitcount\UserApi;


use Xaraya\Modules\Hitcount\UserApi;
use Xaraya\Modules\MethodClass;
use xarMod;
use xarSecurity;
use xarDB;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * hitcount userapi leftjoin function
 * @extends MethodClass<UserApi>
 */
class LeftjoinMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * return the field names and correct values for joining on hitcount table
     * example : SELECT ..., $module_id, $itemid, $hits,...
     *           FROM ...
     *           LEFT JOIN $table
     *               ON $field = <name of itemid field>
     *           WHERE ...
     *               AND $hits > 1000
     *               AND $where
     * @param array<mixed> $args
     * @var mixed $modname name of the module you want items from, or
     * @var mixed $modid ID of the module you want items from
     * @var mixed $itemtype item type (optional) or array of itemtypes
     * @var mixed $itemids optional array of itemids that we are selecting on
     * @return array|void array('table' => '_hitcount',
     * 'field' => '_hitcount.itemid',
     * 'where' => '_hitcount.itemid IN (...)
     *             AND _hitcount.module_id = 123',
     * 'moduleid'  => '_hitcount.module_id',
     * // ...
     * 'hits'  => '_hitcount.hits')
     * @see UserApi::leftjoin()
     */
    public function __invoke(array $args = [])
    {
        // Get arguments from argument array
        extract($args);

        // Optional argument
        if (!isset($modname)) {
            $modname = '';
        } else {
            $modid = $this->mod()->getRegID($modname);
        }
        if (!isset($modid)) {
            $modid = '';
        }
        if (!isset($itemids)) {
            $itemids = [];
        }

        // Security check
        if (count($itemids) > 0) {
            foreach ($itemids as $itemid) {
                if (!xarSecurity::check('ViewHitcountItems', 1, 'Item', "$modname:All:$itemid")) {
                    return;
                }
            }
        } else {
            if (!xarSecurity::check('ViewHitcountItems', 1, 'Item', "$modname:All:All")) {
                return;
            }
        }

        // Table definition
        $xartable = & $this->db()->getTables();
        $dbconn = $this->db()->getConn();
        $userstable = $xartable['hitcount'];

        $leftjoin = [];

        // Specify LEFT JOIN ... ON ... [WHERE ...] parts
        $leftjoin['table'] = $xartable['hitcount'];
        $leftjoin['field'] = '';
        if (!empty($modid)) {
            $leftjoin['field'] .= $xartable['hitcount'] . '.module_id = ' . $modid;
            $leftjoin['field'] .= ' AND ';
        }
        if (isset($itemtype)) { // could be 0 (= most likely)
            if (is_numeric($itemtype)) {
                $leftjoin['field'] .= $xartable['hitcount'] . '.itemtype = ' . $itemtype;
                $leftjoin['field'] .= ' AND ';
            } elseif (is_array($itemtype) && count($itemtype) > 0) {
                $seentype = [];
                foreach ($itemtype as $id) {
                    if (empty($id) || !is_numeric($id)) {
                        continue;
                    }
                    $seentype[$id] = 1;
                }
                if (count($seentype) == 1) {
                    $itemtypes = array_keys($seentype);
                    $leftjoin['field'] .= $xartable['hitcount'] . '.itemtype = ' . $itemtypes[0];
                    $leftjoin['field'] .= ' AND ';
                } elseif (count($seentype) > 1) {
                    $itemtypes = join(', ', array_keys($seentype));
                    $leftjoin['field'] .= $xartable['hitcount'] . '.itemtype IN (' . $itemtypes . ')';
                    $leftjoin['field'] .= ' AND ';
                }
            }
        }
        $leftjoin['field'] .= $xartable['hitcount'] . '.itemid';

        if (count($itemids) > 0) {
            $allids = join(', ', $itemids);
            $leftjoin['where'] = $xartable['hitcount'] . '.itemid IN (' . $allids . ')';
            /*
                    if (!empty($modid)) {
                        $leftjoin['where'] .= ' AND ' .
                                              $xartable['hitcount'] . '.module_id = ' .
                                              $modid;
                    }
            */
        } else {
            /*
                    if (!empty($modid)) {
                        $leftjoin['where'] = $xartable['hitcount'] . '.module_id = ' .
                                             $modid;
                    } else {
                        $leftjoin['where'] = '';
                    }
            */
            $leftjoin['where'] = '';
        }

        // Add available columns in the hitcount table
        $columns = ['module_id','itemtype','itemid','hits'];
        foreach ($columns as $column) {
            $leftjoin[$column] = $xartable['hitcount'] . '.' . $column;
        }

        return $leftjoin;
    }
}
