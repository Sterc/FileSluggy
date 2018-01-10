<?php
/**
 * Default English Lexicon Entries for FileSluggy
 *
 * @package filesluggy
 * @subpackage lexicon
 */

$_lang['setting_filesluggy.charset_iconv'] = "Filename Transliteration";
$_lang['setting_filesluggy.charset_iconv_desc'] = 'Which type of Transliteration is used by FileSluggy.';
$_lang['setting_filesluggy.enc'] = "Filename Encoding";
$_lang['setting_filesluggy.enc_desc'] = 'Type of encoding used for the transliteration.';
$_lang['setting_filesluggy.regexp'] = "Clean filename regular expression";
$_lang['setting_filesluggy.regexp_desc'] = 'The regular expression for stripping unwanted characters';
$_lang['setting_filesluggy.guid_use'] = "Add Unique ID";
$_lang['setting_filesluggy.guid_use_desc'] = 'If \'Yes\' add an unique ID after the prefix but before the filename.';
$_lang['setting_filesluggy.filename_prefix'] = "Filename Prefix";
$_lang['setting_filesluggy.filename_prefix_desc'] = 'Prefix that will be added before the filename. Prefix will also be processed.';
$_lang['setting_filesluggy.ignorefilename'] = "Ignore Filename";
$_lang['setting_filesluggy.ignorefilename_desc'] = 'Removes the filename from the output, but will add an unique ID and the prefix to the output.  ';
$_lang['setting_filesluggy.word_delimiter'] = "Word delimiter";
$_lang['setting_filesluggy.word_delimiter_desc'] = 'The preferred word delimiter for the filename slugs, only use - (dash) or _ (lowerdash). All other chars will be set to - (dash)';
$_lang['setting_filesluggy.lowercase_only'] = 'Lowercase only';
$_lang['setting_filesluggy.lowercase_only_desc'] = 'Force output put to be in lower case';
$_lang['setting_filesluggy.allowed_file_types'] = 'Filetypes';
$_lang['setting_filesluggy.allowed_file_types_desc'] = 'Only filetypes with this extension will be processed. Comma separated value';
$_lang['setting_filesluggy.constrain_mediasource'] = 'Constrain to MediaSource';
$_lang['setting_filesluggy.constrain_mediasource_desc'] = 'To which mediasource you would like to constrain FileSluggy. Leave empty to allow on all.  Enter the ID\'s of the media sources Comma separated value';
$_lang['setting_filesluggy.sanitizeDir'] = 'Sanitize directories';
$_lang['setting_filesluggy.sanitizeDir_desc'] = 'When enabled, FileSluggy also sanitizes directories when created or renamed.';
$_lang['setting_filesluggy.triggerFSOUFEventOnNoRename'] = 'Force FileSluggyOnUpdateFile event';
$_lang['setting_filesluggy.triggerFSOUFEventOnNoRename_desc'] = 'Force the FileSluggyOnUpdateFile event to trigger even if a file rename wasn\'t required';

$_lang['area_fs_encoding'] = 'Transliteration Settings';
$_lang['area_fs_guid'] = 'Output settings';
$_lang['area_fs_type'] = 'File types';
$_lang['area_fs_events'] = 'Events';
