<?php

/**
 * Handle module installer functions
 *
 * @package modules\hitcount
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Hitcount;

use Xaraya\Modules\InstallerClass;
use xarTableDDL;
use xarHooks;
use xarPrivileges;
use xarMasks;

/**
 * Handle module installer functions
 *
 * @todo add extra use ...; statements above as needed
 * @todo replaced hitcount_*() function calls with $this->*() calls
 * @extends InstallerClass<Module>
 */
class Installer extends InstallerClass
{
    /**
     * Configure this module - override this method
     *
     * @todo use this instead of init() etc. for standard installation
     * @return void
     */
    public function configure()
    {
        $this->objects = [
            // add your DD objects here
            //'hitcount_object',
        ];
        $this->variables = [
            // add your module variables here
            'hello' => 'world',
        ];
        $this->oldversion = '2.4.1';
    }

    /** xarinit.php functions imported by bermuda_cleanup */

    /**
     * initialise the hitcount module
     * Initialisation functions for hitcount
     * @Author Original author: Jim McDonald
     */
    public function init()
    {
        // Set ModVar
        $this->mod()->setVar('countadmin', 0);

        // Get database information
        $dbconn = $this->db()->getConn();
        $xartable = & $this->db()->getTables();

        //Load Table Maintenance API

        // Create tables
        $query = xarTableDDL::createTable(
            $xartable['hitcount'],
            ['id'         => ['type'        => 'integer',
                'unsigned'    => true,
                'null'        => false,
                'increment'   => true,
                'primary_key' => true, ],
                // TODO: replace with unique id
                'object_id'  => ['type'        => 'integer',
                    'unsigned'    => true,
                    'null'        => false,
                    'default'     => '0', ],
                'module_id'  => ['type'        => 'integer',
                    'unsigned'    => true,
                    'null'        => false,
                    'default'     => '0', ],
                'itemtype'   => ['type'        => 'integer',
                    'unsigned'    => true,
                    'null'        => false,
                    'default'     => '0', ],
                'itemid'     => ['type'        => 'integer',
                    'unsigned'    => true,
                    'null'        => false,
                    'default'     => '0', ],
                'hits'       => ['type'        => 'integer',
                    'null'        => false,
                    'size'        => 'big',
                    'default'     => '0', ],
                'lasthit'    => ['type'        => 'integer',
                    'unsigned'    => true,
                    'null'        => false,
                    'default'     => '0', ], ]
        );

        $result = $dbconn->Execute($query);
        if (!$result) {
            return;
        }

        $query = xarTableDDL::createIndex(
            $xartable['hitcount'],
            ['name'   => 'i_' . $this->db()->getPrefix() . '_hitcombo',
                'fields' => ['module_id','itemtype', 'itemid'],
                'unique' => false, ]
        );

        $result = $dbconn->Execute($query);
        if (!$result) {
            return;
        }

        $query = xarTableDDL::createIndex(
            $xartable['hitcount'],
            ['name'   => 'i_' . $this->db()->getPrefix() . '_hititem',
                'fields' => ['itemid'],
                'unique' => false, ]
        );

        $result = $dbconn->Execute($query);
        if (!$result) {
            return;
        }

        $query = xarTableDDL::createIndex(
            $xartable['hitcount'],
            ['name'   => 'i_' . $this->db()->getPrefix() . '_hits',
                'fields' => ['hits'],
                'unique' => false, ]
        );

        $result = $dbconn->Execute($query);
        if (!$result) {
            return;
        }

        // Set up module hooks - using hook call handlers now
        // from Jamaica 2.2.0 onwards we use the new hook system
        // (see observers in hitcount/class/hookobservers/)
        xarHooks::registerObserver('ItemCreate', 'hitcount');
        xarHooks::registerObserver('ItemDisplay', 'hitcount');
        xarHooks::registerObserver('ItemDelete', 'hitcount');
        // @checkme: there seems to be a 'view' hook implemented in the 2.0.0-b4 revised hook calls
        // but no such hook exists, the nearest would be the yet to be implemented ItemtypeView hook
        //xarHooks::registerObserver('ItemtypeView', 'hitcount');
        xarHooks::registerObserver('ModuleRemove', 'hitcount');
        // when a module item is displayed, created or deleted
        // (use xar::mem()->set('Hooks.hitcount','save', 1) to tell hitcount *not*
        // to display the hit count, but to save it in 'Hooks.hitcount', 'value')
        // <chris> - why is this necessary?

        /*********************************************************************
        * Define instances for this module
        * Format is
        * setInstance(Module,Type,ModuleTable,IDField,NameField,ApplicationVar,LevelTable,ChildIDField,ParentIDField)
        *********************************************************************/

        $query1 = "SELECT DISTINCT $xartable[modules].name FROM $xartable[hitcount] LEFT JOIN $xartable[modules] ON $xartable[hitcount].module_id = $xartable[modules].regid";
        $query2 = "SELECT DISTINCT itemtype FROM $xartable[hitcount]";
        $query3 = "SELECT DISTINCT itemid FROM $xartable[hitcount]";
        $instances = [
            ['header' => 'Module Name:',
                'query' => $query1,
                'limit' => 20,
            ],
            ['header' => 'Item Type:',
                'query' => $query2,
                'limit' => 20,
            ],
            ['header' => 'Item ID:',
                'query' => $query3,
                'limit' => 20,
            ],
        ];
        xarPrivileges::defineInstance('hitcount', 'Item', $instances);

        /*********************************************************************
        * Register the module components that are privileges objects
        * Format is
        * xarMasks::register(Name,Realm,Module,Component,Instance,Level,Description)
        *********************************************************************/


        xarMasks::register('ViewHitcountItems', 'All', 'hitcount', 'Item', 'All:All:All', 'ACCESS_OVERVIEW');
        xarMasks::register('ReadHitcountItem', 'All', 'hitcount', 'Item', 'All:All:All', 'ACCESS_READ');
        xarMasks::register('DeleteHitcountItem', 'All', 'hitcount', 'Item', 'All:All:All', 'ACCESS_DELETE');
        xarMasks::register('ManageHitcount', 'All', 'hitcount', 'All', 'All', 'ACCESS_DELETE');
        xarMasks::register('AdminHitcount', 'All', 'hitcount', 'All', 'All', 'ACCESS_ADMIN');

        xarPrivileges::register('ViewHitcount', 'All', 'hitcount', 'All', 'All', 'ACCESS_OVERVIEW');
        xarPrivileges::register('ReadHitcount', 'All', 'hitcount', 'All', 'All', 'ACCESS_READ');
        xarPrivileges::register('ManageHitcount', 'All', 'hitcount', 'All', 'All:All', 'ACCESS_DELETE');
        xarPrivileges::register('AdminHitcount', 'All', 'hitcount', 'All', 'All', 'ACCESS_ADMIN');

        // Initialisation successful
        return true;
    }

