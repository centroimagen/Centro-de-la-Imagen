<?php

/**
 * Custom meta box class to create event-date
 *
 * @link       https://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/includes
 * @author     Themify <ptb@themify.me>
 */
class PTB_CMB_Event_Date extends PTB_CMB_Base {
    
    private $search_date = array();
    
    public function __construct($type, $plugin_name, $version) {
        parent::__construct($type, $plugin_name, $version);
        add_filter('ptb_ajax_shortcode_result',array($this,'add_date_field'),10,2);
        add_filter('themify_ptb_shortcode_query',array($this,'date_filter'),10,1);
		add_filter('ptb_filter_cmb_body',array($this,'before_save'),10,2);
        if(!is_admin() || (defined('DOING_AJAX') &&  DOING_AJAX)){
            add_action('ptb_search_event_date',array($this,'search_date_template'),10,8);
            add_filter('ptb_search_by_event_date',array($this,'search_date'),10,6);
            add_filter('ptb_meta_event_date_exist', array($this,'event_date_exist'),10,3);
			
        }
    }


    /**
     * Adds the custom meta type to the plugin meta types array
     *
     * @since 1.0.0
     *
     * @param array $cmb_types Array of custom meta types of plugin
     *
     * @return array
     */
    public function filter_register_custom_meta_box_type($cmb_types) {
        $cmb_types[$this->get_type()] = array(
            'name' => __('Date', 'ptb')
        );
        return $cmb_types;
    }

	/**
     * Converts the date and time to and From 'Y-m-d H:i:s'
     *
     * @since 1.3.5 
     *
     * @param string $datetime
     * @param string $to_mysql only values 'yes' and 'no'
	 *
     * @return string
     */
	private function convert_datetime($datetime, $to_mysql = 'no', $date_format = null, $time_format = null ) {
		if ( empty($datetime) ) {
			return '';
		}

		/* convert date and time format from datepicker.js to PHP first */
		$format = ! empty( $date_format ) ? trim( str_replace(array('yy','MM','mm','m','dd','DD','#'),array('Y','F','#','n','d','L','m'), $date_format ) ) : 'Y-m-d';
		$format .= '\@';
		$format.= ! empty( $time_format ) ? trim( str_replace( array('HH','hh','h','H','mm','m','TT','tt','$','#'),array('$','#','g','G','i','i','A','a','H','h'), $time_format ) ) : 'h:i a';

		switch ($to_mysql) {
			case 'no':
				if (strpos($datetime, '@') === FALSE) {
					$tmp_val = DateTime::createFromFormat('Y-m-d H:i:s', $datetime);
					$datetime = $tmp_val ? $tmp_val->format( $format ) : $datetime;
				}
				break;
			default :
				if (strpos($datetime, '@') !== FALSE) {
					$tmp_val = DateTime::createFromFormat( $format, $datetime);
                    $datetime = $tmp_val ? $tmp_val->format('Y-m-d H:i:s') : $datetime;
				}
		}
		return $datetime;
	}
	
	public function before_save($args, $post){
		if( empty( $_POST ) ) {
			return $args;
		}
		foreach ($args as $key => $data) {
			if ($data['type'] === 'event_date' && !$data['deleted']) {
				if ( ! empty($_POST[$key]) ) {
					$date_format = empty( $data['dateformat'] ) ? null : $data['dateformat'];
					$time_format = empty( $data['timeformat'] ) ? null : $data['timeformat'];
					if (is_array($_POST[$key])) {
						$_POST[$key]['start_date'] = $this->convert_datetime( $_POST[$key]['start_date'], 'yes', $date_format, $time_format );
						$_POST[$key]['end_date'] = $this->convert_datetime( $_POST[$key]['end_date'], 'yes', $date_format, $time_format );
					} else {
						$_POST[$key] = $this->convert_datetime( $_POST[$key], 'yes', $date_format, $time_format );
					}
				}
			}
		}
		return $args;
    }

