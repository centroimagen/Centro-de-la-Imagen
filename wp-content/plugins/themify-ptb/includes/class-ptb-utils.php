<?php

/**
 * Utility class with various static functions
 *
 * This class helps to manipulate with arrays
 *
 * @link       https://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/includes
 */

/**
 * Utility class of various static functions
 *
 * This class helps to manipulate with arrays
 *
 * @since      1.0.0
 * @package    PTB
 * @subpackage PTB/includes
 * @author     Themify <ptb@themify.me>
 */
class PTB_Utils {

    /**
     * This function add the value to array if it's already not in array
     *
     * @since      1.0.0
     *
     * @param mixed $value The value to add
     * @param array $array The reference of array
     *
     * @return bool Returns true if value added to array and false if value already in array
     */
    public static function add_to_array($value, &$array) {

        if (!in_array($value, $array,true)) {
            $array[] = $value;
            return true;
        }

        return false;
    }

    /**
     * This function remove the value from array if it's in array
     *
     * @since      1.0.0
     *
     * @param mixed $value The value to remove
     * @param array $array The reference of array
     *
     * @return bool Returns true if value removed from array and false if value does not exist in array
     */
    public static function remove_from_array($value, &$array) {

        $key = array_search($value, $array);

        if (false !== $key) {

            unset($array[$key]);

            return true;
        }

        return false;
    }

    /**
     * Divides array into segments provided in argument
     *
     * @since 1.0.0
     *
     * @param $array
     * @param int $segmentCount
     *
     * @return array|bool
     */
    public static function array_divide($array, $segmentCount = 2) {
        $dataCount = count($array);
        if ($dataCount === 0) {
            return [];
        }
        $segmentLimit = ceil($dataCount / $segmentCount);
        $outputArray = array_chunk($array, $segmentLimit);

        return $outputArray;
    }

    /**
     * Log array to wp debug file
     *
     * @param array $array
     */
    public static function Log_Array($array) {

        error_log(print_r($array, true));
    }

    /**
     * Log to wp debug file
     *
     * @param string $value
     */
    public static function Log($value) {

        error_log(print_r($value, true));
    }

    /**
     * Returns the current language code
     *
     * @since 1.0.0
     *
     * @return string the language code, e.g. "en"
     */
    public static function get_current_language_code() {
        
        static $language_code = false;
        if($language_code){
            return $language_code;
        }
        if (defined('ICL_LANGUAGE_CODE')) {

            $language_code = ICL_LANGUAGE_CODE;
        } elseif (function_exists('qtrans_getLanguage')) {

            $language_code = qtrans_getLanguage();
        }
        if (!$language_code) {
            $language_code = substr(get_locale(), 0, 2);
        }
        $language_code = strtolower(trim($language_code));
        return $language_code;
    }

    /**
     * Returns the site languages
     *
     * @since 1.0.0
     *
	 * @param bool $only_enabled Returns only the enabled languages in PTB Settings page
     * @return array the languages code, e.g. "en",name e.g English
     */
    public static function get_all_languages( $only_enabled = true ) {
        static $languages = null;
        static $enabled_languages = null;

		if ( $languages === null ) {
			if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
				$lng = self::get_current_language_code();
				if ($lng == 'all') {
					$lng = self::get_default_language_code();
				}
				$all_lang = icl_get_languages('skip_missing=0&orderby=KEY&order=DIR&link_empty_to=str');
				foreach ($all_lang as $key => $l) {
					if ($lng == $key) {
						$languages[$key]['selected'] = true;
					}
					$languages[$key]['name'] = $l['native_name'];
				}
			} elseif (function_exists('qtrans_getLanguage')) {
				$languages = qtrans_getSortedLanguages();
			}elseif (class_exists('TRP_Translate_Press')){
				$trp = TRP_Translate_Press::get_trp_instance();
				$trp_languages = $trp->get_component( 'languages' );
				$trp_settings = $trp->get_component( 'settings' );
				$lngs = $trp_languages->get_language_names( $trp_settings->get_settings()['publish-languages'] );
				$default = $trp_settings->get_settings()['default-language'];
				foreach ( $lngs as $code => $name ) {
					$slug = explode( '_', $code )[0];
					$languages[ $slug ]['name'] = $name;
					if ( $code === $default ) {
						$languages[ $slug ]['selected'] = true;
					}
				}
			} else {
				$available_langs = get_available_languages();
				$default_lang = self::get_default_language_code();
				require_once ABSPATH . 'wp-admin/includes/translation-install.php';
				$translations = wp_get_available_translations();
				foreach ( $available_langs as $locale ) {
					if(!isset($translations[ $locale ]['iso'])){
						continue;
					}
					$slug=current( $translations[ $locale ]['iso'] );
					$languages[$slug] = array('name' => $translations[ $locale ]['native_name']);
					if($slug===$default_lang){
						$languages[$slug]['selected'] = true;
					}
				}

				/* special case: English is not listed in $available_langs */
				if ( 'en' === $default_lang ) {
					$languages[$default_lang]['name'] = __( 'English', 'ptb' );
					$languages[$default_lang]['selected'] = true;
				}
			}
			$languages = apply_filters( 'themify_ptb_languages', $languages );
		}

