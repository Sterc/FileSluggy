<?php
/**
 * Resolve creating system events
 *
 * @package formit
 * @subpackage build
 */
if ($object->xpdo) {
    /** @var modX $modx */
    $modx =& $object->xpdo;
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            $events = ['FileSluggyOnUpdateDirname', 'FileSluggyOnUpdateFilename'];
            foreach ($events as $eventName) {
                $eventFields = array(
                    'name' => $eventName,
                    'service' => 1,
                    'groupname' => 'FileSluggy'
                );
                if (!$modx->getCount('modEvent', $eventFields)) {
                    $eventObj = $modx->newObject('modEvent');
                    $eventObj->fromArray($eventFields);
                }
            }
            break;
    }
}
return true;