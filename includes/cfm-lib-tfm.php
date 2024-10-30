<?php
class CCfmlibtinyfilemanager{
	/**
	 * Get mime types of text files
	 * @return array
	 */
	public static function fm_get_text_mimes()
	{
	    return array(
	        'application/xml',
	        'application/javascript',
	        'application/x-javascript',
	        'image/svg+xml',
	        'message/rfc822',
	    );
	}

	/**
	 * Get text file extensions
	 * @return array
	 */
	public static function fm_get_text_exts()
	{
	    return array(
	        'txt', 'css', 'ini', 'conf', 'log', 'htaccess', 'passwd', 'ftpquota', 'sql', 'js', 'json', 'sh', 'config',
	        'php', 'php4', 'php5', 'phps', 'phtml', 'htm', 'html', 'shtml', 'xhtml', 'xml', 'xsl', 'm3u', 'm3u8', 'pls', 'cue',
	        'eml', 'msg', 'csv', 'bat', 'twig', 'tpl', 'md', 'gitignore', 'less', 'sass', 'scss', 'c', 'cpp', 'cs', 'py',
	        'map', 'lock', 'dtd', 'svg',
	    );
	}
	/**
	 * Get CSS classname for file
	 * @param string $path
	 * @return string
	 */
	public static function fm_get_file_icon_class($path)
	{
	    // get extension
	    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

	    switch ($ext) {
	        case 'ico':
	        case 'gif':
	        case 'jpg':
	        case 'jpeg':
	        case 'jpc':
	        case 'jp2':
	        case 'jpx':
	        case 'xbm':
	        case 'wbmp':
	        case 'png':
	        case 'bmp':
	        case 'tif':
	        case 'tiff':
	        case 'svg':
	            $img = 'fa fa-picture-o';
	            break;
	        case 'passwd':
	        case 'ftpquota':
	        case 'sql':
	        case 'js':
	        case 'json':
	        case 'sh':
	        case 'config':
	        case 'twig':
	        case 'tpl':
	        case 'md':
	        case 'gitignore':
	        case 'c':
	        case 'cpp':
	        case 'cs':
	        case 'py':
	        case 'map':
	        case 'lock':
	        case 'dtd':
	            $img = 'fa fa-file-code-o';
	            break;
	        case 'txt':
	        case 'ini':
	        case 'conf':
	        case 'log':
	        case 'htaccess':
	            $img = 'fa fa-file-text-o';
	            break;
	        case 'css':
	        case 'less':
	        case 'sass':
	        case 'scss':
	            $img = 'fa fa-css3';
	            break;
	        case 'zip':
	        case 'rar':
	        case 'gz':
	        case 'tar':
	        case '7z':
	            $img = 'fa fa-file-archive-o';
	            break;
	        case 'php':
	        case 'php4':
	        case 'php5':
	        case 'phps':
	        case 'phtml':
	            $img = 'fa fa-code';
	            break;
	        case 'htm':
	        case 'html':
	        case 'shtml':
	        case 'xhtml':
	            $img = 'fa fa-html5';
	            break;
	        case 'xml':
	        case 'xsl':
	            $img = 'fa fa-file-excel-o';
	            break;
	        case 'wav':
	        case 'mp3':
	        case 'mp2':
	        case 'm4a':
	        case 'aac':
	        case 'ogg':
	        case 'oga':
	        case 'wma':
	        case 'mka':
	        case 'flac':
	        case 'ac3':
	        case 'tds':
	            $img = 'fa fa-music';
	            break;
	        case 'm3u':
	        case 'm3u8':
	        case 'pls':
	        case 'cue':
	            $img = 'fa fa-headphones';
	            break;
	        case 'avi':
	        case 'mpg':
	        case 'mpeg':
	        case 'mp4':
	        case 'm4v':
	        case 'flv':
	        case 'f4v':
	        case 'ogm':
	        case 'ogv':
	        case 'mov':
	        case 'mkv':
	        case '3gp':
	        case 'asf':
	        case 'wmv':
	            $img = 'fa fa-file-video-o';
	            break;
	        case 'eml':
	        case 'msg':
	            $img = 'fa fa-envelope-o';
	            break;
	        case 'xls':
	        case 'xlsx':
	            $img = 'fa fa-file-excel-o';
	            break;
	        case 'csv':
	            $img = 'fa fa-file-text-o';
	            break;
	        case 'bak':
	            $img = 'fa fa-clipboard';
	            break;
	        case 'doc':
	        case 'docx':
	            $img = 'fa fa-file-word-o';
	            break;
	        case 'ppt':
	        case 'pptx':
	            $img = 'fa fa-file-powerpoint-o';
	            break;
	        case 'ttf':
	        case 'ttc':
	        case 'otf':
	        case 'woff':
	        case 'woff2':
	        case 'eot':
	        case 'fon':
	            $img = 'fa fa-font';
	            break;
	        case 'pdf':
	            $img = 'fa fa-file-pdf-o';
	            break;
	        case 'psd':
	        case 'ai':
	        case 'eps':
	        case 'fla':
	        case 'swf':
	            $img = 'fa fa-file-image-o';
	            break;
	        case 'exe':
	        case 'msi':
	            $img = 'fa fa-file-o';
	            break;
	        case 'bat':
	            $img = 'fa fa-terminal';
	            break;
	        default:
	            $img = 'fa fa-info-circle';
	    }

	    return $img;
	}
	/**
	 * Convert file name to UTF-8 in Windows
	 * @param string $filename
	 * @return string
	 */
	public static function fm_convert_win($filename,$is_win)
	{
	    if ($is_win && function_exists('iconv')) {
	        $filename = iconv( 'UTF-8', 'UTF-8//IGNORE', $filename);
	    }
	    return $filename;
	}

