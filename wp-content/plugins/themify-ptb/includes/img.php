<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Resize an image and return formatted HTML tag
 *
 * @return string
 */
function themify_ptb_get_image( $image, $width, $height, $crop = false ) {
	$image = themify_ptb_do_img( $image, $width, $height, $crop );

	if ( empty( $image['url'] ) ) {
		return '';
	}

	$attachment_id = isset( $image['attachment_id'] ) ? $image['attachment_id'] : 0;

	$out = '<img src="' . $image['url'] . '"';
	if ( ! empty( $image['width'] ) ) {
		$out .= ' width="' . $width . '"';
	}
	if ( $height !== '' && $height !== 0 ) {
		$out .= ' height="' . $height . '"';
	}
	$class = '';
	if ( $attachment_id ) {
		$class .= ' wp-post-image wp-image-' . $attachment_id; /* add attachment_id class to img tag */
		$out .= ' class="' . $class . '"';
	}
	$title = '';
	$out_alt = $attachment_id ? get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) : '';
	if ( ! $out_alt ) {
		if ( $attachment_id ) {
			$p = get_post( $attachment_id );
			$out_alt = ! empty( $p ) ? $p->post_title : '';
			$p = null;
		}
		if ( ! $out_alt ) {
			$out_alt = the_title_attribute('echo=0');
		}
		$title = $out_alt;
	}

	if ( $title === '' ) {
		if ( $attachment_id ) {
			$p = get_post( $attachment_id );
			if (!empty($p)) {
				$title = $p->post_title;
			}
			$p = null;
		}
	}

	// Add title attribute only if explicitly set in $args
	if ( ! empty( $title ) ) {
		$out .= ' title="' . esc_attr( $title ) . '"';
	}
	if ( ! empty( $out_alt ) ) {
		$out .= ' alt="' . esc_attr( $out_alt ) . '"';
	}
	$out .= '>';
	if ($attachment_id) {
		$out = function_exists('wp_filter_content_tags') ? wp_img_tag_add_srcset_and_sizes_attr($out,null,$attachment_id) // WP 5.5
		: wp_make_content_images_responsive($out);
	}

	return $out;
}

/**
 * Resize images dynamically using wp built in functions
 *
 * @param string|int $image Image URL or an attachment ID
 * @param int $width
 * @param int $height
 * @param bool $crop
 * @return array
 */
