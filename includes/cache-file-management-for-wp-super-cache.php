<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class CahceFileManagement_for_WPSuperCache {

	/**
	 * The single instance of CahceFileManagement_for_WPSuperCache.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * Settings class object
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = null;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/** locallization text domain */
	public $localisation_domain;
	/** wp super cache base dir */
	public $wpsc_basedir;
	/** excluded files to be shown */
	public $exclude_items = array();
	public $FM_ROOT_URL;
	public $FM_SELF_URL;
	public $FM_ROOT_PATH;
	public $FM_SHOW_HIDDEN = false;
	public $FM_READONLY = false;
	public $FM_IS_WIN = (DIRECTORY_SEPARATOR == '\\');
	public $FM_ICONV_INPUT_ENC = 'UTF-8';
	public $FM_DATETIME_FORMAT = 'd/M/Y H:i';
	public $FM_PATH;
	public $wpsc_cache_max_time;
	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct ( $file = '', $version = '1.0.0' ) {
		$this->_version = $version;
		$this->_token = 'bbpress_improvements_for_yoast';

		// Load plugin environment variables
		$this->file = $file;
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );
		$this->localisation_domain = 'cache-file-management-for-wp-super-cache';
		$this->wpsc_basedir = trailingslashit( CCfmlibtinyfilemanager::fm_clean_path(WP_CONTENT_DIR . '/cache/') );
		
		// Check if wp super cache exist and activated.
		if( WP_CACHE === false || !@is_file( WP_CONTENT_DIR . '/wp-cache-config.php' ) || !is_plugin_active( 'wp-super-cache/wp-cache.php' ) ) {
			// wp super cache not exist or not installed, we do nothing.
			return;
		} 
		if( @is_file( WP_CONTENT_DIR . '/wp-cache-config.php' ) ){
			@include( WP_CONTENT_DIR . '/wp-cache-config.php' );
			$this->wpsc_cache_max_time = $cache_max_time;
		}

		$this->FM_ROOT_URL = site_url();
		$this->FM_SELF_URL = admin_url('options-general.php?page=cache-file-management-for-wp-super-cache');
		$this->FM_ROOT_PATH = trailingslashit(CCfmlibtinyfilemanager::fm_clean_path( WP_CONTENT_DIR . '/cache/supercache/' .parse_url( $this->FM_ROOT_URL,PHP_URL_HOST ) ));
		$p = isset($_GET['p']) ? $_GET['p'] : (isset($_POST['p']) ? (($_POST['p'])) : '');
		// clean path
		$p = str_replace( '..', '', preg_replace( '/:.*$/', '', CCfmlibtinyfilemanager::fm_clean_path($p) ) );
		$this->FM_PATH = $p;
		
		// Handle localisation
		add_action( 'init', array( $this, 'load_localisation' ), 0 );
		// Add a menu to settings
		add_action('admin_menu', array( $this, 'add_admin_menu') );
	
	} // End __construct ()
	
	/** Add a menu to settings */
	public function add_admin_menu(){
		if (current_user_can('manage_options'))
			add_options_page("Cache File Management", __("Cache File Management", $this->localisation_domain ), 'manage_options', 'cache-file-management-for-wp-super-cache', array( $this, 'management') );
	}
	/** delete a cache file/folder */
	public function delete_file(){
		// Delete file / folder
		if ( function_exists( 'current_user_can' ) && false == current_user_can( 'delete_others_posts' ) ) {
			return __('You do not have the permission to delete cache.', $this->localisation_domain);
		}
		
		if (isset($_GET['del']) && !$this->FM_READONLY) {
			$req_path    = filter_input( INPUT_GET, 'del' );
			$valid_nonce = ( $req_path && isset( $_GET['_wpnonce'] ) ) ? wp_verify_nonce( $_GET['_wpnonce'], 'delete-cache' ) : false;
			if( !$valid_nonce )
				return __('You do not have the permission to delete cache.', $this->localisation_domain);
			
		    $del = str_replace( '/', '', CCfmlibtinyfilemanager::fm_clean_path( $req_path ) );
		    $del =  str_replace( '..', '', preg_replace( '/:.*$/', '', $del ) );
		    
		    if( $valid_nonce  && $del != '' && $del != '..' && $del != '.') {
		    	
		        $path = trailingslashit($this->FM_ROOT_PATH);
		        if ($this->FM_PATH != '') {
		            $path .= $this->FM_PATH;
		        }
		        $path = trailingslashit($path );
		        $path .= $del;
		        if( $this->is_protected_file( $path )){
		        	return __('Delete Cache File: Can not delete the protected file.', $this->localisation_domain);
		        }
		        if( CCfmlibtinyfilemanager::fm_rdelete( $path ) )
		        	return sprintf( __('File: %s has been deleted.', $this->localisation_domain) , $del );
		        else 
		        	return sprintf( __('File: %s can not be deleted.', $this->localisation_domain) , $del );
		    } 
		}
		return '';
	}
	/** delete multiple files/folders */
	public function mass_delete_file(){
		// Mass deleting
		if ( function_exists( 'current_user_can' ) && false == current_user_can( 'delete_others_posts' ) ) {
			return __('You do not have the permission to delete cache.', $this->localisation_domain);
		}
		
		if (isset($_POST['group'], $_POST['delete']) && !$this->FM_READONLY) {
		    $path = trailingslashit( $this->FM_ROOT_PATH) ;
		    if ($this->FM_PATH != '') {
		        $path .= $this->FM_PATH;
		    }

			$path = trailingslashit($path );
		    $errors = 0;
		    $files = $_POST['file'];
		    $deleted = 0;
		    if (is_array($files) && count($files)) {
		        foreach ($files as $f) {
		            if ($f != '') {
		                $new_path = $path . str_replace( '..', '', preg_replace( '/:.*$/', '', $f ) );
		                
		                if( $this->is_protected_file( $new_path )){
				        	return __('Delete Cache File: Can not delete the protected file.', $this->localisation_domain);
				        }
		                if (!CCfmlibtinyfilemanager::fm_rdelete($new_path)) {
		                    $errors++;
		                }
		                
		                $deleted ++;
		            }
		        }
		    }
		    
		    return sprintf( __('%d files were deleted.', $this->localisation_domain) , $deleted );
		}
	}
	/** view cache file */
	public function view_file(){
		// file viewer
		if (isset($_GET['view'])) {
			$path = trailingslashit($this->FM_ROOT_PATH);
			if ($this->FM_PATH != '') {
			    $path .= $this->FM_PATH;
			}
			$path = trailingslashit( $path );
		    $file = str_replace( '..', '', preg_replace( '/:.*$/', '', $_GET['view'] ) );
		    $file = CCfmlibtinyfilemanager::fm_clean_path($file);
		    $file = str_replace('/', '', $file);
		    if ($file == '' || !is_file($path . '/' . $file)) {
		        return __('View Cache File: File not exists', $this->localisation_domain);
		    }

		    $this->fm_show_header(); // HEADER
		    $this->fm_show_nav_path($this->FM_PATH); // current path

		    $file_url = $this->FM_ROOT_URL . CCfmlibtinyfilemanager::fm_convert_win(($this->FM_PATH != '' ? '/' . $this->FM_PATH : '') . '/' . $file, $this->FM_IS_WIN );
		    $file_path = $path . $file;

		    $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
		    $mime_type = CCfmlibtinyfilemanager::fm_get_mime_type($file_path);
		    $filesize = filesize($file_path);

			$file_modify_time = filemtime( $file_path );
		    $modif = date($this->FM_DATETIME_FORMAT, $file_modify_time );
						
		    $is_zip = false;
		    $is_gzip = false;
		    $is_text = false;

		    $view_title = 'Cache File';
		    $filenames = false; // for zip
		    $content = ''; // for text

		    if ($ext == 'zip' || $ext == 'tar') {
		        $is_zip = true;
		        $view_title = 'Cache Archive';
		        $filenames = CCfmlibtinyfilemanager::fm_get_zif_info($file_path, $ext);
		    } elseif (in_array($ext, CCfmlibtinyfilemanager::fm_get_text_exts()) || substr($mime_type, 0, 4) == 'text' || in_array($mime_type, CCfmlibtinyfilemanager::fm_get_text_mimes())) {
		        $is_text = true;
		        $content = file_get_contents($file_path);
		    }

		    ?>
		    <div class="row">
		        <div class="col-12">
		            <p class="break-word"><b><?php echo $view_title ?> "<?php echo CCfmlibtinyfilemanager::fm_enc(CCfmlibtinyfilemanager::fm_convert_win($file,$this->FM_IS_WIN)) ?>"</b></p>
		            <p class="break-word">
		                Full path: <?php echo CCfmlibtinyfilemanager::fm_enc(CCfmlibtinyfilemanager::fm_convert_win($file_path,$this->FM_IS_WIN)) ?><br/>
						File Modified Time: <?php echo $modif;?><br/>
		                File
		                size: <?php echo CCfmlibtinyfilemanager::fm_get_filesize($filesize) ?><?php if ($filesize >= 1000): ?> (<?php echo sprintf('%s bytes', $filesize) ?>)<?php endif; ?>
		                <br>
		                MIME-type: <?php echo $mime_type ?><br>
		                <?php
		                // ZIP info
		                if (($is_zip || $is_gzip) && $filenames !== false) {
		                    $total_files = 0;
		                    $total_comp = 0;
		                    $total_uncomp = 0;
		                    foreach ($filenames as $fn) {
		                        if (!$fn['folder']) {
		                            $total_files++;
		                        }
		                        $total_comp += $fn['compressed_size'];
		                        $total_uncomp += $fn['filesize'];
		                    }
		                    ?>
		                    Files in archive: <?php echo $total_files ?><br>
		                    Total size: <?php echo CCfmlibtinyfilemanager::fm_get_filesize($total_uncomp) ?><br>
		                    Size in archive: <?php echo CCfmlibtinyfilemanager::fm_get_filesize($total_comp) ?><br>
		                    Compression: <?php echo round(($total_comp / $total_uncomp) * 100) ?>%<br>
		                    <?php
		                }
		                
		                // Text info
		                if ($is_text) {
		                    $is_utf8 = CCfmlibtinyfilemanager::fm_is_utf8($content);
		                    if (function_exists('iconv')) {
		                        if (!$is_utf8) {
		                            $content = iconv(FM_ICONV_INPUT_ENC, 'UTF-8//IGNORE', $content);
		                        }
		                    }
		                    echo 'Charset: ' . ($is_utf8 ? 'utf-8' : '8 bit') . '<br>';
		                }
		                ?>
		            </p>
		            <p>
		                <b><a href="<?php echo trailingslashit(esc_attr($this->FM_ROOT_URL . ($this->FM_PATH != '' ? '/' . $this->FM_PATH : '') )); ?>" target="_blank"><i class="fa fa-external-link-square"></i> <?php _e('Open Cache URL', $this->localisation_domain);?></a></b>
		                &nbsp;
		                <b><a href="<?php echo $this->FM_SELF_URL;?>&amp;p=<?php echo urlencode($this->FM_PATH) ?>"><i class="fa fa-chevron-circle-left go-back"></i> <?php _e('Back to Cache list', $this->localisation_domain); ?></a></b>
		            </p>
		            <?php
		            if ($is_zip) {
		                // ZIP content
		                if ($filenames !== false) {
		                    echo '<code class="maxheight">';
		                    foreach ($filenames as $fn) {
		                        if ($fn['folder']) {
		                            echo '<b>' . CCfmlibtinyfilemanager::fm_enc($fn['name']) . '</b><br>';
		                        } else {
		                            echo $fn['name'] . ' (' . CCfmlibtinyfilemanager::fm_get_filesize($fn['filesize']) . ')<br>';
		                        }
		                    }
		                    echo '</code>';
		                } else {
		                    echo '<p>Error while fetching archive info</p>';
		                }
		            }  elseif ($is_text) {
						$content = '<textarea style="width: 100%;height: 500px;" readonly>'.CCfmlibtinyfilemanager::fm_enc($content).'</textarea>';
		                echo $content;
		            }
		            ?>
		        </div>
		    </div>
		    <?php
		    $this->fm_show_footer();
		    exit;
		}
	}
	/** list all cache files/folders */
	public function list_cache_file( $error_msg = '' ){
		// get current path
		$path = trailingslashit($this->FM_ROOT_PATH);
		
		if ( $this->FM_PATH != '') {
		    $path .= $this->FM_PATH;
		}
		
		// check path
		if (!is_dir($path)) {
		    $path = $this->FM_ROOT_PATH;
		}
		
		$path = trailingslashit($path );
		// get parent folder
		$parent = CCfmlibtinyfilemanager::fm_get_parent_path($this->FM_PATH);
		
		// read files list.
		$objects = is_readable($path) ? scandir($path) : array();
		$folders = array();
		$files = array();
		if (is_array($objects)) {
		    foreach ($objects as $file) {
		        if ($file == '.' || $file == '..' && in_array($file, $this->exclude_items )) {
		            continue;
		        }
		        if (!$this->FM_SHOW_HIDDEN && substr($file, 0, 1) === '.') {
		            continue;
		        }
		        $new_path = $path . '/' . $file;
		        if (@is_file($new_path) && !in_array($file, $this->exclude_items)) {
		            $files[] = $file;
		        } elseif (@is_dir($new_path) && $file != '.' && $file != '..' && !in_array($file, $this->exclude_items)) {
		            $folders[] = $file;
		        }
		    }
		}

		// sort files.
		if (!empty($files)) {
		    natcasesort($files);
		}
		if (!empty($folders)) {
		    natcasesort($folders);
		}
		$num_files = count($files);
		$num_folders = count($folders);
		$all_files_size = 0;
		?>
		
		<div class="wrap">
		<?php if( !empty( $error_msg ) ):?>
			<div id="message" class="updated"><p><?php echo $error_msg ; ?></p></div>
		<?php endif;
			
		$this->fm_show_header(); // HEADER
		$this->fm_show_nav_path($this->FM_PATH); // current path
		?>
		<form action="" method="post" class="pt-3">
		    <input type="hidden" name="p" value="<?php echo esc_attr($this->FM_PATH) ?>" />
		    <input type="hidden" name="group" value="1" />
		    <div class="table-responsive">
		        <table class="table table-bordered table-hover table-sm bg-white" id="main-table">
		            <thead class="thead-white">
		            <tr>
		                <?php if (!$this->FM_READONLY): ?>
		                    <th style="width:3%" class="custom-checkbox-header">
		                        <div class="custom-control custom-checkbox">
		                            <input type="checkbox" class="custom-control-input" id="js-select-all-items" onclick="checkbox_toggle()">
		                            <label class="custom-control-label" for="js-select-all-items"></label>
		                        </div>
		                    </th><?php endif; ?>
		                <th><?php _e('Cache Name', $this->localisation_domain); ?></th>
		                <th><?php _e('Cache File Size', $this->localisation_domain); ?></th>
		                <th><?php _e('Last Modified', $this->localisation_domain); ?></th>
		    			<th><?php _e('Expired in', $this->localisation_domain); ?></th>
		                <th><?php _e('Actions', $this->localisation_domain); ?></th>
		            </tr>
		            </thead>
		            <?php
		            // link to parent folder
		            if ($parent !== false) {
		                ?>
		                <tr><?php if (!$this->FM_READONLY): ?>
		                    <td class="nosort"></td><?php endif; ?>
		                    <td class="border-0"><a href="<?php echo $this->FM_SELF_URL;?>&amp;p=<?php echo urlencode($parent); ?>"><i class="fa fa-chevron-circle-left go-back"></i> ..</a></td>
		                    <td class="border-0"></td>
		                    <td class="border-0"></td>
		                    <td class="border-0"></td>
		                    <td class="nosort"></td>
		                </tr>
		                <?php
		            }
		            $ii = 3000;
		            foreach ($folders as $f) {
		                $img = 'fa fa-folder-o';
		                $file_modify_time = filemtime($path . '/' . $f);
		                $modif = date($this->FM_DATETIME_FORMAT, $file_modify_time );
		                $expired_in = $file_modify_time + $this->wpsc_cache_max_time;
		                ?>
		                <tr>
		                    <?php if (!$this->FM_READONLY): ?>
		                        <td class="custom-checkbox-td">
		                        <div class="custom-control custom-checkbox">
		                            <input type="checkbox" class="custom-control-input" id="<?php echo $ii ?>" name="file[]" value="<?php echo CCfmlibtinyfilemanager::fm_enc($f) ?>">
		                            <label class="custom-control-label" for="<?php echo $ii ?>"></label>
		                        </div>
		                        </td><?php endif; ?>
		                    <td>
		                        <div class="filename"><a href="<?php echo $this->FM_SELF_URL;?>&amp;p=<?php echo urlencode(trim($this->FM_PATH . '/' . $f, '/')) ?>"><i class="<?php echo $img ?>"></i> <?php echo CCfmlibtinyfilemanager::fm_convert_win($f,$this->FM_IS_WIN) ?>
		                            </a></div>
		                    </td>
		                    <td><?php _e('Cache Folder', $this->localisation_domain); ?></td>
		                    <td><?php echo $modif ?></td>
		                    <td><?php echo CCfmlibtinyfilemanager::fm_get_time_remain( current_time('timestamp'), $expired_in ); ?></td>
		                    <td class="inline-actions">
								<?php if (!$this->FM_READONLY): ?>
		                            <a title="<?php _e('Delete', $this->localisation_domain);?>" href="<?php echo wp_nonce_url($this->FM_SELF_URL.'&amp;p='.urlencode($this->FM_PATH).'&amp;del='.urlencode($f),'delete-cache'); ?>" onclick="return confirm('Delete Cache folder?');"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
		                        <?php endif; ?>
		                    </td>
		                </tr>
		                <?php
		                $ii++;
		            }
		            
		            $ik = 20000;
		            foreach ($files as $f) {
		                $img = CCfmlibtinyfilemanager::fm_get_file_icon_class($path . '/' . $f);
		                $file_modify_time = filemtime($path . '/' . $f);
		                $modif = date($this->FM_DATETIME_FORMAT, $file_modify_time );
		                $expired_in = $file_modify_time + $this->wpsc_cache_max_time;
		                
		                $filesize_raw = filesize($path . '/' . $f);
		                $filesize = CCfmlibtinyfilemanager::fm_get_filesize($filesize_raw);
		                $filelink = $this->FM_SELF_URL .'&amp;p=' . urlencode($this->FM_PATH) . '&amp;view=' . urlencode($f);
		                $all_files_size += $filesize_raw;
		                ?>
		                <tr>
		                    <?php if (!$this->FM_READONLY): ?>
		                        <td class="custom-checkbox-td">
		                        <div class="custom-control custom-checkbox">
		                            <input type="checkbox" class="custom-control-input" id="<?php echo $ik ?>" name="file[]" value="<?php echo CCfmlibtinyfilemanager::fm_enc($f) ?>">
		                            <label class="custom-control-label" for="<?php echo $ik ?>"></label>
		                        </div>
		                        </td><?php endif; ?>
		                    <td>
		                        <div class="filename"><a href="<?php echo $filelink ?>" title="File info"><i class="<?php echo $img ?>"></i> <?php echo CCfmlibtinyfilemanager::fm_convert_win($f,$this->FM_IS_WIN) ?>
		                            </a></div>
		                    </td>
		                    <td><span title="<?php printf('%s bytes', $filesize_raw) ?>"><?php echo $filesize ?></span></td>
		                    <td><?php echo $modif ?></td>
		                    <td><?php echo CCfmlibtinyfilemanager::fm_get_time_remain( current_time('timestamp'), $expired_in ); ?></td>
		                    
		                    <td class="inline-actions">
		                        <?php if (!$this->FM_READONLY): ?>
		                            <a title="<?php _e('Delete', $this->localisation_domain); ?>" href="<?php echo wp_nonce_url($this->FM_SELF_URL.'&amp;p='.urlencode($this->FM_PATH).'&amp;del='.urlencode($f),'delete-cache'); ?>" onclick="return confirm('Delete Cache file?');"><i class="fa fa-trash-o"></i></a>
		                        <?php endif; ?>
		                        <a title="<?php _e('Open Cache URL', $this->localisation_domain); ?>" href="<?php echo trailingslashit(CCfmlibtinyfilemanager::fm_enc($this->FM_ROOT_URL . ($this->FM_PATH != '' ? '/' . $this->FM_PATH : '') )); ?>" target="_blank"><i class="fa fa-link"></i></a>
		                    </td>
		                </tr>
		                <?php
		                flush();
		                $ik++;
		            }

		            if (empty($folders) && empty($files)) {
		                ?>
		                <tfoot>
		                    <tr><?php if (!$this->FM_READONLY): ?>
		                            <td></td><?php endif; ?>
		                        <td colspan="5"><em><?php _e('Folder is empty', $this->localisation_domain); ?></em></td>
		                    </tr>
		                </tfoot>
		                <?php
		            } else {
		                ?>
		                <tfoot>
		                    <tr><?php if (!$this->FM_READONLY): ?>
		                            <td class="gray"></td><?php endif; ?>
		                        <td class="gray" colspan="5">
		                            <?php echo __('File', $this->localisation_domain).': <span class="badge badge-light">'.$num_files.'</span>' ?>,
		                            <?php echo __('Folder', $this->localisation_domain).': <span class="badge badge-light">'.$num_folders.'</span>' ?>
		                        </td>
		                    </tr>
		                </tfoot>
		                <?php
		            }
		            ?>
		        </table>
		    </div>

		    <div class="row">
		        <?php if (!$this->FM_READONLY): ?>
		        <div class="col-xs-12 col-sm-9">
		            <ul class="list-inline footer-action">
		                <li class="list-inline-item"> <a href="#/select-all" class="btn btn-small btn-outline-primary btn-2" onclick="select_all();return false;"><i class="fa fa-check-square"></i> <?php _e('SelectAll', $this->localisation_domain); ?> </a></li>
		                <li class="list-inline-item"><a href="#/unselect-all" class="btn btn-small btn-outline-primary btn-2" onclick="unselect_all();return false;"><i class="fa fa-window-close"></i> <?php _e('UnSelectAll', $this->localisation_domain);?> </a></li>
		                <li class="list-inline-item"><a href="#/invert-all" class="btn btn-small btn-outline-primary btn-2" onclick="invert_all();return false;"><i class="fa fa-th-list"></i> <?php _e('InvertSelection', $this->localisation_domain); ?> </a></li>
		                <li class="list-inline-item"><input type="submit" class="hidden" name="delete" id="a-delete" value="Delete" onclick="return confirm('Delete selected files and folders?')">
		                    <a href="javascript:document.getElementById('a-delete').click();" class="btn btn-small btn-outline-primary btn-2"><i class="fa fa-trash"></i> <?php _e('Delete', $this->localisation_domain);?> </a></li>
		            </ul>
		        </div>
		        <?php endif; ?>
		    </div>

		</form>
		</div>
		<?php
		$this->fm_show_footer();
	}
	/**
	 * Show Header after login
	 */
	public function fm_show_header()
	{
		?>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
		<link rel="stylesheet" href="<?php echo $this->assets_url;?>style.css">
	<?php
	}

	/**
	 * Show nav block
	 * @param string $path
	 */
	public function fm_show_nav_path($path)
	{
		?>
		<nav class="navbar navbar-expand-lg navbar-light bg-white mb-4 main-nav">
			<div class="collapse navbar-collapse" id="navbarSupportedContent">
				<?php
				$path = CCfmlibtinyfilemanager::fm_clean_path($path);
				$nav_url = "<a href='".$this->FM_SELF_URL."&amp;p='><i class='fa fa-home' aria-hidden='true' title='" . $this->FM_ROOT_PATH . "'></i></a>";
				$sep = '<i class="bread-crumb"> / </i>';
				if ($path != '') {
					$exploded = explode('/', $path);
					$count = count($exploded);
					$array = array();
					$parent = '';
					for ($i = 0; $i < $count; $i++) {
						$parent = trim($parent . '/' . $exploded[$i], '/');
						$parent_enc = urlencode($parent);
						$array[] = "<a href='".$this->FM_SELF_URL."&amp;p={$parent_enc}'>" . CCfmlibtinyfilemanager::fm_enc(CCfmlibtinyfilemanager::fm_convert_win($exploded[$i],$this->FM_IS_WIN)) . "</a>";
					}
					$nav_url .= $sep . implode($sep, $array);
				}
				echo '<div class="col-xs-8 col-sm-8">' . $nav_url . '</div>';
				?>

				<div class="col-xs-4 col-sm-4 text-right">
					<ul class="navbar-nav mr-auto float-right">
						<?php if (!$this->FM_READONLY): ?>
						<li class="nav-item mr-2">
							<div class="input-group input-group-sm mr-1" style="margin-top:4px;">
								<input type="text" class="form-control" placeholder="<?php  _e('Search', $this->localisation_domain); ?>" aria-label="<?php _e('Search', $this->localisation_domain); ?>" aria-describedby="search-addon2" id="search-addon">
								<div class="input-group-append">
									<span class="input-group-text" id="search-addon2"><i class="fa fa-search"></i></span>
								</div>
							</div>
						</li>
						<?php endif; ?>
					</ul>
				</div>
			</div>
		</nav>
		<?php
	}
	/**
	 * Show page footer
	 */
	function fm_show_footer()
	{ ?>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
	<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
	<script>
	    function change_checkboxes(e, t) { for (var n = e.length - 1; n >= 0; n--) e[n].checked = "boolean" == typeof t ? t : !e[n].checked }
	    function get_checkboxes() { for (var e = document.getElementsByName("file[]"), t = [], n = e.length - 1; n >= 0; n--) (e[n].type = "checkbox") && t.push(e[n]); return t }
	    function select_all() { change_checkboxes(get_checkboxes(), !0) }
	    function unselect_all() { change_checkboxes(get_checkboxes(), !1) }
	    function invert_all() { change_checkboxes(get_checkboxes()) }
	    function checkbox_toggle() { var e = get_checkboxes(); e.push(this), change_checkboxes(e) }
	    // Dom Ready Event
	    $(document).ready( function () {
	        //dataTable init
	        var $table = $('#main-table'),
	            tableLng = $table.find('th').length,
	            _targets = [0,5],
	            mainTable = $('#main-table').DataTable({"paging":   false, "info":     false, "columnDefs": [{"targets": _targets, "orderable": false}]
	        });
	        $('#search-addon').on( 'keyup', function () { //Search using custom input box
	            mainTable.search( this.value ).draw();
	        });
	    });
	</script>
	<?php
	}
	/** show management UI */
	public function management(){
		$msg = '';
		if( isset($_GET['del']) ){
			$msg = $this->delete_file();
		}
		if( isset($_POST['group'], $_POST['delete']) ){
			$msg = $this->mass_delete_file();
		}
		if (isset($_GET['view'])) {
			$msg = $this->view_file();
		}
		$this->list_cache_file( $msg );
	}
	
	/** check if a path is final path */
	public function is_final_path( $path ){
		if ( $path == '' ) return true;
		
		$has_dir = false;
		if ( is_dir( $path ) && $dh = @opendir( $path ) ) {
			while ( ( $file = readdir( $dh ) ) !== false ) {
				if ( $file != '.' && $file != '..' && $file != '.htaccess' && is_dir( $path . $file ) ){
					$has_dir = true;
					break;
				}
			}
		}
		return $has_dir;
	}
	/** delete caches and the sub-directory */
	public function is_protected_file( $path ){
		static $protected = '';
		// only do this once, this function will be called many times
		if ( $protected == '' ) {
			$protected = array( $this->wpsc_basedir, $this->wpsc_basedir . "blogs/",$this->wpsc_basedir  . 'meta/', $this->wpsc_basedir  . 'supercache/',$this->FM_ROOT_PATH );
		}
		// disallow user to delete protected path
		if ( in_array( $path, $protected ) )
			return true;
		
		if( strncmp( $path ,$this->FM_ROOT_PATH ,strlen($path) )== 0 ) return true;
		
		return false;
	}
	/**
	 * Load plugin localisation
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation () {
		load_plugin_textdomain( $this->localisation_domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation ()

	/**
	 * Main CahceFileManagement_for_WPSuperCache Instance
	 *
	 * Ensures only one instance of CahceFileManagement_for_WPSuperCache is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 */
	public static function instance ( $file = '', $version = '1.0.0' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	} // End instance ()
}