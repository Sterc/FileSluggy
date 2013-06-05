<?php

/**
 * 
 */
class FileSluggy {

    /** @var \modX $modx */
    public $modx;

    /** @var array $config */
    public $config = array();

    function __construct(modX &$modx, array $config = array()) {
        $this->modx = & $modx;

        $corePath = $this->modx->getOption('filesluggy.core_path', $config, $this->modx->getOption('core_path') . 'components/filesluggy/');
        $charSet = $this->modx->getOption('charset_iconv', $config, $this->modx->getOption('filesluggy.charset_iconv', null, 'US-ASCII//TRANSLIT'));
        $Encoding = strtoupper($this->modx->getOption('enc', $config, $this->modx->getOption('filesluggy.enc', null, 'UTF-8')));
        $RegExp = $this->modx->getOption('regexp', $config, $this->modx->getOption('filesluggy.regexp', null, '/[^\.A-Za-z0-9 _-]/'));
        $AddGUID = (boolean) $this->modx->getOption('guid_use', $config, $this->modx->getOption('filesluggy.guid_use', null, 0));
        $fileNamePrefix = (string) $this->modx->getOption('filenamePrefix', $config, $this->modx->getOption('filesluggy.filename_prefix', null, ''));
        $ignoreFilename = (boolean) $this->modx->getOption('ignoreFilename', $config, $this->modx->getOption('filesluggy.ignorefilename', null, 0)); // Replaces the whole file name with a guid.
        $Delimiter = $this->modx->getOption('word_delimiter', $config, $this->modx->getOption('filesluggy.word_delimiter', null, '-'));
        $fileTypes = $this->modx->getOption('allowed_file_types', $config, $this->modx->getOption('filesluggy.allowed_file_types', null, 'jpg,jpeg,png,gif,psd,ico,bmp,svg,doc,docx,pdf'));
        $LowerCaseOnly = (boolean) $this->modx->getOption('lowercase_only', $config, $this->modx->getOption('filesluggy.lowercase_only', null, 1));

        $cultureKey = $this->modx->getOption('cultureKey', null, 'en');
        $this->SkipIconv = function_exists('iconv') ? false : true;
		$this->SkipMB = function_exists('mb_check_encoding')?false:true;

        $connectorUrl = $assetsUrl . 'connector.php';

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
            
            foreach($tmp as $data){
	            $tmpArr[] = trim($data);
            }
            if (is_array($tmpArr)) {
                $this->config['fileTypes'] = $tmpArr;
            }
        }


        $this->modx->addPackage('poimanager', $this->config['modelPath']);
        $this->modx->lexicon->load('poimanager:default');
    }

    public function allowType($filename) {
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

    public function checkFileExists($filePath) {
        $fileData = pathinfo($filePath);
        $fileNameOld = $fileData['filename'];
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

    public function sanitizeName($input) {
        $fileData = pathinfo($input);
        $fileName = $fileData['filename'];
        $fileExt = $fileData['extension'];

        if ($this->allowType($input)) {
            $newFilename = '';
            /**
             * Add Prefix and Guid to the filename
             */
            if (!empty($this->config['filenamePrefix'])) {
                $newFilename .= $this->config['filenamePrefix'] . $this->config['wordDelimiter'];
            }

            if ((boolean) $this->config['ignoreFilename'] && (boolean) $this->config['addGuid']) {

                $newFilename .= uniqid() . $this->config['wordDelimiter'];
            } elseif ((boolean) $this->config['ignoreFilename'] && (boolean) !$this->config['addGuid']) {
                $newFilename .= uniqid() . $this->config['wordDelimiter'];
            } elseif ((boolean) !$this->config['ignoreFilename'] && (boolean) $this->config['addGuid']) {
                $newFilename .=uniqid() . $this->config['wordDelimiter'] . $fileName;
            } else {
                $newFilename .= $fileName;
            }
            $newFilename = trim($newFilename, $this->config['wordDelimiter']);


			/**
			 *
			 */
			 
			if(!$this->SkipMB){
				if(!mb_check_encoding($newFilename,'UTF-8')){
					$newFilename = mb_convert_encoding($newFilename,'UTF-8');
				}
			}
	
            /**
             * 	If possible execute iconv
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
                $this->modx->log(modX::LOG_LEVEL_ERROR, '[FileSluggy] Could not execute preg_replace(' . $this->config['regExp'] . ') on  ' . $newFilename);
            }

            $newFilename = trim($newFilename);
            $newFilename = trim($newFilename, $this->config['wordDelimiter']);
            $newFilename = rtrim($newFilename, '.');
            if(empty($newFilename) || $newFilename == '') $newFilename = uniqid();
            return $newFilename . '.' . $fileExt;
        } else {
            return false;
        }
    }

}