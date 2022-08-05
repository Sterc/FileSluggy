<?php
use Sterc\FileSluggy\FileSluggy;
use MODX\Revolution\modX;

$filesluggy = new FileSluggy($modx);

switch ($modx->event->name) {
    case 'OnFileManagerDirCreate':
    case 'OnFileManagerDirRename':
        if ($filesluggy->santizeAllowThisMediaSource($source->get('id'))) {
            if ($modx->getOption('filesluggy.sanitizeDir')) {
                $basePath    = $source->getBasePath();
                $dirName     = basename($directory);
                $newDirName  = $filesluggy->sanitizeName($dirName, true);

                $filesluggy->renameContainer($source, str_replace(realpath($basePath), '', $directory), $newDirName);

                /* Invoke custom system event 'FileSluggyOnUpdateDirname'. */
                $modx->invokeEvent('FileSluggyOnUpdateDirname', [
                    'oldName' => $dirName,
                    'newName' => $newDirName
                ]);
            }
        }
        break;
    case 'OnFileManagerUpload':
        $url   = parse_url($_SERVER['HTTP_REFERER']);
        $query = $url['query'];

        foreach ($files as $file) {
            if ($filesluggy->santizeAllowThisMediaSource($source->get('id'))) {
                if (!$source->hasErrors()) {
                    if ($file['error'] == 0) {
                        $basePath = $source->getBasePath();
                        $oldPath  = $directory . $file['name'];
                        if ($filesluggy->allowType($file['name'])) {
                            $newFileName = $filesluggy->sanitizeName($file['name']);

                            if ($filesluggy->checkFileNameChanged()) {
                                $newFileName = $filesluggy->checkFileExists($basePath . $directory . $newFileName);

                                if ($source->renameObject($oldPath, $newFileName)) {
                                    $modx->invokeEvent('FileSluggyOnUpdateFilename', [
                                        'oldName' => $file['name'],
                                        'newName' => $newFileName
                                    ]);
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
        break;
}