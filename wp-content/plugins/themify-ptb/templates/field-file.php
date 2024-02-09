<?php
/**
 * Template to display File field types
 *
 * To override this template copy it to <your_theme>/plugins/themify-ptb/templates/field-file.php
 *
 * @author Themify
 * @package PTB Extra Fields
 */

/* if the custom field value is an string, assume it's the URL to the file */
$value = is_array( $meta_data[ $args['key'] ] ) && isset( $meta_data[ $args['key'] ]['url'] ) ? $meta_data[ $args['key'] ] : array( 'url' => array( $meta_data[ $args['key'] ] ) );

if ( ! empty( $value ) && ! empty( array_filter( $value['url'] ) )  ) {

    $lightbox = isset($data['file_link']) && $data['file_link'] === 'lightbox';
    $length = count($value['url']) - 1;
    $disable_lightbox = apply_filters('ptb_extra_disable_file_lightbox', array('zip', 'doc', 'docx', 'xls', 'xlsx', 'xlsm', 'tar', 'gzip', '7z'));
    $new_window = !$lightbox && isset($data['file_link']) && $data['file_link'] === 'new_window';
    $show_icons = !empty($data['show_icons']);
    $color = $show_icons && !empty($data['color']) ? $data['color'] : false;
    $show_as = !empty($data['show_as']) ? $data['show_as'] : 'l';
	/* file extensions with their corresponding FA icon */
	$extension_icons = [
		'pdf' => 'pdf',
		'doc' => 'word',
		'docx' => 'word',
		'xls' => 'excel',
		'xlsx' => 'excel',
		'zip' => 'archive',
		'7z' => 'archive',
		'rar' => 'archive',
		'pot' => 'powerpoint',
		'potx' => 'powerpoint',
		'txt' => 'code',
	];
    ?>
    <ul  class="ptb_extra_files ptb_extra_files_<?php echo $show_as ?>">
        <?php foreach ($value['url'] as $index => $file): ?>
            <?php if ($file): ?>
                <?php
                $title = !empty($value['title'][$index]) ? esc_attr($value['title'][$index]) : basename( $file );
                $file = esc_url($file);
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                $class = array();
                if ($lightbox && !in_array($ext, $disable_lightbox, true)) {
                    $class[] = 'ptb_lightbox';
                }
                ?>
                <li>
                    <a <?php if ($color): ?>style="color:<?php echo $color ?>"<?php endif; ?><?php if ($new_window): ?>target="_blank"<?php endif; ?> <?php echo!empty($class) ? 'class="' . implode(' ', $class) . '"' : '' ?> href="<?php echo $file ?>">
						<?php if ( $show_icons && isset( $extension_icons[ $ext ] ) ) : ?>
							<span class="ptb_extra_file_icons"><?php echo PTB_Utils::get_icon( 'fa-file-' . $extension_icons[ $ext ] ) ?></span>
						<?php endif; ?>
						<span><?php echo $title ?></span>
					</a><?php if ($index != $length): ?>, <?php endif; ?>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
    <?php
}