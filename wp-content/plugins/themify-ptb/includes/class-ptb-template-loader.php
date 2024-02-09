<?php

class PTB_Template_Loader {

	/**
     * List of directories where PTB must look for templates
     */
    public static $template_directories = null;

    /**
     * Search for a template file inside all registered template directories
     *
     * @return string|false
     */
    public static function locate_template( $names, $type ) {
        static $result = array();
        $names = (array) $names;
        if (!isset($result[$type])) {
            $dirs = self::get_template_directories();
            foreach ($names as $file) {
                foreach ($dirs as $dir) {
                    $f = trailingslashit($dir) . $file;
                    if (file_exists($f)) {
                        $result[$type] = $f;
                        return $f;
                    }
                }
            }
            $result[$type] = false;
        } else {
            return $result[$type];
        }

        return false;
    }

    public static function register_template_directories() {
        if ( empty( self::$template_directories ) ) {
            $defaults = array(
                4 => trailingslashit(PTB::$dir) . 'templates',
                9 => trailingslashit(get_template_directory()) . 'plugins/themify-ptb/templates',
            );
            if (is_child_theme()) {
                $defaults[10] = trailingslashit(get_stylesheet_directory()) . 'plugins/themify-ptb/templates';
            }
            $template_directories = apply_filters('themify_ptb_template_directories', $defaults);
            ksort($template_directories, SORT_NUMERIC);
            self::$template_directories = array_reverse( $template_directories );
        }

        return self::$template_directories;
    }

	/**
     * Retrieve list of directories where PTB should look for template files
     *
     * Higher priority directories are higher in the list
     *
     * @return array
     */
    public static function get_template_directories() {
        return self::$template_directories;
    }
}
add_action( 'init', [ 'PTB_Template_Loader', 'register_template_directories' ] );