    /**
     * @param string $id the id template
     * @param array $languages
     */
    public function action_template_type($id, array $languages) {
        ?>

        <div class="ptb_cmb_input_row">
            <label for="<?php echo $id; ?>_showrange" class="ptb_cmb_input_label">
                <?php _e("Show as range", 'ptb'); ?>
            </label>
            <div class="ptb_cmb_input">
                <input type="checkbox" id="<?php echo $id; ?>_showrange" name="<?php echo $id; ?>_showrange" value="1" />
            </div>
        </div>
        <div class="ptb_cmb_input_row">
            <label class="ptb_cmb_input_label">
                <?php _e("Format", 'ptb'); ?>
            </label>
            <div class="ptb_cmb_input">
                <?php _e("Date Format", 'ptb'); ?>: <input type="text" id="<?php echo $id; ?>_dateformat" name="<?php echo $id; ?>_dateformat" value="" style="width: 100px" /> 
				<?php _e("Time Format", 'ptb'); ?>: <input type="text" id="<?php echo $id; ?>_timeformat" name="<?php echo $id; ?>_timeformat" value="" style="width: 100px" />
				<br>
				<span class="ptb_cmb_input_description"><?php printf( __( 'How it is displayed in WordPress backend. <a href="%s" target="blank">Formatting Guide</a>', 'ptb' ), 'http://api.jqueryui.com/datepicker/#utility-formatDate' ); ?></span>
            </div>
        </div>
        <?php
    }

    /**
     * Renders the meta boxes for themplates
     *
     * @since 1.0.0
     *
     * @param string $id the metabox id
     * @param string $type the type of the page(Arhive or Single)
     * @param array $args Array of custom meta types of plugin
     * @param array $data saved data
     * @param array $languages languages array
     */
    public function action_them_themplate($id, $type, $args, $data = array(), array $languages = array()) {
        ?>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[start][dateformat]"><?php _e('Start Date format', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input">
                <input type="text" id="ptb_<?php echo $id ?>[start][dateformat]"
                       name="[<?php echo $id ?>][start][dateformat]" value="<?php echo isset($data['start']['dateformat']) ? $data['start']['dateformat'] : 'M j,Y' ?>"
                       />
                <input type="text" id="ptb_<?php echo $id ?>[start][separator]"
                       name="[<?php echo $id ?>][start][separator]" value="<?php echo isset($data['start']['separator']) ? $data['start']['separator'] : '@' ?>"
                       size="1" /> 
				<input type="text" id="ptb_<?php echo $id ?>[start][timeformat]"
                       name="[<?php echo $id ?>][start][timeformat]" value="<?php echo isset($data['start']['timeformat']) ? $data['start']['timeformat'] : 'H:i' ?>"
                       size="4" />
				<?php _e('(e.g. M j,Y @ H:i)', 'ptb') ?>
				<a href="https://wordpress.org/support/article/formatting-date-and-time/" target="_blank"><?php _e('More info', 'ptb') ?></a>
            </div>
        </div>
		<div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label>&nbsp;</label>
            </div>
			<div class="ptb_back_active_module_input">
                <input type="checkbox" id="ptb_<?php echo $id ?>_start_hide_data"
                        name="[<?php echo $id ?>][start][hide_date]" value="1"
                        <?php if (isset($data['start']['hide_date'])): ?>checked="checked"<?php endif; ?>/>
				<label for="ptb_<?php echo $id ?>_start_hide_data"><?php _e('Hide Date','ptb'); ?></label>
				<input type="checkbox" id="ptb_<?php echo $id ?>_start_hide_time"
                        name="[<?php echo $id ?>][start][hide_time]" value="1"
                        <?php if (isset($data['start']['hide_time'])): ?>checked="checked"<?php endif; ?>/>
				<label for="ptb_<?php echo $id ?>_start_hide_time"><?php _e('Hide Time','ptb'); ?></label>
            </div>
        </div>
        <?php if(isset($args['showrange']) && $args['showrange'] == 1):?>
		<div class="ptb_back_active_module_row">
			<div class="ptb_back_active_module_label">
				<label for="ptb_<?php echo $id ?>[rangeseperator]"><?php _e('Date Range separator', 'ptb') ?></label>
			</div>
			<div class="ptb_back_active_module_input">
				<input type="text" id="ptb_<?php echo $id ?>[rangeseperator]"
					   name="[<?php echo $id ?>][rangeseperator]" value="<?php echo isset($data['rangeseperator']) ? $data['rangeseperator'] : '-' ?>"
					   />
			</div>
		</div>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[end][dateformat]"><?php _e('End Date format', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input">
                <input type="text" id="ptb_<?php echo $id ?>[end][dateformat]"
                       name="[<?php echo $id ?>][end][dateformat]" value="<?php echo isset($data['end']['dateformat']) ? $data['end']['dateformat'] : 'M j,Y' ?>"
                       />
                <input type="text" id="ptb_<?php echo $id ?>[end][separator]"
                       name="[<?php echo $id ?>][end][separator]" value="<?php echo isset($data['end']['separator']) ? $data['end']['separator'] : '@' ?>"
                       size="1" /> 
				<input type="text" id="ptb_<?php echo $id ?>[end][timeformat]"
                       name="[<?php echo $id ?>][end][timeformat]" value="<?php echo isset($data['end']['timeformat']) ? $data['end']['timeformat'] : 'H:i' ?>"
                       size="4" />
				<?php _e('(e.g. M j,Y @ H:i)', 'ptb') ?>
				<a href="https://wordpress.org/support/article/formatting-date-and-time/" target="_blank"><?php _e('More info', 'ptb') ?></a>
            </div>
        </div>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label>&nbsp;</label>
            </div>
			<div class="ptb_back_active_module_input">
                <input type="checkbox" id="ptb_<?php echo $id ?>_end_hide_data"
                        name="[<?php echo $id ?>][end][hide_date]" value="1"
                        <?php if (isset($data['end']['hide_date'])): ?>checked="checked"<?php endif; ?>/>
				<label for="ptb_<?php echo $id ?>_end_hide_data"><?php _e('Hide Date','ptb'); ?></label>
				<input type="checkbox" id="ptb_<?php echo $id ?>_end_hide_time"
                        name="[<?php echo $id ?>][end][hide_time]" value="1"
                        <?php if (isset($data['end']['hide_time'])): ?>checked="checked"<?php endif; ?>/>
				<label for="ptb_<?php echo $id ?>_end_hide_time"><?php _e('Hide Time','ptb'); ?></label>
            </div>
        </div>
        <?php endif; ?>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[show_text]"><?php _e('Show text if date has been expired', 'ptb') ?></label>
            </div>
            <?php self::module_language_tabs($id, $data, $languages, 'show_text') ?>
        </div>
        <?php
    }


