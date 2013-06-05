<?php

$values = array(
    'ignorefilename' => '0',
    'filename_prefix' => '',
    'guid_use' => '0',
);
switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
  
        $setting = $modx->getObject('modSystemSetting',array('key' => 'filesluggy.ignorefilename'));
        if ($setting != null) { $values['ignorefilename'] = $setting->get('value'); }
        unset($setting);

        $setting = $modx->getObject('modSystemSetting',array('key' => 'filesluggy.filename_prefix'));
        if ($setting != null) { $values['filename_prefix'] = $setting->get('value'); }
        unset($setting);

        $setting = $modx->getObject('modSystemSetting',array('key' => 'filesluggy.guid_use'));
        if ($setting != null) { $values['guid_use'] = $setting->get('value'); }
        unset($setting);
    break;
     case xPDOTransport::ACTION_UPGRADE:break;
    case xPDOTransport::ACTION_UNINSTALL: break;
}



$output = '<div class="installer">
    <h2>Configuration Options</h2>
    <table cellpadding="5" cellspacing="0" border="0" width="600">
        <tr>
            <td width="400">
                <label for="ignorefilename">
                    <strong>Ignore Filenames</strong><br />
                    <small>Removes the orginal Filename. Always applies an uid.</small>
                </label>
            </td>
        </tr>
        <tr>
            <td>
                <select name="ignorefilename" id="ignorefilename">
                    <option value="0">No</option> 
                    <option value="1">Yes</option> 
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <label for="guid_use" >
                    <strong>Use GUID</strong><br />
                    <small>Adds an unique number to every file.</small>
                </label>
            </td>
        </tr>
        <tr>
            <td>
                <select name="guid_use" id="guid_use">
                    <option value="0">No</option> 
                    <option value="1">Yes</option> 
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <label for="filename_prefix">
                    <strong>Filename Prefix</strong><br />
                    <small>Adds a prefix to every file. For example :"filesluggy". Leave empty to add no prefix. </small>
                </label>
            </td>

        </tr>
        <tr>
            <td>
                <input style="width:300px;" type="text" name="filename_prefix" id="filename_prefix" />
            </td>
        </tr>
    </table>
    <br />
    <small>This plugin alters the name of a file after it has been uploaded via the MODx Filemanager. Usage is own risk . Created by Sterc</small>
</div>';

return $output;