	/**
	 * Check if string is in UTF-8
	 * @param string $string
	 * @return int
	 */
	public static function fm_is_utf8($string)
	{
	    return preg_match('//u', $string);
	}

	/**
	 * Encode html entities
	 * @param string $text
	 * @return string
	 */
	public static function fm_enc($text)
	{
	    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
	}

	/**
	 * Get info about zip archive
	 * @param string $path
	 * @return array|bool
	 */
	public static function fm_get_zif_info($path, $ext) {
	    if ($ext == 'zip' && function_exists('zip_open')) {
	        $arch = zip_open($path);
	        if ($arch) {
	            $filenames = array();
	            while ($zip_entry = zip_read($arch)) {
	                $zip_name = zip_entry_name($zip_entry);
	                $zip_folder = substr($zip_name, -1) == '/';
	                $filenames[] = array(
	                    'name' => $zip_name,
	                    'filesize' => zip_entry_filesize($zip_entry),
	                    'compressed_size' => zip_entry_compressedsize($zip_entry),
	                    'folder' => $zip_folder
	                    //'compression_method' => zip_entry_compressionmethod($zip_entry),
	                );
	            }
	            zip_close($arch);
	            return $filenames;
	        }
	    } elseif($ext == 'tar' && class_exists('PharData')) {
	        $archive = new PharData($path);
	        $filenames = array();
	        foreach(new RecursiveIteratorIterator($archive) as $file) {
	            $parent_info = $file->getPathInfo();
	            $zip_name = str_replace("phar://".$path, '', $file->getPathName());
	            $zip_name = substr($zip_name, ($pos = strpos($zip_name, '/')) !== false ? $pos + 1 : 0);
	            $zip_folder = $parent_info->getFileName();
	            $zip_info = new SplFileInfo($file);
	            $filenames[] = array(
	                'name' => $zip_name,
	                'filesize' => $zip_info->getSize(),
	                'compressed_size' => $file->getCompressedSize(),
	                'folder' => $zip_folder
	            );
	        }
	        return $filenames;
	    }
	    return false;
	}
	/**
	 * Get nice filesize
	 * @param int $size
	 * @return string
	 */
	public static function fm_get_filesize($size)
	{
	    if ($size < 1000) {
	        return sprintf('%s B', $size);
	    } elseif (($size / 1024) < 1000) {
	        return sprintf('%s KB', round(($size / 1024), 2));
	    } elseif (($size / 1024 / 1024) < 1000) {
	        return sprintf('%s MB', round(($size / 1024 / 1024), 2));
	    } elseif (($size / 1024 / 1024 / 1024) < 1000) {
	        return sprintf('%s GB', round(($size / 1024 / 1024 / 1024), 2));
	    } else {
	        return sprintf('%s TB', round(($size / 1024 / 1024 / 1024 / 1024), 2));
	    }
	}

	/**
	 * Get parent path
	 * @param string $path
	 * @return bool|string
	 */
	public static function fm_get_parent_path($path)
	{
	    $path = CCfmlibtinyfilemanager::fm_clean_path($path);
	    if ($path != '') {
	        $array = explode('/', $path);
	        if (count($array) > 1) {
	            $array = array_slice($array, 0, -1);
	            return implode('/', $array);
	        }
	        return '';
	    }
	    return false;
	}
	/**
	 * Clean path
	 * @param string $path
	 * @return string
	 */
	public static function fm_clean_path($path)
	{
	    $path = trim($path);
	    $path = str_replace(array('../', '..\\'), '', $path);
	    if ($path == '..') {
	        $path = '';
	    }
	    return str_replace('\\', '/', $path);
	}

	/**
	 * Get mime type
	 * @param string $file_path
	 * @return mixed|string
	 */
	public static function fm_get_mime_type($file_path)
	{
	    if (function_exists('finfo_open')) {
	        $finfo = finfo_open(FILEINFO_MIME_TYPE);
	        $mime = finfo_file($finfo, $file_path);
	        finfo_close($finfo);
	        return $mime;
	    } elseif (function_exists('mime_content_type')) {
	        return mime_content_type($file_path);
	    } elseif (!stristr(ini_get('disable_functions'), 'shell_exec')) {
	        $file = escapeshellarg($file_path);
	        $mime = shell_exec('file -bi ' . $file);
	        return $mime;
	    } else {
	        return '--';
	    }
	}

	/**
	 * Delete  file or folder (recursively)
	 * @param string $path
	 * @return bool
	 */
	public static function fm_rdelete($path)
	{
	    if (is_link($path)) {
	        return unlink($path);
	    } elseif (is_dir($path)) {
	        $objects = scandir($path);
	        $ok = true;
	        if (is_array($objects)) {
	            foreach ($objects as $file) {
	                if ($file != '.' && $file != '..') {
	                    if (!CCfmlibtinyfilemanager::fm_rdelete($path . '/' . $file)) {
	                        $ok = false;
	                    }
	                }
	            }
	        }
	        return ($ok) ? rmdir($path) : false;
	    } elseif (is_file($path)) {
	        return unlink($path);
	    }
	    return false;
	}
	
	// Time format is UNIX timestamp or
    // PHP strtotime compatible strings
    public static function fm_get_time_remain($time1, $time2, $precision = 6) {
    	$date1 = new DateTime(date('Y-m-d H:i',$time1));
		$date2 = new DateTime(date('Y-m-d H:i',$time2));
		
		$date3 = $date1->diff( $date2 );
		$out = '';
		if( $date3->d > 0 ) $out .= $date3->d.' days ';
		if( $date3->h > 0 ) $out .= $date3->h.' hours ';
		if( $date3->i > 0 ) $out .= $date3->i.' minutes ';
		
		return trim($out);
    }
}
?>