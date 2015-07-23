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

$events['OnFileManagerDirCreate']= $modx->newObject('modPluginEvent');
$events['OnFileManagerDirCreate']->fromArray(array(
    'event' => 'OnFileManagerDirCreate',
    'priority' => 0,
    'propertyset' => 0,
),'',true,true);

$events['OnFileManagerDirRename']= $modx->newObject('modPluginEvent');
$events['OnFileManagerDirRename']->fromArray(array(
    'event' => 'OnFileManagerDirRename',
    'priority' => 0,
    'propertyset' => 0,
),'',true,true);

return $events;