    /**
     * Renders the meta boxes on post edit dashboard
     *
     * @since 1.0.0
     *
     * @param WP_Post $post
     * @param string $meta_key
     * @param array $args
     */
    public function render_post_type_meta($object, $meta_key, $args) {

        $value = PTB_Utils::get_meta_value( $object, $meta_key );
		$date_format = ! empty( $args['dateformat'] ) ? $args['dateformat'] : 'yy-mm-dd';
		$time_format = ! empty( $args['timeformat'] ) ? $args['timeformat'] : 'hh:mm tt';
        ?>
        <?php if (!isset($args['showrange'])): ?>
            <?php
            if ($value && is_array($value) && isset($value['start_date'])) {
                $value = esc_attr($value['start_date']);
            }
			$value = isset($value) ? $this->convert_datetime( $value, 'no', $date_format, $time_format ) : '';
            ?>
            <input  id="ptb_extra_<?php echo $meta_key; ?>" 
				type="text" 
				name="<?php echo sprintf('%s', $meta_key); ?>"
				value="<?php echo $value ?>" class="ptb_extra_input_datepicker"
				data-dateformat="<?php echo esc_attr( $date_format ); ?>"
				data-timeformat="<?php echo esc_attr( $time_format ); ?>"
			/>

        <?php else: ?>
            <?php
            if ( !is_array($value) ) {
                $tmp_val = $value;
                $value = array();
                if ($tmp_val) {
					$value['start_date'] = $tmp_val;
				}
            }
			
			$value['start_date'] = isset($value['start_date']) ? $this->convert_datetime($value['start_date'], 'no', $date_format, $time_format ) : '';
			$value['end_date'] = isset($value['end_date']) ? $this->convert_datetime($value['end_date'], 'no', $date_format, $time_format ) : '';
            ?>
            <div class="ptb_table_row">
                <div class="ptb_table_cell">
                    <input
                        type="text" name="<?php echo sprintf('%s[start_date]', $meta_key); ?>"
                        placeholder="<?php _e('Starts on', 'ptb') ?>" class="ptb_extra_input_datepicker" id="<?php echo $meta_key; ?>_start" type="text"
                        value="<?php echo esc_attr($value['start_date']) ?>"
						data-dateformat="<?php echo esc_attr( $date_format ); ?>"
						data-timeformat="<?php echo esc_attr( $time_format ); ?>"
					/>
                </div>
                <span class="ptb-arrow-right"><?php echo PTB_Utils::get_icon( 'ti-arrow-right' ); ?></span>
                <div class="ptb_table_cell">
                    <input 
                        placeholder="<?php _e('Ends on', 'ptb') ?>" class="ptb_extra_input_datepicker" id="<?php echo $meta_key; ?>_end" type="text" name="<?php echo sprintf('%s[end_date]', $meta_key); ?>"
                        value="<?php echo esc_attr($value['end_date']); ?>"/>
                </div>
            </div>
        <?php endif; ?>
        <?php
    }
    
