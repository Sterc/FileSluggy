<?php
/**
 * FileSluggy by Sterc
 * Sanitizes a filename on upload to be a nice and more clean filename, so it will work better with phpthumbof, pthumb and overall cleaner filenames and directories.
 * Copyright 2015 by Sterc
 * FileSluggy is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * FileSluggy is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * formAlicious; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @author Sterc <modx@sterc.nl>
 * @credits:
 *      - Based on the code of the sanitizefilename plugin of Benjamin Vauchel https://github.com/benjamin-vauchel/SanitizeFilename
 *      - The Slug() phunction of AlixAxel https://github.com/alixaxel/phunction/blob/master/phunction/Text.php
 * @version Version 1.3
 * @package filesluggy
 */

$FileSluggy = $modx->getService('filesluggy', 'FileSluggy', $modx->getOption('filesluggy.core_path', null, $modx->getOption('core_path') . 'components/filesluggy/') . 'model/filesluggy/', $scriptProperties);
if (!($FileSluggy instanceof FileSluggy)) {
    return;
}

switch ($modx->event->name) {
    case 'OnFileManagerDirCreate':
    case 'OnFileManagerDirRename':
        if ($FileSluggy->santizeAllowThisMediaSource($source->get('id'))) {
            if ($modx->getOption('filesluggy.sanitizeDir')) {
                $basePath = $source->getBasePath();
                $dirName  = basename($directory);
                $dirName  = $FileSluggy->sanitizeName($dirName, true);
                $FileSluggy->renameContainer($source, str_replace($basePath, '', $directory), $dirName);
            }
        }
        break;
    case 'OnFileManagerUpload':
        $url = parse_url($_SERVER['HTTP_REFERER']);
        $query = $url['query'];
        if (strpos($query, 'a=resource/create') !== false ||
            strpos($query, 'a=resource/update') !== false ||
            strpos($query, 'a=media/browser') !== false
        ) {
            foreach ($files as $file) {
                if ($FileSluggy->santizeAllowThisMediaSource($source->get('id'))) {
                    if (!$source->hasErrors()) {
                        if ($file['error'] == 0) {
                            $basePath = $source->getBasePath();
                            $oldPath  = $directory . $file['name'];
                            if ($FileSluggy->allowType($file['name'])) {
                                $newFileName = $FileSluggy->sanitizeName($file['name']);
                                if ($FileSluggy->checkFileNameChanged()) {
                                    $newFileName = $FileSluggy->checkFileExists($basePath . $directory . $newFileName);
                                    if ($source->renameObject($oldPath, $newFileName)) {
                                        return;
                                    } else {
                                        return;
                                    }
                                } else {
                                    return;
                                }
                            } else {
                                return;
                            }
                        } else {
                            return;
                        }
                    } else {
                        $modx->log(modX::LOG_LEVEL_ERROR, '[FileSluggy] There was an error during the upload process...');
                    }
                    return;
                }
                return;
            }
        }
        break;
}
