<?php

defined( 'ABSPATH' ) || exit;

/**
 * Themify icon font
 */
class Themify_PTB_Icon_Manager {

	protected static $usedIcons = array();

	public static function init() {
		include PTB::$dir . 'includes/class-icon-fontawesome.php';
		include PTB::$dir . 'includes/class-icon-themify.php';

		/* embed used icons into the page as inline SVG */
		if ( is_admin() ) {
			add_action( 'admin_footer', [ __CLASS__, 'loadIcons' ] );
			add_action( 'wp_ajax_ptb_get_icons_list', [ __CLASS__, 'wp_ajax_ptb_get_icons_list' ] );
			add_action( 'wp_ajax_nopriv_ptb_get_icons_list', [ __CLASS__, 'wp_ajax_ptb_get_icons_list' ] );
		} else {
			add_action( 'wp_footer', [ __CLASS__, 'loadIcons' ], 100 );
		}
	}

	public static function get_icon( $name ) {
		$class = substr( $name, 0, 3 ) === 'ti-' ? 'Themify_PTB_Icon_Themify' : 'Themify_PTB_Icon_FontAwesome';
		return $class::get_classname( $name );
	}

    static function svg_attributes( $attrs ) {
        if ( isset( $attrs['aria-label']) ) {
            $attrs['role']='img';
        } else {
            $attrs['aria-hidden']='true';
        }
		$out = '';
		foreach ( $attrs as $k => $v ) {
			$out .= ' ' . $k . '="' . esc_attr($v) . '"';
		}

		return $out;
    }

	/**
	 * Outputs the used icons
	 */
    public static function loadIcons( $echo = true ) {
		$icons = array_merge( Themify_PTB_Icon_Themify::get_used_icons(), Themify_PTB_Icon_FontAwesome::get_used_icons() );
        $svg = '<svg id="ptb_svg" style="display:none"><defs>';
        if ( ! empty( $icons ) ) {
            $st = '';
            foreach ( $icons as $k => $v ) {
                $w = isset($v['vw']) ? $v['vw'] : '32';
                $h = isset($v['vh']) ? $v['vh'] : '32';
                $p = isset($v['is_fontello']) ? ' transform="matrix(1 0 0 -1 0 ' . $h . ')"' : '';
                $svg .= '<symbol id="ptb-' . $k . '" viewBox="0 0 ' . $w . ' ' . $h . '"><path d="' . $v['p'] . '"' . $p . '></path></symbol>';
                if (isset($v['w'])) {
                    $st .= '.ptb_fa.ptb_' . $k . '{width:' . $v['w'] . 'em}';
                }
            }
            if ($st !== '') {
                $svg .= '<style id="ptb_fonts_style">' . $st . '</style>';
                $st = null;
            }
        }
        $svg .= '</defs></svg>';
		$svg .= '<style>.ptb_fa { display: inline-block; width: 1em; height: 1em; stroke-width: 0; stroke: currentColor; overflow: visible; fill: currentColor; pointer-events: none; vertical-align: middle; }</style>';

        if ( $echo === false ) {
            return $svg;
        }

        echo $svg;
    }

	public static function wp_ajax_ptb_get_icons_list() {
		$categories = Themify_PTB_Icon_FontAwesome::get_categories();
		if ( ! empty( $categories ) ) : ?>
			<div id="ptb_icon_picker_wrap" style="display: none">
				<input type="text" class="ptb_icon_search">
				<style></style>
				<?php foreach( $categories as $category_slug => $category_label ) : ?>
					<section id="<?php echo $category_slug; ?>">
						<h2 class="page-header"><?php echo $category_label; ?></h2>
						<div class="row ptb_icons_groups">
							<?php
							$icons = Themify_PTB_Icon_FontAwesome::get_icons_by_category( $category_slug );
							foreach( $icons as $icon_name ) : ?>
								<a href="#" data-cat="<?php echo $category_slug; ?>" data-icon="<?php echo $icon_name; ?>">
									<?php echo Themify_PTB_Icon_FontAwesome::get_classname( $icon_name ), $icon_name; ?>
								</a>
							<?php endforeach; ?>
						</div>
					</section>
				<?php endforeach; ?>

				<?php self::loadIcons(); ?>

			</div>
		<?php endif;

		die;
	}
}
add_action( 'init', [ 'Themify_PTB_Icon_Manager', 'init' ] );