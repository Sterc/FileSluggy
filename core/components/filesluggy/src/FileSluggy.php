<?php
namespace Sterc\FileSluggy;

use MODX\Revolution\modX;
use MODX\Revolution\File\modFileHandler;
use MODX\Revolution\File\modDirectory;

class FileSluggy
{
    /** @var modX $modx */
    public $modx;

    /** @var array $config */
    public $config = [];
    private $fileNameSameAsOrginal = false;
    private $mediaSourceAllow = null;

    protected $skipIconv = false;
    protected $skipMB = false;

    /**
     * @param modX $modx
     * @param array $config
     */
    public function __construct(modX &$modx, array $config = [])
    {
        $this->modx      = & $modx;
        $this->skipIconv = function_exists('iconv') ? false : true;
        $this->skipMB    = function_exists('mb_check_encoding') ? false : true;

        $corePath = $this->modx->getOption('filesluggy.core_path', $config, $this->modx->getOption('core_path') . 'components/filesluggy/');

        $this->config = array_merge([
            'corePath'          => $corePath,
            'charSet'           => $this->modx->getOption('charset_iconv', $config, $this->modx->getOption('filesluggy.charset_iconv', null, 'US-ASCII//TRANSLIT')),
            'encoding'          => strtoupper($this->modx->getOption('enc', $config, $this->modx->getOption('filesluggy.enc', null, 'UTF-8'))),
            'regExp'            => $this->modx->getOption('regexp', $config, $this->modx->getOption('filesluggy.regexp', null, '/[^\.A-Za-z0-9 _-]/')),
            'addGuid'           => (int) $this->modx->getOption('guid_use', $config, $this->modx->getOption('filesluggy.guid_use', null, 0)),
            'filenamePrefix'    => (string) trim($this->modx->getOption('filenamePrefix', $config, $this->modx->getOption('filesluggy.filename_prefix', null, ''))),
            'ignoreFilename'    => (int) $this->modx->getOption('ignoreFilename', $config, $this->modx->getOption('filesluggy.ignorefilename', null, 0)), // Replaces the whole file name with a guid.
            'wordDelimiter'     => $this->modx->getOption('word_delimiter', $config, $this->modx->getOption('filesluggy.word_delimiter', null, '-')),
            'lowerCase'         => (boolean) $this->modx->getOption('lowercase_only', $config, $this->modx->getOption('filesluggy.lowercase_only', null, 1)),
            'cultureKey'        => $this->modx->getOption('cultureKey', null, 'en'),
            'sanitizeDir'       => $this->modx->getOption('sanitizeDir', $config, $this->modx->getOption('filesluggy.sanitizeDir', null, false)),
            'fileTypes'         => $this->modx->getOption('allowed_file_types', $config, $this->modx->getOption('filesluggy.allowed_file_types', null, 'jpg,jpeg,png,gif,psd,ico,bmp,svg,doc,docx,pdf'))
        ], $config);

        if (!in_array($this->config['wordDelimiter'], ['-', '_'])) {
            $this->config['wordDelimiter'] = '-';
        }

        $this->config['wordDelimiters'] = '-_';
        if (!empty($this->config['fileTypes'])) {
            $tmp = preg_replace('/\s+/u', '', $this->config['fileTypes']);
            $tmp = strtolower($tmp);
            $tmp = explode(',', $this->config['fileTypes']);
            foreach ($tmp as $data) {
                $tmpArr[] = trim($data);
            }

            if (is_array($tmpArr)) {
                $this->config['fileTypes'] = $tmpArr;
            }
        }

        $constrainMediaSource = $this->modx->getOption('constrain_mediasource', $config, $this->modx->getOption('filesluggy.constrain_mediasource', null, null));
        if (!empty($constrainMediaSource)) {
            $arrMS = explode(',', $constrainMediaSource);
            if (is_array($arrMS) && count($arrMS) > 0) {
                $this->mediaSourceAllow = $arrMS;
            } else {
                $this->mediaSourceAllow = null;
            }
        }

        $this->modx->lexicon->load('filesluggy:default');
    }

