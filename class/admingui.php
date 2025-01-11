<?php

/**
 * @package modules\hitcount
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
 **/

namespace Xaraya\Modules\Hitcount;

use Xaraya\Modules\AdminGuiClass;
use sys;

sys::import('xaraya.modules.admingui');
sys::import('modules.hitcount.class.adminapi');

/**
 * Handle the hitcount admin GUI
 *
 * @method mixed delete(array $args)
 * @method mixed hooks(array $args)
 * @method mixed main(array $args)
 * @method mixed modifyconfig(array $args)
 * @method mixed overview(array $args)
 * @method mixed view(array $args)
 * @extends AdminGuiClass<Module>
 */
class AdminGui extends AdminGuiClass
{
    // ...
}