    public function add_date_field($result,$post_type){
        $options = PTB::get_option();
        $cmb = $options->get_cpt_cmb_options($post_type);
        if(!empty($cmb)){
            $values = array(array('text'=>__('Show All','ptb'),'value'=>''),
                            array('text'=>__('Show Past Posts','ptb'),'value'=>'past'),
                            array('text'=>__('Show Upcoming Posts','ptb'),'value'=>'upcoming')
                           );
            foreach($cmb as $key=>$c){
                if(($c['type']==='event_date' && !isset($c['showrange']))){
                    $result['data'][$key]['type'] = 'listbox';
                    $result['data'][$key]['values'] = $values;
                    $result['data'][$key]['label'] = PTB_Utils::get_label($c['name']);
                    $result['data'][$key]['value'] = '';      
                }
            }
        }
        return $result;
    }
    
    public function date_filter(array $args=array()){
        if(!empty($args)){
            $options = PTB::get_option();
            $cmb = $options->get_cpt_cmb_options($args['post_type']);
            $query = array();
            $now = current_time('Y-m-d H:i:s');
            foreach($args as  $key=>$v){
                if (isset($cmb[$key]) && $cmb[$key]['type']==='event_date' &&  !isset($cmb[$key]['showrange']) && $v) {
                    $query[] = array(
                                    'key'=>'ptb_'.$key,
                                    'value'=>$now,
                                    'compare'=>$v==='upcoming'?'>=':'<=',
									'type' => 'DATETIME'
                                );
                    unset($args[$key]);
                }
            }
            if(!empty($query)){
                $args['meta_query'] = $query;
                $args['meta_query']['relation'] = 'AND';
            }
        }
        return $args;
    }

