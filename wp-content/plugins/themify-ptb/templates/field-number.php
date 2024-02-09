<?php
/**
 * Number field template
 *
 * To override this template copy it to <your_theme>/plugins/themify-ptb/templates/field-number.php
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

if (!empty($meta_data[$args['key']]) || $meta_data[$args['key']]==='0') {
	$value = $meta_data[$args['key']];
	$range =  !empty($args['range']);
	$is_array = is_array($value);
	if(!$range && $is_array){
		$value = current($value);
	}
	if(!$value && $value!=='0'){
		return;
	}
	$thousand_separator =  !empty($data['thousand'])?$data['thousand']:false;
	if($thousand_separator){
		$decimals =  isset($data['ndecimals'])?(int)$data['ndecimals']:'';
		$decimal_separator = !empty($data['decimal'])?$data['decimal']:'';
	}
	$currency = !empty($data['currency'])?$data['currency']:'';
	$currency_pos = $currency && !empty($data['currency_pos'])?$data['currency_pos']:'left';
	if ( ! isset( $data['seperator'] ) ) {
		$data['seperator'] = ' - ';
	}

	if ( $range && $is_array ) {
		if ( ! empty( $value['from'] ) ) {
			if($thousand_separator){
				$value['from'] = number_format( $value['from'], $decimals, $decimal_separator, $thousand_separator );
			}
			if($currency_pos){
				$value['from'] = PTB_Utils::get_price_format($currency_pos, $currency, $value['from']);
			}
			echo $value['from'];
		}

		if ( ! empty( $value['to'] ) ) {
			echo '<span class="number_seperator">' . $data['seperator'] . '</span>';
			if( $thousand_separator ) {
				$value['to'] = number_format( $value['to'], $decimals, $decimal_separator, $thousand_separator );
			}
			if($currency_pos){
				$value['to'] = PTB_Utils::get_price_format($currency_pos, $currency, $value['to']);
			}
			echo $value['to'];
		}

	} else {
		if($thousand_separator){
			$value = number_format( $value, $decimals, $decimal_separator, $thousand_separator );
		}
		if($currency_pos){
			$value = PTB_Utils::get_price_format($currency_pos, $currency, $value);
		}
		echo $value;
	}

}