<?php
/**
 * @package filesluggy
 * @subpackage build
 */
$settings = array();

$settings['filesluggy.charset_iconv']= $modx->newObject('modSystemSetting');
$settings['filesluggy.charset_iconv']->fromArray(array(
    'key' => 'filesluggy.charset_iconv',
    'value' => 'US-ASCII//TRANSLIT',
    'xtype' => 'textfield',
    'namespace' => 'filesluggy',
    'area' => 'fs_encoding',
),'',true,true);

$settings['filesluggy.enc']= $modx->newObject('modSystemSetting');
$settings['filesluggy.enc']->fromArray(array(
    'key' => 'filesluggy.enc',
    'value' => 'UTF-8',
    'xtype' => 'charset',
    'namespace' => 'filesluggy',
    'area' => 'fs_encoding',
),'',true,true);

$settings['filesluggy.regexp']= $modx->newObject('modSystemSetting');
$settings['filesluggy.regexp']->fromArray(array(
    'key' => 'filesluggy.regexp',
    'value' => '/[^\.A-Za-z0-9 _-]/',
    'xtype' => 'textfield',
    'namespace' => 'filesluggy',
    'area' => 'fs_encoding',
),'',true,true);

$settings['filesluggy.guid_use']= $modx->newObject('modSystemSetting');
$settings['filesluggy.guid_use']->fromArray(array(
    'key' => 'filesluggy.guid_use',
    'value' => false,
    'xtype' => 'combo-boolean',
    'namespace' => 'filesluggy',
    'area' => 'fs_guid',
),'',true,true);

$settings['filesluggy.filename_prefix']= $modx->newObject('modSystemSetting');
$settings['filesluggy.filename_prefix']->fromArray(array(
    'key' => 'filesluggy.filename_prefix',
    'value' => null,
    'xtype' => 'textfield',
    'namespace' => 'filesluggy',
    'area' => 'fs_guid',
),'',true,true);

$settings['filesluggy.ignorefilename']= $modx->newObject('modSystemSetting');
$settings['filesluggy.ignorefilename']->fromArray(array(
    'key' => 'filesluggy.ignorefilename',
    'value' => false,
    'xtype' => 'combo-boolean',
    'namespace' => 'filesluggy',
    'area' => 'fs_guid',
),'',true,true);

$settings['filesluggy.word_delimiter']= $modx->newObject('modSystemSetting');
$settings['filesluggy.word_delimiter']->fromArray(array(
    'key' => 'filesluggy.word_delimiter',
    'value' => '-',
    'xtype' => 'textfield',
    'namespace' => 'filesluggy',
    'area' => 'fs_guid',
),'',true,true);

$settings['filesluggy.lowercase_only']= $modx->newObject('modSystemSetting');
$settings['filesluggy.lowercase_only']->fromArray(array(
    'key' => 'filesluggy.lowercase_only',
    'value' => true,
    'xtype' => 'combo-boolean',
    'namespace' => 'filesluggy',
    'area' => 'fs_guid',
),'',true,true);

$settings['filesluggy.sanitizeDir']= $modx->newObject('modSystemSetting');
$settings['filesluggy.sanitizeDir']->fromArray(array(
    'key' => 'filesluggy.sanitizeDir',
    'value' => true,
    'xtype' => 'combo-boolean',
    'namespace' => 'filesluggy',
    'area' => 'fs_type',
),'',true,true);

$settings['filesluggy.allowed_file_types']= $modx->newObject('modSystemSetting');
$settings['filesluggy.allowed_file_types']->fromArray(array(
    'key' => 'filesluggy.allowed_file_types',
    'value' => 'jpg,jpeg,png,gif,psd,ico,bmp,svg,doc,docx,pdf',
    'xtype' => 'textfield',
    'namespace' => 'filesluggy',
    'area' => 'fs_type',
),'',true,true);

$settings['filesluggy.constrain_mediasource']= $modx->newObject('modSystemSetting');
$settings['filesluggy.constrain_mediasource']->fromArray(array(
    'key' => 'filesluggy.constrain_mediasource',
    'value' => '',
    'xtype' => 'textfield',
    'namespace' => 'filesluggy',
    'area' => 'fs_type',
),'',true,true);



return $settings;