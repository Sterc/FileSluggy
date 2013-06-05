<?php
/**
 * FileSluggy by Sterc
 * Sanitizes a filename to be a nice and more clean filename, so it will work better with phpthumb and that kind of stuff

 * Copyright 2013 by Sterc
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
 *
 * @authors Friso Speulman <friso@sterc.nl> & Wieger Sloot <wieger@sterc.nl>
 * @credits:
 *      - Based on the code of the sanitizefilename plugin of Benjamin Vauchel https://github.com/benjamin-vauchel/SanitizeFilename
 *      - The Slug() phunction of AlixAxel https://github.com/alixaxel/phunction/blob/master/phunction/Text.php
 * @version Version 1.0
 * @package filesluggy
 */
/**
 * Default English Lexicon Entries for FileSluggy
 *
 * @package filesluggy
 * @subpackage lexicon
 */
$FileSluggy = $modx->getService('filesluggy', 'FileSluggy', $modx->getOption('filesluggy.core_path', null, $modx->getOption('core_path') . 'components/filesluggy/') . 'model/filesluggy/', $scriptProperties);
if (!($FileSluggy instanceof FileSluggy))
    return '';

switch ($modx->event->name) {
    case 'OnFileManagerUpload':


        foreach ($files as $file) {
            if (!$source->hasErrors()) {
                if ($file['error'] == 0) {

                    $basePath = $source->getBasePath();
                    $oldPath = $directory . $file['name'];
					if($FileSluggy->allowType($file['name'])){
						$newFileName = $FileSluggy->sanitizeName($file['name']);
						$newFileName = $FileSluggy->checkFileExists($basePath.$directory.$newFileName);
						
						if($source->renameObject($oldPath, $newFileName)){
							return;
						}else{
							return;
						}
					}else{
					return;
					}
                    
                } else {
                    return;
                }
            } else {
                $modx->log(modX::LOG_LEVEL_ERROR, '[FileSluggy] There was an error during the upload process...');
            }
        }
        break;
}