		if ( $enabled_languages === null ) {
			$enabled_languages = $languages;
			$setting = PTB::get_option()->get_plugin_setting( 'languages', [ self::get_default_language_code() => 'on' ] );

			/* reduce the list to only languages enabled in the PTB Settings page */
			$enabled_languages = array_intersect_key( $languages, $setting );

			$selected_exists = false;
			foreach ( $enabled_languages as $lang ) {
				if ( isset( $lang['selected'] ) ) {
					$selected_exists = true;
					break;
				}
			}
			/* the default language is not in the list of enabled languages, mark the first found language as active */
			if ( ! $selected_exists ) {
				$enabled_languages[ key( $enabled_languages ) ]['selected'] = true;
			}
		}

        return $only_enabled ? $enabled_languages : $languages;
    }

    /**
     * Returns the current language code
     *
     * @since 1.0.0
     *
     * @return string the language code, e.g. "en"
     */
    public static function get_default_language_code() {

        static $language_code=false;
        if($language_code!==false){
            return $language_code;
        }
        global $sitepress;
        if (isset($sitepress)) {
            $language_code = $sitepress->get_default_language();
        }

        $language_code = empty($language_code) ? substr( get_locale(), 0, 2 ) : $language_code;
        $language_code = strtolower(trim($language_code));
        return $language_code;
    }

	/**
	 * Check whether multiple languages are active on the website.
	 *
	 * @return bool
	 */
	public static function is_multilingual() {
		static $is = null;
		if ( $is === null ) {
			$languages = PTB_Utils::get_all_languages( true );
			$is = count( $languages ) > 1;
		}

		return $is;
	}

    public static function get_label( $label, $default = '' ) {
        if (!is_array($label)) {
            return esc_attr($label);
        }
        static $lng=false;
        if($lng===false){
            $lng = self::get_current_language_code();
        }
        $value = '';
        if (!empty($label[$lng])) {
            $value = $label[$lng];
        } else {
            static $default_lng=false;
            if($default_lng===false){
                $default_lng = self::get_default_language_code();
            }
            $value = isset($label[$default_lng]) && $label[$default_lng] ? $label[$default_lng] : current($label);
        }
        return esc_attr( $value === '' ? $default : $value );
    }
    
    
    public static function get_reserved_terms(){
		return array(
			'attachment',
			'attachment_id',
			'author',
			'author_name',
			'author__in',
			'author__not_in',
			'calendar',
			'cat',
			'category',
			'category__and',
			'category__in',
			'category__not_in',
			'category_name',
			'comments_per_page',
			'comments_popup',
			'custom',
			'customize_messenger_channel',
			'customized',
			'cpage',
			'day',
			'debug',
			'embed',
			'error',
			'exact',
			'feed',
			'hour',
			'link_category',
			'm',
			'minute',
			'monthnum',
			'more',
			'name',
			'nav_menu',
			'nonce',
			'nopaging',
			'offset',
			'order',
			'orderby',
			'p',
			'page',
			'page_id',
			'paged',
			'pagename',
			'pb',
			'perm',
			'post',
			'post__in',
			'post__not_in',
			'post_format',
			'post_mime_type',
			'post_status',
			'post_tag',
			'post_type',
			'posts',
			'posts_per_archive_page',
			'posts_per_page',
			'preview',
			'robots',
			's',
			'search',
			'second',
			'sentence',
			'showposts',
			'static',
			'subpost',
			'subpost_id',
			'tag',
			'tag__and',
			'tag__in',
			'tag__not_in',
			'tag_id',
			'tag_slug__and',
			'tag_slug__in',
			'tax',
			'taxonomy',
			'tb',
			'term',
			'terms',
			'theme',
			'title',
			'type',
			'w',
			'withcomments',
			'withoutcomments',
			'year',
			'include',
			'exclude',
			'fields',
		);
    }

    /**
     * Get full list of currency codes.
     * @return array
     */
    public static function get_currencies() {
        return array_unique(
                apply_filters('ptb_currencies', array(
            'AED' => __('United Arab Emirates Dirham', 'ptb'),
            'ARS' => __('Argentine Peso', 'ptb'),
            'AUD' => __('Australian Dollars', 'ptb'),
            'BDT' => __('Bangladeshi Taka', 'ptb'),
            'BRL' => __('Brazilian Real', 'ptb'),
            'BGN' => __('Bulgarian Lev', 'ptb'),
            'CAD' => __('Canadian Dollars', 'ptb'),
            'CLP' => __('Chilean Peso', 'ptb'),
            'CNY' => __('Chinese Yuan', 'ptb'),
            'COP' => __('Colombian Peso', 'ptb'),
            'CZK' => __('Czech Koruna', 'ptb'),
            'DKK' => __('Danish Krone', 'ptb'),
            'DOP' => __('Dominican Peso', 'ptb'),
            'EUR' => __('Euros', 'ptb'),
            'HKD' => __('Hong Kong Dollar', 'ptb'),
            'HRK' => __('Croatia kuna', 'ptb'),
            'HUF' => __('Hungarian Forint', 'ptb'),
            'ISK' => __('Icelandic krona', 'ptb'),
            'IDR' => __('Indonesia Rupiah', 'ptb'),
            'INR' => __('Indian Rupee', 'ptb'),
            'NPR' => __('Nepali Rupee', 'ptb'),
            'ILS' => __('Israeli Shekel', 'ptb'),
            'JPY' => __('Japanese Yen', 'ptb'),
            'KIP' => __('Lao Kip', 'ptb'),
            'KRW' => __('South Korean Won', 'ptb'),
            'MYR' => __('Malaysian Ringgits', 'ptb'),
            'MXN' => __('Mexican Peso', 'ptb'),
            'NGN' => __('Nigerian Naira', 'ptb'),
            'NOK' => __('Norwegian Krone', 'ptb'),
            'NZD' => __('New Zealand Dollar', 'ptb'),
            'PYG' => __('Paraguayan Guaraní', 'ptb'),
            'PHP' => __('Philippine Pesos', 'ptb'),
            'PLN' => __('Polish Zloty', 'ptb'),
            'GBP' => __('Pounds Sterling', 'ptb'),
            'RON' => __('Romanian Leu', 'ptb'),
            'RUB' => __('Russian Ruble', 'ptb'),
            'SGD' => __('Singapore Dollar', 'ptb'),
            'ZAR' => __('South African rand', 'ptb'),
            'SEK' => __('Swedish Krona', 'ptb'),
            'CHF' => __('Swiss Franc', 'ptb'),
            'TWD' => __('Taiwan New Dollars', 'ptb'),
            'THB' => __('Thai Baht', 'ptb'),
            'TRY' => __('Turkish Lira', 'ptb'),
            'UAH' => __('Ukrainian Hryvnia', 'ptb'),
            'USD' => __('US Dollars', 'ptb'),
            'VND' => __('Vietnamese Dong', 'ptb'),
            'EGP' => __('Egyptian Pound', 'ptb')
                        )
                )
        );
    }

    /**
     * Get Currency symbol.
     * @param string $currency
     * @return string
     */
    public static function get_currency_symbol($currency) {
        static $return = array();
        if(empty($return[$currency])){

            switch ($currency) {
                case 'AED' :
                    $currency_symbol = 'د.إ';
                    break;
                case 'AUD' :
                case 'ARS' :
                case 'CAD' :
                case 'CLP' :
                case 'COP' :
                case 'HKD' :
                case 'MXN' :
                case 'NZD' :
                case 'SGD' :
                case 'USD' :
                    $currency_symbol = '&#36;';
                    break;
                case 'BDT':
                    $currency_symbol = '&#2547;&nbsp;';
                    break;
                case 'BGN' :
                    $currency_symbol = '&#1083;&#1074;.';
                    break;
                case 'BRL' :
                    $currency_symbol = '&#82;&#36;';
                    break;
                case 'CHF' :
                    $currency_symbol = '&#67;&#72;&#70;';
                    break;
                case 'CNY' :
                case 'JPY' :
                case 'RMB' :
                    $currency_symbol = '&yen;';
                    break;
                case 'CZK' :
                    $currency_symbol = '&#75;&#269;';
                    break;
                case 'DKK' :
                    $currency_symbol = 'DKK';
                    break;
                case 'DOP' :
                    $currency_symbol = 'RD&#36;';
                    break;
                case 'EGP' :
                    $currency_symbol = 'EGP';
                    break;
                case 'EUR' :
                    $currency_symbol = '&euro;';
                    break;
                case 'GBP' :
                    $currency_symbol = '&pound;';
                    break;
                case 'HRK' :
                    $currency_symbol = 'Kn';
                    break;
                case 'HUF' :
                    $currency_symbol = '&#70;&#116;';
                    break;
                case 'IDR' :
                    $currency_symbol = 'Rp';
                    break;
                case 'ILS' :
                    $currency_symbol = '&#8362;';
                    break;
                case 'INR' :
                    $currency_symbol = 'Rs.';
                    break;
                case 'ISK' :
                    $currency_symbol = 'Kr.';
                    break;
                case 'KIP' :
                    $currency_symbol = '&#8365;';
                    break;
                case 'KRW' :
                    $currency_symbol = '&#8361;';
                    break;
                case 'MYR' :
                    $currency_symbol = '&#82;&#77;';
                    break;
                case 'NGN' :
                    $currency_symbol = '&#8358;';
                    break;
                case 'NOK' :
                    $currency_symbol = '&#107;&#114;';
                    break;
                case 'NPR' :
                    $currency_symbol = 'Rs.';
                    break;
                case 'PHP' :
                    $currency_symbol = '&#8369;';
                    break;
                case 'PLN' :
                    $currency_symbol = '&#122;&#322;';
                    break;
                case 'PYG' :
                    $currency_symbol = '&#8370;';
                    break;
                case 'RON' :
                    $currency_symbol = 'lei';
                    break;
                case 'RUB' :
                    $currency_symbol = '&#1088;&#1091;&#1073;.';
                    break;
                case 'SEK' :
                    $currency_symbol = '&#107;&#114;';
                    break;
                case 'THB' :
                    $currency_symbol = '&#3647;';
                    break;
                case 'TRY' :
                    $currency_symbol = '&#8378;';
                    break;
                case 'TWD' :
                    $currency_symbol = '&#78;&#84;&#36;';
                    break;
                case 'UAH' :
                    $currency_symbol = '&#8372;';
                    break;
                case 'VND' :
                    $currency_symbol = '&#8363;';
                    break;
                case 'ZAR' :
                    $currency_symbol = '&#82;';
                    break;
                default :
                    $currency_symbol = '';
                    break;
            }
            $return[$currency] = apply_filters('ptb_currency_symbol', $currency_symbol, $currency);
        }
        return $return[$currency];
    }

    /**
     * Get full list of currency codes.
     * @return array
     */
    public static function get_currency_position() {
        return array_unique(
                apply_filters('ptb_currency_position', array(
                        'left' => __('Left (£99.99)', 'ptb'),
                        'right' => __('Right (99.99£)', 'ptb'),
                        'left_space' => __('Left with space (£ 99.99)', 'ptb'),
                        'right_space' => __('Right with space (99.99 £)', 'ptb')
                                    )
                )
        );
    }
    
    /**
     * Get the price format depending on the currency position
     *
     * @return string
     */
    public static function  get_price_format($currency_pos, $currency, $price) {
       
        switch ($currency_pos) {
            case 'left' :
                $format = '%1$s%2$s';
                break;
            case 'right' :
                $format = '%2$s%1$s';
                break;
            case 'left_space' :
                $format = '%1$s&nbsp;%2$s';
                break;
            case 'right_space' :
                $format = '%2$s&nbsp;%1$s';
                break;
        }
        $format = apply_filters('ptb_price_format', $format, $currency, $currency_pos);
        return sprintf($format, self::get_currency_symbol($currency), $price);
    }
    
    /**
     * Check if ajax request
     *
     * @param void
     *
     * return boolean
     */
    public static function is_ajax() {
        static $is_ajax = null;
        if(is_null($is_ajax)){
            $is_ajax = defined('DOING_AJAX') || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
        }
        return $is_ajax;
    }
    
    
    public static function enque_min( $url, $check = false ) {
        static $is_disabled = null;
        if ( $is_disabled === null ) {
            $is_disabled = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) || ( defined( 'THEMIFY_DEBUG' ) && THEMIFY_DEBUG ) || defined( 'THEMIFY_DISABLE_MIN' );
			if ( ! $is_disabled ) {
				$is_disabled = function_exists( 'themify_builder_get' ) && themify_builder_get( 'setting-script_minification-min','builder_minified' ) === null ? false : true;
			}
        }	
        if( $is_disabled ) {
            return $check ? false : $url;
        }
        $f = pathinfo( $url );
        $return = 0;
        if ( strpos( $f['filename'], '.min.', 2 ) === false ) {
			$absolute = str_replace( WP_CONTENT_URL, '', $f['dirname'] );
			$name = $f['filename'] . '.min.' . $f['extension'];
			if ( is_file( trailingslashit( WP_CONTENT_DIR ) . trailingslashit( $absolute ) . $name ) ) {
				if( $check ) {
					$return = 1;
				} else {
					$url = trailingslashit( $f['dirname'] ) . $name;
				}
			}
		}

        return $check ? $return : $url;
    }
    
    
    public static function is_themify_theme() {
        static $is_themify = null;
        if ($is_themify === null) {
            $is_themify = function_exists('themify_is_themify_theme') && themify_is_themify_theme();
        }
        return $is_themify;
    }

    public static function get_unique_slug(array $array,$slug){
        $i=1;
        $reserved = self::get_reserved_terms();
        while(isset($array[$slug]) || in_array($slug,$reserved,true)){
            $slug.='-'.$i;
            ++$i;
        }
        return $slug;
    }

	/**
	 * Returns a list of public post types
	 *
	 * @param bool $exclude_ptb_types whether to exclude post types registered by PTB from the list
	 *
	 * @return array
	 */
	public static function get_public_post_types( $exclude_ptb_types = true ) {
		$post_types = get_post_types( [ 'public' => true ] );
		if ( $exclude_ptb_types ) {
			$ptb_post_types = PTB::get_option()->get_custom_post_types();
			$post_types = array_diff( $post_types, array_keys( $ptb_post_types ) );
		}
		unset( $post_types['attachment'], $post_types['tbuilder_layout'], $post_types['tbuilder_layout_part'], $post_types['tglobal_style'] );

		return $post_types;
	}

	public static function enqueue_script( $handle, $src = '', $deps = array(), $ver = false, $in_footer = false ) {
		$src = PTB_Utils::enque_min( $src );
		wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );
	}

	public static function enqueue_style( $handle, $src = '', $deps = array(), $ver=  false, $media = 'all' ) {
		$src = PTB_Utils::enque_min( $src );
		/* in Themify theme, add the stylesheet to theme's concate.css */
		if ( class_exists( 'Themify_Enqueue_Assets' ) ) {
			Themify_Enqueue_Assets::add_css( $handle, $src, $deps, $ver, $media );
		} else {
			wp_enqueue_style( $handle, $src, $deps, $ver, $media );
		}
	}

	/**
	 * Shortcut to enqueue_style to load a CSS file from css/modules
	 *
	 * @return void
	 */
	public static function enqueue_module_css( $name ) {
		PTB_Utils::enqueue_style( "ptb-{$name}", PTB::$uri . "public/css/modules/{$name}.css" );
	}

	/**
	 * Display an icon into page
	 *
	 * @return string
	 */
	public static function get_icon( $name ) {
		return Themify_PTB_Icon_Manager::get_icon( $name );
	}

	/**
	 * Get the raw meta value, based on the type of $thing
	 *
	 * @return mixed
	 */
	public static function get_meta_value( $thing, $key, $default = null ) {
		if ( is_null( $thing ) ) {
			return null;
		}

		if ( is_object( $thing ) ) {
			if ( $thing instanceof WP_Post ) {
				$context = 'post';
			} else if ( $thing instanceof WP_Term ) {
				$context = 'term';
			} else if ( $thing instanceof WP_User ) {
				$context = 'user';
			}
		} else if ( is_string( $thing ) ) {
			$context = $thing;
		}

		$object_id = $context === 'term' ? $thing->term_id : $thing->ID;
		$value = get_metadata( $context, $object_id, "ptb_{$key}", true );
		if ( ! $value && $default ) {
			$value = PTB_Utils::get_label( $default );
		}

		return $value;
	}

	/**
	 * Performs sprintf on string only if %s is detected
	 *
	 * @return string
	 */
	public static function safe_sprintf( $format, $args ) {
		if ( strpos( $format, '%s' ) !== false ) {
			$format = sprintf( $format, $args );
		}

		return $format;
	}
}