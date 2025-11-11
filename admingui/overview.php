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
use xarTpl;

/**
 * hitcount admin overview function
 * @extends MethodClass<AdminGui>
 */
class OverviewMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Overview function that displays the standard Overview page
     * This function shows the overview template, currently admin-main.xd.
     * The template contains overview and help texts
     * @author the Hitcount module development team
     * @return array|string|void xarTpl::module with $data containing template data
     * @since 4 March 2006
     * @see AdminGui::overview()
     */
    public function __invoke(array $args = [])
    {
        /* Security Check */
        if (!$this->sec()->checkAccess('AdminHitcount', 0)) {
            return;
        }

        $data = [];

        /* if there is a separate overview function return data to it
         * else just call the main function that displays the overview
         */

        $data['context'] = $this->getContext();
        return $this->mod()->template('main', $data, 'main');
    }
}