function themify_ptb_do_img( $image, $width, $height, $crop = false ) {
	$attachment_id =$img_url= null;
	if(!is_numeric( $width ) ){
		$width='';
	}
	if(!is_numeric( $height ) ){
		$height='';
	}
	// if an attachment ID has been sent
	if( is_numeric( $image ) ) {
		$post = get_post( $image );
		if( $post ) {
			$attachment_id = $post->ID;
			$img_url = wp_get_attachment_url( $attachment_id );
		}
		unset($post);
	} else {
		if(strpos($image,'data:image/' )!==false ){
			return array(
				'url' =>$image,
				'width' => $width,
				'height' => $height
			);
		}
		// URL has been passed to the function
		$img_url = esc_url( $image );

		// Check if the image is an attachment. If it's external return url, width and height.
		if(strpos($img_url,themify_ptb_upload_dir('baseurl'))===false){
			if($width==='' || $height===''){
				$size = themify_ptb_get_image_size($img_url);
				if($size!==false){
					if($width===''){
						$width=$size['w'];
					}
					if($height===''){
						$height=$size['h'];
					}
				}
			}
			return array(
				'url' =>$img_url,
				'width' => $width,
				'height' => $height
			);
		}
		// Finally, run a custom database query to get the attachment ID from the modified attachment URL
		$attachment_id = themify_ptb_get_attachment_id_from_url( $img_url);
	}
	// Fetch attachment meta data. Up to this point we know the attachment ID is valid.
	$meta = $attachment_id ?wp_get_attachment_metadata( $attachment_id ):null;

	// missing metadata. bail.
	if (!is_array( $meta ) ) {
		$ext=strtolower(strtok(pathinfo($img_url,PATHINFO_EXTENSION ),'?'));
		if($ext==='png' || $ext==='jpg' || $ext==='jpeg' || $ext==='webp' || $ext==='gif' ||$ext==='bmp' ){//popular types
			$upload_dir = themify_ptb_upload_dir();
			$attached_file=str_replace($upload_dir['baseurl'],$upload_dir['basedir'],$img_url);
			if(!is_file ($attached_file)){
				$attached_file=$attachment_id?get_attached_file( $attachment_id ):null;
			}
			if($attached_file){
				$size=themify_ptb_get_image_size($attached_file,true);
				if($size){
					$meta=array(
					'width'=>$size['w'],
					'height'=>$size['h'],
					'file'=>trim(str_replace($upload_dir['basedir'],'',$attached_file),'/')
					);
					//if the meta doesn't exist it means the image large size also doesn't exist,that is why checking if the image is too large before cropping,otherwise the site will down
					static $threshold=null;
					if($threshold===null){
						$threshold = (int) apply_filters( 'big_image_size_threshold', 2560, array($meta['width'],$meta['height']), $attached_file, $attachment_id );
					}
					if($meta['width']>$threshold || $meta['height']>$threshold){
						return array(
							'url' => $img_url,
							'width' => $width,
							'height' => $height,
							'is_large'=>true
						);
					}

				}
				unset($upload_dir,$ext,$size,$attached_file);
			}
		}
		if ( ! is_array( $meta ) ) {
			return array(
				'url' => $img_url,
				'width' => $width,
				'height' => $height
			);
		}
	}

	// Perform calculations when height or width = 0
	if( empty( $width ) ) {
		$width = 0;
	}
	if ( empty( $height ) ) {
		// If width and height or original image are available as metadata
		if ( !empty( $meta['width'] ) && !empty( $meta['height'] ) ) {
			// Divide width by original image aspect ratio to obtain projected height
			// The floor function is used so it returns an int and metadata can be written
			$height = (int)(floor( $width / ( $meta['width'] / $meta['height'] ) ));
		} else {
			$height = 0;
		}
	}
	// Check if resized image already exists
	if ( is_array( $meta ) && isset( $meta['sizes']["resized-{$width}x{$height}"] ) ) {
		$size = $meta['sizes']["resized-{$width}x{$height}"];
		if( isset( $size['width'],$size['height'] )) {
			$split_url = explode( '/', $img_url );
			
			if( ! isset( $size['mime-type'] ) || $size['mime-type'] !== 'image/gif' ) {
				$split_url[ count( $split_url ) - 1 ] = $size['file'];
			}

			return array(
				'url' => implode( '/', $split_url ),
				'width' => $width,
				'height' => $height,
				'attachment_id' => $attachment_id
			);
		}
	}

	// Requested image size doesn't exists, so let's create one
	if ( true == $crop ) {
		add_filter( 'image_resize_dimensions', 'themify_ptb_img_resize_dimensions', 10, 5 );
	}
	// Patch meta because if we're here, there's a valid attachment ID for sure, but maybe the meta data is not ok.
	if ( empty( $meta ) ) {
		$meta['sizes'] = array( 'large' => array() );
	}
	// Generate image returning an array with image url, width and height. If image can't generated, original url, width and height are used.
	$image = themify_ptb_make_image_size( $attachment_id, $width, $height, $meta, $img_url );
	if ( true == $crop ) {
		remove_filter( 'image_resize_dimensions', 'themify_ptb_img_resize_dimensions', 10 );
	}
	$image['attachment_id'] = $attachment_id;
	return $image;
}

/**
 * Creates new image size.
 *
 * @uses get_attached_file()
 * @uses image_make_intermediate_size()
 * @uses wp_update_attachment_metadata()
 * @uses get_post_meta()
 * @uses update_post_meta()
 *
 * @param int $attachment_id
 * @param int $width
 * @param int $height
 * @param array $meta
 * @param string $img_url
 *
 * @return array
 */
