<?php

/**
 * Description: Resolver script for FileSluggy package
 * @package filesluggy
 * @subpackage build
 */
/* Example Resolver script */

/* The $modx object is not available here. In its place we
 * use $object->xpdo
 */

$modx = & $object->xpdo;

/* Remember that the files in the _build directory are not available
 * here and we don't know the IDs of any objects, so resources,
 * elements, and other objects must be retrieved by name with
 * $modx->getObject().
 */

/* Connecting plugins to the appropriate system events and
 * connecting TVs to their templates is done here.
 *
 * Be sure to set the name of the category in $category.
 *
 * You will have to hand-code the names of the elements and events
 * in the arrays below.
 */


$success = true;

$modx->log(xPDO::LOG_LEVEL_INFO, 'Configurating settings.');
switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    /* This code will execute during an install */
    case xPDOTransport::ACTION_INSTALL:
  
        $settings = array(
            'ignorefilename',
            'filename_prefix',
            'guid_use',
        );
        foreach ($settings as $key) {
            if (isset($options[$key])) {
                $setting = $object->xpdo->getObject('modSystemSetting', array('key' => 'filesluggy.' . $key));
                if ($setting != null) {
                    $setting->set('value', $options[$key]);
                    $setting->save();
                } else {
                    $object->xpdo->log(xPDO::LOG_LEVEL_ERROR, '[FileSluggy] ' . $key . ' setting could not be found, so the setting could not be changed.');
                }
            }
        }

        $success = true;
        break;
  case xPDOTransport::ACTION_UPGRADE:
    $success = true;
  break;
    /* This code will execute during an uninstall */
    case xPDOTransport::ACTION_UNINSTALL:
        $modx->log(xPDO::LOG_LEVEL_INFO, 'Uninstalling . . .');
        $success = true;
        break;
}
$modx->log(xPDO::LOG_LEVEL_INFO, 'Script resolver actions completed');
return $success;