    /**
     *
     * @param int $sourceID sourceID where files are uploaded into
     * @return boolean TRUE allow filesluggy || FALSE prohibit filesluggy
     */
    public function santizeAllowThisMediaSource($sourceID = null)
    {
        if (empty($this->mediaSourceAllow)) {
            return true;
        }

        if (!empty($sourceID)) {
            if (in_array($sourceID, $this->mediaSourceAllow)) {
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
        $fileExt  = strtolower($fileData['extension'] ?? '');

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
    public function checkFileExists($filePath, $options = [])
    {
        $fileData    = pathinfo($filePath);
        $fileNameOld = $fileData['filename'];
        $fileName    = $fileData['filename'];
        $fileExt     = $fileData['extension'];

        if (file_exists($filePath)) {
            $fileName    = $fileName . $this->config['wordDelimiter'] . 'duplicate.' . $fileExt;
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
     * Check if the filename has been changes. If it has been changed return true else return false.
     * @return boolean $this->fileNameSameAsOrginal
     */
    public function checkFileNameChanged()
    {
        return $this->fileNameSameAsOrginal ? false : true;
    }

    /**
     * Sanitatizes the filename.
     *
     * @param string $filePath Complete filelocation;
     * @param boolean $isdir Whether or not filePath is a directory;
     * @return mixed $newFilename new sanitized filename;
     */
    public function sanitizeName($filePath, $isdir = false)
    {
        $fileData    = pathinfo($filePath);
        $fileName    = $fileData['filename'];
        $fileExt     = $fileData['extension'];
        $newFilename = '';

        /**
         * Add Prefix and Guid to the filename.
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

        if (!$this->skipMB) {
            if (!mb_check_encoding($newFilename, 'UTF-8')) {
                $newFilename = mb_convert_encoding($newFilename, 'UTF-8');
            }
        }

        /* If possible execute iconv. */
        if (!$this->skipIconv) {
            setlocale(LC_ALL, strtolower($this->config['cultureKey']) . '_' . strtoupper($this->config['cultureKey']));
            if (iconv($this->config['encoding'], $this->config['charSet'], $newFilename)) {
                $newFilename = iconv($this->config['encoding'], $this->config['charSet'], $newFilename);
            } else {
                $this->modx->log(modX::LOG_LEVEL_ERROR, '[FileSluggy] Could not execute iconv on ' . $newFilename);
            }
        }

        /**
         * Strip all spaces and double word delimiters. Magic from MODX
         */
        /* replace one or more space characters with word delimiter */
        $newFilename = preg_replace('/\s+/u', $this->config['wordDelimiter'], $newFilename);
        /* replace one or more instances of word delimiters with word delimiter */
        $delimiterTokens = array();
        for ($d = 0; $d < strlen($this->config['wordDelimiters']); $d++) {
            $delimiterTokens[] = $this->config['wordDelimiters'][$d];
        }
        $delimiterPattern = '/[' . implode('|', $delimiterTokens) . ']+/';
        $newFilename      = preg_replace($delimiterPattern, $this->config['wordDelimiter'], $newFilename);

        /* It is time for our own magic again. */
        if ($this->config['lowerCase']) {
            $newFilename = strtolower($newFilename);
        }

        if (!empty($this->config['regExp'])) {
            if (preg_replace($this->config['regExp'], '', $newFilename)) {
                $newFilename = preg_replace($this->config['regExp'], '', $newFilename);
            }
        } else {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[FileSluggy] Could not execute preg_replace(' . $this->config['regExp'] . ') on  ' . $newFilename);
        }

        $newFilename = trim($newFilename);
        $newFilename = trim($newFilename, $this->config['wordDelimiter']);
        $newFilename = rtrim($newFilename, '.');
        if (empty($newFilename) || $newFilename == '') {
            $newFilename = uniqid();
        }

        if ($newFilename == $fileName) {
            $this->fileNameSameAsOrginal = true;
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
        $bases       = $source->getBases($oldPath);
        $oldPath     = $bases['pathAbsolute'] . $oldPath;
        $fileHandler = new modFileHandler($this->modx, ['context' => $this->modx->context->get('key')]);

        /** @var modDirectory $oldDirectory */
        $oldDirectory = $fileHandler->make($oldPath);

        /* Make sure is a directory and writable. */
        if (!($oldDirectory instanceof modDirectory) || !$oldDirectory->isReadable() || !$oldDirectory->isWritable()) {
            return false;
        }

        /* Sanitize new path. */
        $newPath = $fileHandler->sanitizePath($newName);
        $newPath = $fileHandler->postfixSlash($newPath);
        $newPath = dirname($oldPath) . '/' . $newPath;

        /* Rename the dir. */
        if (!$oldDirectory->rename($newPath)) {
            return false;
        }

        return true;
    }
}