function themify_ptb_make_image_size( $attachment_id, $width, $height, $meta, $img_url ) {
	if($width!==0 || $height!==0){
		$upload_dir = themify_ptb_upload_dir();
		$attached_file=str_replace($upload_dir['baseurl'],$upload_dir['basedir'],$img_url);
		unset($upload_dir);
		if(!is_file ($attached_file)){
			$attached_file=get_attached_file( $attachment_id );
		}
		$source_size = apply_filters( 'themify_ptb_image_script_source_size', 'large' );
		if ( $source_size !== 'full' && isset( $meta['sizes'][ $source_size ]['file'] ) ){
			$attached_file = str_replace( $meta['file'], trailingslashit( dirname( $meta['file'] ) ) . $meta['sizes'][ $source_size ]['file'], $attached_file );
		}
		unset($source_size);
		$resized = image_make_intermediate_size( $attached_file, $width, $height, true );
		if ( $resized && ! is_wp_error( $resized ) ) {

			// Save the new size in meta data
			$key = sprintf( 'resized-%dx%d', $width, $height );
			$meta['sizes'][$key] = $resized;
			$img_url = str_replace( basename( $img_url ), $resized['file'], $img_url );

			wp_update_attachment_metadata( $attachment_id, $meta );

			// Save size in backup sizes so it's deleted when original attachment is deleted.
			$backup_sizes = get_post_meta( $attachment_id, '_wp_attachment_backup_sizes', true );
			if ( ! is_array( $backup_sizes ) ){
				$backup_sizes = array();
			}
			$backup_sizes[$key] = $resized;
			update_post_meta( $attachment_id, '_wp_attachment_backup_sizes', $backup_sizes );
			$img_url=esc_url($img_url);
		}
	}
	// Return original image url, width and height.
	return array(
		'url' => $img_url,
		'width' => $width,
		'height' => $height
	);
}



/**
 * Disable the min commands to choose the minimum dimension, thus enabling image enlarging.
 *
 * @param $default
 * @param $orig_w
 * @param $orig_h
 * @param $dest_w
 * @param $dest_h
 * @return array
 */
function themify_ptb_img_resize_dimensions( $default, $orig_w, $orig_h, $dest_w, $dest_h ) {
	// set portion of the original image that we can size to $dest_w x $dest_h
	$aspect_ratio = $orig_w / $orig_h;
	$new_w = $dest_w;
	$new_h = $dest_h;

	if ( !$new_w ) {
		$new_w = (int)( $new_h * $aspect_ratio );
	}

	if ( !$new_h ) {
		$new_h = (int)( $new_w / $aspect_ratio );
	}

	$size_ratio = max( $new_w / $orig_w, $new_h / $orig_h );

	$crop_w = round( $new_w / $size_ratio );
	$crop_h = round( $new_h / $size_ratio );

	$s_x = floor( ( $orig_w - $crop_w ) / 2 );
	$s_y = floor( ( $orig_h - $crop_h ) / 2 );

	// the return array matches the parameters to imagecopyresampled()
	// int dst_x, int dst_y, int src_x, int src_y, int dst_w, int dst_h, int src_w, int src_h
	return array( 0, 0, (int) $s_x, (int) $s_y, (int) $new_w, (int) $new_h, (int) $crop_w, (int) $crop_h );
}

/**
 * Get attachment ID for image from its url.
 *
 * @param string $url
 * @return bool|null|string
 */
function themify_ptb_get_attachment_id_from_url( $url = '' ) {
	/* cache the result, prevent duplicate DB queries */
	static $cache = array();

	// If this is the URL of an auto-generated thumbnail, get the URL of the original image
	$url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif|webp)$)/i', '', $url );
	if ( ! empty( $url ) ) {
		if ( ! isset( $cache[ $url ] ) ) {
			$cache[ $url ] = attachment_url_to_postid( $url );
		}
		return $cache[ $url ];
	}
}

function themify_ptb_get_image_size($url,$isLocal=false){
	if(strpos($url,'x',3)!==false){
		preg_match('/\-(\d+x\d+)\./i',$url,$m);
		if(isset($m[1])){
			$m=explode('x',$m[1]);
			return array('w'=>$m[0],'h'=>$m[1]);
		}
		unset($m);
	}
	elseif(strpos($url,'gravatar.com')!==false){
		$parts = parse_url($url,PHP_URL_QUERY);
		if(!empty($parts)){
			parse_str($parts, $query_params);
			if(!empty($query_params['s'])){
				return array('w'=>$query_params['s'],'h'=>$query_params['s']);
			}
		}
	}
	return false;
}

function themify_ptb_upload_dir($mode = 'all',$reinit=false) {
    static $dir = null;
    if ($dir === null || $reinit === true) {
        $dir = wp_get_upload_dir();
        /* foolproof the paths, in case they mistakenly have trailing slash */
        $dir = array_map('untrailingslashit', $dir);
        $dir['baseurl'] = themify_ptb_https_esc($dir['baseurl']);
    }

    return $mode==='all'?$dir:$dir[$mode];
}

function themify_ptb_https_esc($url = '') {
    if (is_ssl()) {
        $url = str_replace('http:', 'https:', $url);
    }
    return $url;
}