    public function ptb_submission_themplate($id, array $args, array $module, array $post_support, array $languages = array()) {
        ?>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[time]"><?php _e("Show Time", 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input ptb_change_disable" data-disabled="1" data-action="1">
                <input type="checkbox"  id="ptb_<?php echo $id ?>[time]" name="[<?php echo $id ?>][time]" value="1" <?php echo !empty($module['time'])? 'checked="checked"' : '' ?>/>
            </div>
        </div>
        <div class="ptb_back_active_module_row">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[dateformat]"><?php _e('Date format', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input">
                <input type="text" id="ptb_<?php echo $id ?>[dateformat]"
                       name="[<?php echo $id ?>][dateformat]" value="<?php echo isset($module['dateformat']) ? $module['dateformat'] : '' ?>"
                       />
                <?php _e('(e.g. yy-mm-dd)', 'ptb') ?> <a href="//api.jqueryui.com/datepicker/#utility-formatDate" target="_blank"><?php _e('More info', 'ptb') ?></a>
            </div>
        </div>
        <div class="ptb_back_active_module_row ptb_maybe_disabled">
            <div class="ptb_back_active_module_label">
                <label for="ptb_<?php echo $id ?>[timeformat]"><?php _e('Time format', 'ptb') ?></label>
            </div>
            <div class="ptb_back_active_module_input">
                <input type="text" id="ptb_<?php echo $id ?>[timeformat]" name="[<?php echo $id ?>][timeformat]" value="<?php echo isset($module['timeformat']) ? $module['timeformat'] : '' ?>"/>
                <ul>
                    <li>H - <?php _e('Hour with no leading 0 (24 hour)','ptb')?></li>
                    <li>HH - <?php _e('Hour with leading 0 (24 hour)','ptb')?></li>
                    <li>h - <?php _e('Hour with no leading 0 (12 hour)','ptb')?></li>
                    <li>hh - <?php _e('Hour with leading 0 (12 hour)','ptb')?></li>
                    <li>m - <?php _e('Minute with no leading 0','ptb')?></li>
                    <li>mm - <?php _e('Minute with leading 0','ptb')?></li>
                    <li>tt - <?php _e('am or pm for AM/PM','ptb')?></li>
                    <li>TT - <?php _e('AM or PM for AM/PM','ptb')?></li>
                </ul>
            </div>
        </div>
        <?php
    }

	public static function public_enqueue() {
		PTB_Utils::enqueue_style( 'themify-datetimepicker', PTB::$uri . 'admin/css/jquery-ui-timepicker.min.css', array(), PTB::get_version(), 'all' );
        wp_enqueue_script( 'jquery-ui-datepicker' );
        PTB_Utils::enqueue_script( 'themify-datetimepicker', PTB::$uri . 'admin/js/jquery-ui-timepicker.min.js', array('jquery-ui-datepicker'), PTB::get_version(), true );
	}

    public function ptb_submission_form($post_type, array $args, array $module, $post, $lang, array $languages) {
		self::public_enqueue();
		/* the "themify-" prefix is added so on Themify framework it loads as defered */
		PTB_Utils::enqueue_script( 'themify-ptb-submission-date', PTB_Submission::$url . 'public/js/field-date.js', array( 'ptb-submission', 'themify-datetimepicker' ), PTB::get_version(), true );
		PTB_Utils::enqueue_style( 'ptb-submission-date', PTB_Submission::$url . 'public/css/field-event-date.css', [ 'ptb-submission' ] );

        $data = isset($post->ID) ? get_post_meta($post->ID, 'ptb_' . $args['key'], TRUE) : false;
        $kk=rand(10,99);
        ?>
        <div class="ptb_back_active_module_input">
            <?php if (!isset($args['showrange'])): ?>
                <div class="ptb-submission-date-wrap">
                    <input <?php if (!empty($module['dateformat'])): ?>data-dateformat="<?php esc_attr_e($module['dateformat'])?>"<?php endif; ?>  <?php if (isset($module['time'])): ?><?php if (!empty($module['timeformat'])): ?>data-timeformat="<?php esc_attr_e($module['timeformat'])?>"<?php endif; ?> data-time="1"<?php endif; ?>  id="ptb_extra_<?php echo $args['key'].$kk ?>" type="text" name="submission[<?php echo $args['key'] ?>]" data-id="<?php echo $args['key'].$kk ?>" value="<?php echo isset($data['start_date']) ? $data['start_date'] : $data; ?>" class="ptb_extra_input_datepicker"/>
                    <?php echo PTB_Utils::get_icon( 'fa-calendar' ); ?>
                </div>
            <?php else: ?>
                <?php $data = $data && !is_array($data) ? array('start_date' => $data) : $data; ?>
                <table class="ptb-submission-date-range">
                    <tr>
                        <td class="ptb-submission-date-wrap"><input value="<?php echo isset($data['start_date']) ? $data['start_date'] : '' ?>" <?php if (!empty($module['dateformat'])): ?>data-dateformat="<?php esc_attr_e($module['dateformat'])?>"<?php endif; ?> <?php if (isset($module['time'])): ?><?php if (!empty($module['timeformat'])): ?>data-timeformat="<?php esc_attr_e($module['timeformat'])?>"<?php endif; ?> data-time="1"<?php endif; ?> placeholder="<?php _e('Starts on', 'ptb') ?>" type="text" data-id="<?php echo $args['key'].$kk ?>" name="submission[<?php echo $args['key'] ?>][start_date]" id="ptb_submission_<?php echo $args['key'].$kk ?>" /><?php echo PTB_Utils::get_icon( 'fa-calendar' ); ?></td>
                        <td class="ptb-submission-date-arrow"><span class="ptb-submission-arrow-right"></span></td>
                        <td class="ptb-submission-date-wrap"><input value="<?php echo isset($data['end_date']) ? $data['end_date'] : '' ?>" placeholder="<?php _e('Ends on', 'ptb') ?>" type="text"  name="submission[<?php echo $args['key'] ?>][end_date]" id="ptb_submission_<?php echo $args['key'].$kk ?>_end" /><?php echo PTB_Utils::get_icon( 'fa-calendar' ); ?></td>
                    </tr>
                </table>
            <?php endif; ?>
            <?php if (isset($module['show_description'])): ?>
                <div class="ptb-submission-description ptb-submission-<?php echo $args['key'] ?>-description"><?php echo PTB_Utils::get_label($args['description']); ?></div>
            <?php endif; ?>
        </div>
        <?php
    }

