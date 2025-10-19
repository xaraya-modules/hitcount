<?php

/**
 * Hitcount Module
 *
 * @package modules
 * @subpackage hitcount module
 * @category Third Party Xaraya Module
 * @version 2.0.0
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://xaraya.com/index.php/release/177.html
 * @author Hitcount Module Development Team
 */

namespace Xaraya\Modules\Hitcount;

class Version
{
    /**
     * Get module version information
     *
     * @return array<string, mixed>
     */
    public function __invoke(): array
    {
        return [
            'name' => 'Hitcount',
            'id' => '177',
            'version' => '2.2.0',
            'displayname' => 'Hitcount',
            'description' => 'Count displays of module items',
            'credits' => 'xardocs/credits.txt',
            'help' => 'xardocs/help.txt',
            'changelog' => 'xardocs/changelog.txt',
            'license' => 'xardocs/license.txt',
            'coding' => 'xardocs/coding.txt',
            'official' => true,
            'author' => 'Jim McDonald',
            'contact' => '',
            'admin' => true,
            'user' => true,
            'class' => 'Complete',
            'category' => 'Utility',
            'namespace' => 'Xaraya\\Modules\\Hitcount',
            'twigtemplates' => true,
            'dependencyinfo'
             => [
                 0
                  => [
                      'name' => 'Xaraya Core',
                      'version_ge' => '2.4.1',
                  ],
             ],
        ];
    }
}
