<?php
/**
 * @package filesluggy
 * @subpackage build
 */
$events = array();

$events['OnFileManagerUpload']= $modx->newObject('modPluginEvent');
$events['OnFileManagerUpload']->fromArray(array(
    'event' => 'OnFileManagerUpload',
    'priority' => 0,
    'propertyset' => 0,
),'',true,true);

return $events;

