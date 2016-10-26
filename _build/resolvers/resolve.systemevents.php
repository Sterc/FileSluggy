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
                    $eventObj->set('name', $eventName);
                    $eventObj->set('service', 1);
                    $eventObj->set('groupname', 'FileSluggy');
                    $eventObj->save();
                    $modx->log(modX::LOG_LEVEL_INFO, 'Added system event: ' . $eventName);
                }
            }
            break;
    }
}
return true;