<?php

// Copy the code below to your theme functions.php file
// ----------------------------------------------------

// Remove SVG extension from buddyboss avatar URL
add_filter('bp_core_fetch_avatar_url_check', function ($url, $param){
	if('avatars' == $param['avatar_dir']){
		// Check if it's an SVG file.
		if('.svg' == substr($url, -4)){
			$split_url = explode('/', $url);
			$file_name = end($split_url);
			$file_name = str_replace('.svg', '', $file_name);
			$uploads = wp_upload_dir();
			$path = $uploads['basedir'] . DIRECTORY_SEPARATOR
			        . $param['avatar_dir'] . DIRECTORY_SEPARATOR
			        . $param['item_id'] . DIRECTORY_SEPARATOR
			        . $file_name;
			$check_files = ['.png', '.webp', '.jpg', '.jpeg'];
			$exist = '';
			foreach ($check_files as $file){
				$file_path = $path . $file;
				if(file_exists($file_path)){
					$exist = $file;
					break;
				}
			}
			if(!empty($exist)){
				// Remove .svg from the end of the url
				$new_url = substr($url, 0, -4);
				// Add the exist file extension to the url
				$new_url .= $exist;
				return $new_url;
			}
		}
	}
	return $url;
}, 99, 2);

// Remove SVG extension from buddyboss member profile cover image
function niam_update_cover_image_extension( $return, $args ) {
	if( in_array($args['object_dir'], ['members', 'groups']) && 'cover-image' == $args['type'] ){
		$bp_attachments_uploads_dir = bp_attachments_uploads_dir_get();
		if ( ! $bp_attachments_uploads_dir ) {
			return null;
		}

		$type_subdir = sprintf('%s/%s/cover-image/', $args['object_dir'], $args['item_id']);
		$type_dir = trailingslashit( $bp_attachments_uploads_dir['basedir'] ) . $type_subdir;

		$file = '';
		// Open the directory and get the file.
		if (file_exists($type_dir) && $att_dir = opendir( $type_dir ) ) {
			while ( false !== ( $attachment_file = readdir( $att_dir ) ) ) {
				// Look for the first file having the type in its name.
				if ( false !== strpos( $attachment_file, $args['type'] ) && empty( $file ) ) {
					// check if .svg is its extension
					$extension = pathinfo($attachment_file, PATHINFO_EXTENSION);
					if('svg' != $extension){
						$file = $attachment_file;
						break;
					}
				}
			}
		}
		if(empty($file)){
			return null;
		}
		return trailingslashit( $bp_attachments_uploads_dir['baseurl'] ) . trailingslashit($type_subdir) . $file;
	}
	return $return;
}
add_filter('bp_attachments_pre_get_attachment', 'niam_update_cover_image_extension', 99, 2);
