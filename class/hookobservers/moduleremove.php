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
 */
/**
 * Event Subject Observer
 *
 * Event Subject is notified every time xarEvents::notify is called
 * see /code/modules/eventsystem/class/eventsubjects/event.php for subject info
 *
 * This observer is responsible for logging the event to the system log
**/
sys::import('xaraya.structures.hooks.observer');
class HitcountModuleRemoveObserver extends HookObserver implements ixarHookObserver
{
    public $module = 'hitcount';

    /**
     * @param ixarHookSubject $subject
     */
    public function notify(ixarEventSubject $subject)
    {
        $this->setContext($subject->getContext());
        // for module remove, we need the module name, we get that from the objectid
        // get args from subject (array containing objectid, extrainfo)
        $args = $subject->getArgs();
        extract($args);

        // validate parameters...
        // NOTE: this isn't strictly necessary, the hook subject will have already
        // taken care of validations and these values can be relied on to be pre-populated
        // however, just for completeness...
        if (isset($objectid) && !is_string($objectid) || !$this->mod()->isAvailable($objectid)) {
            $invalid['objectid'] = 1;
        }

        // NOTE: as of Jamaica 2.2.0 it's ok to throw exceptions in hooks, the subject handles them
        if (!empty($invalid)) {
            $args = [join(',', $invalid), 'hitcount', 'hooks', 'ModuleRemove'];
            $msg = 'Invalid #(1) for #(2) module #(2) #(3) observer notify method';
            throw new BadParameterException($args, $msg);
        }

        // call the hitcount delete api function
        // @fixme: the delete func returns (potentially) an empty array
        // there's no way to reliably check deletion was a success
        $hit = $this->mod()->apiMethod(
            'hitcount',
            'adminapi',
            'deleteall',
            [
                'objectid' => $objectid,
            ]
        );

        // @checkme: the api func returns an array of info, only really need bool response ?
        if (!is_array($hit)) {
            // @todo: exception here?
            return $extrainfo;
        }

        // the subject expects an array of extrainfo
        // return the merged array of extrainfo and the deleted hit
        return $extrainfo += $hit;
    }
}
