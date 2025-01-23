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

/*
 * Table information for hitcount module
 *
 * Original Author of file: Jim McDonald
 */
function hitcount_xartables(?string $prefix = null)
{
    // Initialise table array
    $xartable = [];
    $prefix ??= xarDB::getPrefix();

    // Name for hitcount database entities
    $hitcount = $prefix . '_hitcount';

    // Table name
    $xartable['hitcount'] = $hitcount;

    // Return table information
    return $xartable;
}