    public function ptb_submission_validate(array $post_data, array $args, array $module, $post_type, $post_id, $lang, array $languages) {
        $error = false;
        if (isset($post_data[$module['key']])) {
            $time = isset($module['time']);
            $format = !empty($module['dateformat'])?trim(str_replace(array('yy','MM','mm','m','dd','DD','#'),array('Y','F','#','n','d','L','m'),$module['dateformat'])):'Y-m-d';
            if($time){
                $format.='\@';
                $format.= ! empty( $module['timeformat'] ) ?
					trim( str_replace(array('HH','hh','h','H','mm','m','TT','tt','$','#'),array('$','#','g','G','i','i','A','a','H','h'),$module['timeformat']) ) :
					'h:i a';
            }
            if (isset($args['showrange'])) {
                $values = $post_data[$module['key']];
                $keys = array('start_date', 'end_date');
            } else {
                $keys = array(0);
                $values = array(0 => $post_data[$module['key']]);
            }
            foreach ($keys as $k) {
                if (isset($values[$k]) && trim($values[$k])) {
                    $start_date = sanitize_text_field($values[$k]);
                    $start_date = str_replace(array('AM','PM'),array('am','pm'),$start_date);
                    $convert = DateTime::createFromFormat($format, $start_date);
                    if(!$convert){
                        return sprintf( __('%s has incorrect date format', 'ptb'), PTB_Utils::get_label($args['name']) );
                    }
                    
                    $start_date = $convert->format('Y-m-d@h:i a');  
                    $date = explode('@', $start_date);
                    $valid_start_date = $date[0];
                    $valid_start_date = explode('-', $valid_start_date);
                    if (!checkdate($valid_start_date[1], $valid_start_date[2], $valid_start_date[0])) {
                        return sprintf( __('%s has incorrect date format', 'ptb'), PTB_Utils::get_label($args['name']) );
                    }
                    if ($time && isset($date[1]) && trim($date[1]) && !preg_match("/(0?\d|1[0-2]):(0\d|[0-5]\d) (AM|PM)/i", $date[1], $matches)) {
                        return sprintf( __('%s has incorrect time format', 'ptb'), PTB_Utils::get_label($args['name']) );
                    }
                    if ($k) {
                        $post_data[$module['key']][$k] = $time ? $this->convert_datetime($start_date, 'yes') : $date[0];
                    } else {
                        $post_data[$module['key']] = $time ? $this->convert_datetime($start_date, 'yes') : $date[0];
                    }
                } else {
                    $error = true;
                }
            }
            if ($error && isset($module['required'])) {
                return sprintf( __('%s is required', 'ptb'), PTB_Utils::get_label($args['name']) );
            }
            return $post_data;
        }
    }

