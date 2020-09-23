<?php

/**
 * FileSluggy by Sterc
 * Sanitizes a filename to be a nice and more clean filename.
 */
class FileSluggy
{
    /** @var \modX $modx */
    public $modx;

    /** @var array $config */
    public $config = array();
    private $_FileNameSameAsOrginal = false;
    private $_mediaSourceAllow = null;

    public function __construct(modX &$modx, array $config = array())
    {
        $this->modx = & $modx;
        $corePath = $this->modx->getOption(
            'filesluggy.core_path',
            $config,
            $this->modx->getOption('core_path') . 'components/filesluggy/'
        );
        $charSet = $this->modx->getOption(
            'charset_iconv',
            $config,
            $this->modx->getOption('filesluggy.charset_iconv', null, 'US-ASCII//TRANSLIT')
        );
        $Encoding = strtoupper(
            $this->modx->getOption(
                'enc',
                $config,
                $this->modx->getOption('filesluggy.enc', null, 'UTF-8')
            )
        );
        $RegExp = $this->modx->getOption(
            'regexp',
            $config,
            $this->modx->getOption('filesluggy.regexp', null, '/[^\.A-Za-z0-9 _-]/')
        );
        $AddGUID = (boolean) $this->modx->getOption(
            'guid_use',
            $config,
            $this->modx->getOption('filesluggy.guid_use', null, 0)
        );
        $fileNamePrefix = (string) $this->modx->getOption(
            'filenamePrefix',
            $config,
            $this->modx->getOption('filesluggy.filename_prefix', null, '')
        );
        $ignoreFilename = (boolean) $this->modx->getOption(
            'ignoreFilename',
            $config,
            $this->modx->getOption('filesluggy.ignorefilename', null, 0)
        ); // Replaces the whole file name with a guid.
        $Delimiter = $this->modx->getOption(
            'word_delimiter',
            $config,
            $this->modx->getOption('filesluggy.word_delimiter', null, '-')
        );
        $fileTypes = $this->modx->getOption(
            'allowed_file_types',
            $config,
            $this->modx->getOption(
                'filesluggy.allowed_file_types',
                null,
                'jpg,jpeg,png,gif,psd,ico,bmp,svg,doc,docx,pdf'
            )
        );
        $LowerCaseOnly = (boolean) $this->modx->getOption(
            'lowercase_only',
            $config,
            $this->modx->getOption('filesluggy.lowercase_only', null, 1)
        );
        $constrainMediaSource = $this->modx->getOption(
            'constrain_mediasource',
            $config,
            $this->modx->getOption('filesluggy.constrain_mediasource', null, null)
        );
        $cultureKey = $this->modx->getOption('cultureKey', null, 'en');
        $sanitizeDir = $this->modx->getOption(
            'sanitizeDir',
            $config,
            $this->modx->getOption('filesluggy.sanitizeDir', null, false)
        );
        $this->SkipIconv = function_exists('iconv') ? false : true;
        $this->SkipMB = function_exists('mb_check_encoding') ? false : true;


        $this->config = array_merge(array(
                                        'corePath' => $corePath,
                                        'modelPath' => $corePath . 'model/',
                                        'charSet' => $charSet,
                                        'encoding' => $Encoding,
                                        'regExp' => $RegExp,
                                        'addGuid' => (int) $AddGUID,
                                        'filenamePrefix' => $fileNamePrefix,
                                        'ignoreFilename' => (int) $ignoreFilename,
                                        'wordDelimiter' => $Delimiter,
                                        'lowerCase' => $LowerCaseOnly,
                                        'cultureKey' => $cultureKey,
                                        'sanitizeDir' => $sanitizeDir,
                                        'fileTypes' => $fileTypes
                                    ), $config);


        $this->config['filenamePrefix'] = trim($this->config['filenamePrefix']);

        if (!in_array($this->config['wordDelimiter'], array("-", "_"))) {
            $this->config['wordDelimiter'] = '-';
        }
        $this->config['wordDelimiters'] = '-_';
        if (!empty($this->config['fileTypes'])) {
            $tmp = preg_replace('/\s+/u', "", $this->config['fileTypes']);
            $tmp = strtolower($tmp);
            $tmp = explode(",", $this->config['fileTypes']);
            foreach ($tmp as $data) {
                $tmpArr[] = trim($data);
            }
            if (is_array($tmpArr)) {
                $this->config['fileTypes'] = $tmpArr;
            }
        }
        if (!empty($constrainMediaSource)) {
            $arrMS = explode(",", $constrainMediaSource);
            if (is_array($arrMS) && count($arrMS) > 0) {
                $this->_mediaSourceAllow = $arrMS;
            } else {
                $this->_mediaSourceAllow = null;
            }
        }


        $this->modx->addPackage('filesluggy', $this->config['modelPath']);
        $this->modx->lexicon->load('filesluggy:default');
    }