    /**
     * upgrade the hitcount module from an old version
     */
    public function upgrade($oldversion)
    {
        // Upgrade dependent on old version number
        switch ($oldversion) {
            case '2.0.0':
                // intermediate versions from repository in jamaica 2.0.0-b2 may have wrong module id's
                // stored in xar_hitcount

            case '2.0.1':
                // this is only supported for versions *after* jamaica 2.0.0-b4 ! (deprecated)
                /*
                    This functionality has been replaced, skip straight to the new hook system
                    when upgrading from this version

                // switch from hook functions to hook class handlers
                $dbconn = $this->db()->getConn();
                $xartable =& $this->db()->getTables();

                $tmodInfo = $this->mod()->getBaseInfo('hitcount');
                $tmodId = $tmodInfo['systemid'];

                $sql = "UPDATE $xartable[hooks]
                        SET t_type = ?,
                            t_func = ?,
                            t_file = ?
                        WHERE t_module_id = ?
                          AND object = ?";
                $stmt = $dbconn->prepareStatement($sql);

                // update item hooks
                $bindvars = array('class','HitcountItemHooks','modules.hitcount.class.itemhooks',$tmodId,'item');
                $stmt->executeUpdate($bindvars);

                // update module hooks
                $bindvars = array('class','HitcountConfigHooks','modules.hitcount.class.confighooks',$tmodId,'module');
                $stmt->executeUpdate($bindvars);
                */
            case '2.1.0':
                // from Jamaica 2.2.0 onwards we use the new hook system
                // (see observers in hitcount/class/hookobservers/)
                xarHooks::registerObserver('ItemCreate', 'hitcount');
                xarHooks::registerObserver('ItemDisplay', 'hitcount');
                xarHooks::registerObserver('ItemDelete', 'hitcount');
                //xarHooks::registerObserver('ItemtypeView', 'hitcount');
                xarHooks::registerObserver('ModuleRemove', 'hitcount');

                break;
        }

        return true;
    }

    /**
     * delete the hitcount module
     */
    public function delete()
    {
        // nothing special to do here - rely on standard deinstall to take care of everything
        $module = 'hitcount';
        return $this->mod()->apiFunc('modules', 'admin', 'standarddeinstall', ['module' => $module]);
    }
}
