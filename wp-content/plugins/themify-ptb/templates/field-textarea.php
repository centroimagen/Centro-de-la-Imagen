<?php
/**
 * Textarea field template
 *
 * To override this template copy it to <your_theme>/plugins/themify-ptb/templates/field-textarea.php
 *
 * @var string $type
 * @var array $args
 * @var array $data
 * @var array $meta_data
 * @var array $lang
 * @var boolean $is_single single page
 *
 * @package Themify PTB
 */
?>

<?php

$text = do_shortcode( self::format_text( $meta_data[ $args['key'] ], $is_single ) );
echo $text;