    public function ptb_submission_save(array $m, $key, array $post_data, $post_id, $lng) {
        return $m;
    }
    
    
    public function search_date_template($post_type,$id,$args,$module,$value,$label,$lang,$languages){
        $name =  PTB_Utils::get_label($args['name']);
        $name = $name ? sanitize_title($name) : $args['key'];
        if(isset($this->search_date[$name])){
            $value = $this->search_date[$name];
        }
		self::public_enqueue();
        PTB_Search_Public::show_as('date', $post_type, $id, $name, $value, $args['key'], $label);
    }

	/**
	 * Filter empty fields
	 *
	 * @since 1.5.8
	 *
	 * @param array $meta_query
	 * @param string $origk
	 * @param array $cmb_option
	 *
	 * @return array
	 */
	public function event_date_exist ( array $meta_query, $origk,array $cmb_option) {

		// checking for serialized range date is enough.
		$meta_query['compare'] = 'NOT LIKE';
		$meta_query['value'] = '"start_date";s:0:""';

		return $meta_query;
	}
	
    public function search_date($post_id,$post_type,$value,$args,$meta_key,$post_taxonomies){
        $meta_key = 'ptb_'.$meta_key;
        $range = !empty($args['showrange']);
        $include =  !empty($post_id) ? implode(',', array_keys($post_id)) : FALSE;
        $query_args = array(
            'fields' => 'ids',
            'post_type' => $post_type,
            'orderby' => 'ID',
            'order' => 'ASC',
            'nopaging' => 1,
            'include'=> $include,
            'meta_query' => array(
				array(
					'type'  => 'date',
					'key' =>$meta_key
				)
            )
        );
        $from = $to = false;
        $condition = $post_id = array();
        if(!empty($value['from'])){
            $from = esc_sql($value['from']);
            if(!$range){
                $query_args['meta_query'][0]['compare'] = '>=';
                $query_args['meta_query'][0]['value'] = $from;
            }
            else{
                $condition[] = "STR_TO_DATE(SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value,'\"',4),'\"',-1),'%Y-%m-%d')>='$from'";
            }
        }
        if(!empty($value['to'])){
            $to = esc_sql($value['to']);
            if(!$range){
                $query_args['meta_query'][0]['compare'] = $from?'BETWEEN':'<=';
                $query_args['meta_query'][0]['value'] = $from?array($from,$to):$to;
            }
            else{
                $condition[] ="STR_TO_DATE(SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value,'\"',-2),'\"',1),'%Y-%m-%d')<='$to'";
            }
        }
		if($range && !empty($condition)){
            global $wpdb;
            if($include){
                $condition[] = 'post_id IN('.$include.')'; 
            }
            $condition = implode(' AND ',$condition);
            $posts = $wpdb->get_results("SELECT `post_id` FROM `{$wpdb->postmeta}` WHERE `meta_key` = '$meta_key' AND $condition");
            if(!empty($posts)){
                $ids = array();
                foreach ($posts as $p) {
                    $ids[] = $p->post_id;
                } 
                unset($query_args['meta_query']);
                $query_args['include'] = $ids;
            }
            else{
                $from = $to = false;
            }
        }
      
        if($from || $to){
            $posts_array = get_posts($query_args);
            if(!empty($posts_array)){
                foreach ($posts_array as $p) {
                    $post_id[$p] = 1;
                }
            }
        }
        return $post_id;
    }

	public static function admin_column_display( $value ) {
		$format = get_option( 'date_format' );
		if ( is_array( $value ) ) {
			return date_i18n( $format, strtotime( $value['start_date'] ) ) . ' - ' . date_i18n( $format, strtotime( $value['end_date'] ) );
		}

		return date_i18n( $format, strtotime( $value ) );
	}
}