    /**
     *
     * @param int $sourceID sourceID where files are uploaded into
     * @return boolean TRUE allow filesluggy || FALSE prohibit filesluggy
     */
    public function santizeAllowThisMediaSource($sourceID = null)
    {
        if (empty($this->_mediaSourceAllow)) {
            return true;
        }
        if (!empty($sourceID)) {
            if (in_array($sourceID, $this->_mediaSourceAllow)) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Check if we have to sanitize the directories.
     * @return boolean
     */
    public function sanitizeDir()
    {
        return $this->sanitizeDir;
    }
    /**
     * Check if the file extenstion may be processed.
     * @param string $filename
     * @return boolean
     */
    public function allowType($filename)
    {
        $fileData = pathinfo($filename);
        $fileName = $fileData['filename'];
        $fileExt = $fileData['extension'];
        $fileExt = strtolower($fileExt);

        if (in_array($fileExt, $this->config['fileTypes'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if the checkfile exists. If so add duplicate.
     * @param string  $filePath complete location of file
     * @param array $options //not used yet
     * @return string $fileName the new filename
     */
    public function checkFileExists($filePath, $options = array())
    {
        $fileData = pathinfo($filePath);
        $fileNameOld = $fileData['filename'];
        $fileBase = $fileData['basename'];
        $fileName = $fileData['filename'];
        $fileExt = $fileData['extension'];
        if (file_exists($filePath)) {
            $fileName = $fileName . $this->config['wordDelimiter'] . 'duplicate.' . $fileExt;
            $newFilePath = str_replace($fileNameOld . '.' . $fileExt, $fileName, $filePath);
            if (file_exists($newFilePath)) {
                $fileName = $this->checkFileExists($newFilePath);
            }
        } else {
            $fileName = $fileName . '.' . $fileExt;
        }
        return $fileName;
    }

    /**
     * Check if the filename has been changes. If it has been changed return true else return false;
     *
     * @uses FileSluggy::_FileNameSameAsOrginal
     * @since 1.1
     * @return boolean $this->_FileNameSameAsOrginal
     */
    public function checkFileNameChanged()
    {
        return $this->_FileNameSameAsOrginal ? false : true;
    }

    /**
     * Sanitatizes the filename.
     *
     * @param string $filePath Complete filelocation;
     * @param boolean $isdir Whether or not filePath is a directory;
     * @uses FileSluggy::_FileNameSameAsOrginal
     * @since 1.0
     * @version 1.1 Added check for filename changes;
     * @return mixed $newFilename new sanitized filename;
     */
    public function sanitizeName($filePath, $isdir = false)
    {
        $fileData = pathinfo($filePath);
        $fileName = $fileData['filename'];
        $fileExt = $fileData['extension'];

        $newFilename = '';
        /**
         * Add Prefix and Guid to the filename
         */
        if ($isdir) {
            $newFilename .= $fileName;
        } else {
            if (!empty($this->config['filenamePrefix'])) {
                $newFilename .= $this->config['filenamePrefix'] . $this->config['wordDelimiter'];
            }
            if ((boolean)$this->config['ignoreFilename'] && (boolean)$this->config['addGuid']) {
                $newFilename .= uniqid() . $this->config['wordDelimiter'];
            } elseif ((boolean)$this->config['ignoreFilename'] && (boolean)!$this->config['addGuid']) {
                $newFilename .= uniqid() . $this->config['wordDelimiter'];
            } elseif ((boolean)!$this->config['ignoreFilename'] && (boolean)$this->config['addGuid']) {
                $newFilename .= uniqid() . $this->config['wordDelimiter'] . $fileName;
            } else {
                $newFilename .= $fileName;
            }
            $newFilename = trim($newFilename, $this->config['wordDelimiter']);
        }

        /**
         *
         */
        if (!$this->SkipMB) {
            if (!mb_check_encoding($newFilename, 'UTF-8')) {
                $newFilename = mb_convert_encoding($newFilename, 'UTF-8');
            }
        }

        /**
         *  If possible execute iconv
         */
        if (!$this->SkipIconv) {
            setlocale(LC_ALL, strtolower($this->config['cultureKey']) . '_' . strtoupper($this->config['cultureKey']));
            if (iconv($this->config['encoding'], $this->config['charSet'], $newFilename)) {
                $newFilename = iconv($this->config['encoding'], $this->config['charSet'], $newFilename);
            } else {
                $this->modx->log(modX::LOG_LEVEL_ERROR, '[FileSluggy] Could not execute iconv on  ' . $newFilename);
            }
        }

        /**
         * Strip all spaces and double word delimters. Magic from MODX
         */
        /* replace one or more space characters with word delimiter */
        $newFilename = preg_replace('/\s+/u', $this->config['wordDelimiter'], $newFilename);
        /* replace one or more instances of word delimiters with word delimiter */
        $delimiterTokens = array();
        for ($d = 0; $d < strlen($this->config['wordDelimiters']); $d++) {
            $delimiterTokens[] = $this->config['wordDelimiters']{$d};
        }
        $delimiterPattern = '/[' . implode('|', $delimiterTokens) . ']+/';
        $newFilename = preg_replace($delimiterPattern, $this->config['wordDelimiter'], $newFilename);

        /** It is time for our own magic again */
        if ($this->config['lowerCase']) {
            $newFilename = strtolower($newFilename);
        }

        if (!empty($this->config['regExp'])) {
            if (preg_replace($this->config['regExp'], '', $newFilename)) {
                $newFilename = preg_replace($this->config['regExp'], '', $newFilename);
            }
        } else {
            $this->modx->log(
                modX::LOG_LEVEL_ERROR,
                '[FileSluggy] Could not execute preg_replace(' . $this->config['regExp'] . ') on  ' . $newFilename
            );
        }

        $newFilename = trim($newFilename);
        $newFilename = trim($newFilename, $this->config['wordDelimiter']);
        $newFilename = rtrim($newFilename, '.');
        if (empty($newFilename) || $newFilename == '') {
            $newFilename = uniqid();
        }
        if ($newFilename == $fileName) {
            $this->_FileNameSameAsOrginal = true;
        }
        if (!$isdir) {
            $newFilename .= '.' . $fileExt;
        }
        return $newFilename;
    }

    /**
     * Safely remove a directory.
     * @param $source
     * @param $path
     * @return bool
     */
    public function removeDirectoryIfEmpty($source, $path)
    {
        if (count($source->getFilesystem()->listContents($path)) > 0) {
            $this->modx->log(xPDO::LOG_LEVEL_ERROR, '[FileSluggy.removeDirectoryIfEmpty] Cannot remove directory because it is not empty: ' . $path);

            return false;
        }

        return $source->getFileSystem()->deleteDir($path);
    }

    /**
     * @param object $source
     * @param string $oldPath
     * @param string $newName
     * @return bool
     */
    public function renameContainer($source, $oldPath, $newName)
    {
        if (version_compare($this->modx->getOption('settings_version'), '3') >= 0) {
            /* Format old and new paths to prevent mismatches based on / or ., such as ./directory/ and /directory/. */
            $oldPath = trim($oldPath, '/');
            $newPath = rtrim(dirname($oldPath), '/') . '/' . $newName;
            $newPath = ltrim($newPath, './');

            /* If the old path equals the new path, nothing needs to be renamed because these paths are exactly the same. This occurs when creating a new folder with a name such as parent. */
            if ($oldPath === $newPath) {
                return false;
            }

            /** If that path equals to the sanitized path and that already exists, then we remove the old path. For example: "PARENT " while "parent" already exists. */
            if (strtolower(trim($oldPath)) === $newPath && strlen($oldPath) > strlen($newPath) && $source->getFilesystem()->has($newPath)) {
                return $this->removeDirectoryIfEmpty($source, $oldPath);
            }

            $oldPathInfo          = pathinfo($oldPath);
            $fullSanitizedOldPath = $oldPathInfo['dirname'] . '/' . $this->sanitizeName($oldPath, true);
            /* If the old sanitized name equals the new path, we need to rename. Example: PARENT --> parent. */
            if ($oldPath !== $newPath && $fullSanitizedOldPath === $newPath) {
                try {
                    $tmpName = $newPath . '-tmp-' . time();
                    $source->getFilesystem()->rename($oldPath, $tmpName);

                    $source->getFilesystem()->getAdapter()->getCache()->flush();

                    return $source->getFilesystem()->rename($tmpName, $newPath);
                } catch (Exception $exception) {
                    /* If the new path already exists, then we only need to remove the old path. So parent already exists, so remove old directory PARENT. */
                    return $this->removeDirectoryIfEmpty($source, $oldPath);
                }
            }

            /* Simply rename the directory. */
            try {
                return $source->getFilesystem()->rename($oldPath, $newPath);
            } catch (Exception $exception) {
                $tmpName = $newPath . '-tmp-' . time();
                $source->getFilesystem()->rename($oldPath, $tmpName);

                $source->getFilesystem()->getAdapter()->getCache()->flush();

                try {
                    return $source->getFilesystem()->rename($tmpName, $newPath);
                } catch (Exception $exception) {
                    $this->modx->log(xPDO::LOG_LEVEL_ERROR, '[FileSluggy] Failed renaming directory: ' . $exception->getMessage());
                }
            }
        } else {
            $bases = $source->getBases($oldPath);
            $oldPath = $bases['pathAbsolute'] . $oldPath;
            /** @var modDirectory $oldDirectory */
            $oldDirectory = $source->fileHandler->make($oldPath);

            /* make sure is a directory and writable */
            if (!($oldDirectory instanceof modDirectory)) {
                return false;
            }
            if (!$oldDirectory->isReadable() || !$oldDirectory->isWritable()) {
                return false;
            }

            /* sanitize new path */
            $newPath = $source->fileHandler->sanitizePath($newName);
            $newPath = $source->fileHandler->postfixSlash($newPath);
            $newPath = dirname($oldPath) . '/' . $newPath;
            /* rename the dir */
            if (!$oldDirectory->rename($newPath)) {
                return false;
            }
        }

        return true;
    }
}
