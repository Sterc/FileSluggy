<?php
/**
 * Resolve creating system events
 *
 * @package formit
 * @subpackage build
 */
use MODX\Revolution\modX;
use MODX\Revolution\modEvent;
use xPDO\Transport\xPDOTransport;

if ($transport->xpdo) {
    /** @var modX $modx */
    $modx =& $transport->xpdo;
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            $events = ['FileSluggyOnUpdateDirname', 'FileSluggyOnUpdateFilename'];
            foreach ($events as $eventName) {
                $eventFields = [
                    'name'      => $eventName,
                    'service'   => 1,
                    'groupname' => 'FileSluggy'
                ];

                if (!$modx->getCount(modEvent::class, $eventFields)) {
                    $eventObj = $modx->newObject(modEvent::class);
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