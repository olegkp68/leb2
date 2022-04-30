<?php
class OPCHikaCurrency {
	public static function getVendorStyle(&$arr) {
		//var op_vendor_style = \'1|£|2|.|,|3|8|8|{symbol}{number}|{symbol}{sign}{number}\';  ';
		
		$number = 0.99; 
		
		$ref = OPCHikaRef::getInstance(); 
		$currencyClass = $ref->currencyClass; 
		$currency_id = (int) $currency_id;
		
		$currency_id = $currencyClass->mainCurrency();
		
		$null = null;
		$currencies = $currencyClass->getCurrencies($currency_id,$null);
		$data=$currencies[$currency_id];
		if(empty($format_override)) {
			$format = $data->currency_format;
		} else {
			$format = $format_override;
		}
		$locale = $data->currency_locale;

		$config = hikashop_config();
		if(!$config->get('round_calculations', 0) && !empty($locale['rounding_increment']) && $locale['rounding_increment'] > 0.00001){
			$number = $currencyClass->roundByIncrement($number, (float)$locale['rounding_increment']);
		}

		preg_match_all('/%((?:[\^!\-]|\+|\(|\=.)*)([0-9]+)?(?:#([0-9]+))?(?:\.([0-9]+))?([in%][in]?)/', $format, $matches, PREG_SET_ORDER);
		foreach ($matches as $fmatch) {
			$value = (float)$number;
			$flags = array(
				'fillchar'  => preg_match('/\=(.)/', $fmatch[1], $match) ? $match[1] : ' ',
				'nogroup'   => preg_match('/\^/', $fmatch[1]) > 0,
				'usesignal' => preg_match('/\+|\(/', $fmatch[1], $match) ? $match[0] : '+',
				'nosimbol'  => preg_match('/\!/', $fmatch[1]) > 0,
				'isleft'	=> preg_match('/\-/', $fmatch[1]) > 0
			);
			$width	    = trim($fmatch[2]) ? (int)$fmatch[2] : 0;
			$left	    = trim($fmatch[3]) ? (int)$fmatch[3] : 0;
			$conversion = $fmatch[5];
			$right	    = trim($fmatch[4]) ? (int)$fmatch[4] : $locale[($conversion[0] == 'i' ? 'int_' : '').'frac_digits'];

			$positive = true;
			$letter = $positive ? 'p' : 'n';
			$prefix = $suffix = $cprefix = $csuffix = $signal = '';
			//positive format: 
			
			$signal = $positive ? $locale['positive_sign'] : $locale['negative_sign'];
			switch (true) {
				case $locale[$letter.'_sign_posn'] == 1 && $flags['usesignal'] == '+':
					$prefix = $signal;
					break;
				case $locale[$letter.'_sign_posn'] == 2 && $flags['usesignal'] == '+':
					$suffix = $signal;
					break;
				case $locale[$letter.'_sign_posn'] == 3 && $flags['usesignal'] == '+':
					$cprefix = $signal;
					break;
				case $locale[$letter.'_sign_posn'] == 4 && $flags['usesignal'] == '+':
					$csuffix = $signal;
					break;
				case $flags['usesignal'] == '(':
				case $locale[$letter.'_sign_posn'] == 0:
					$prefix = '(';
					$suffix = ')';
					break;
			}
			if (!$flags['nosimbol']) {
				$currency = $cprefix .
							($conversion[0] == 'i' ? $data->currency_code : $data->currency_symbol) .
							( isset($conversion[1]) ? ' '.( $conversion[1] == 'i' ? $data->currency_code : $data->currency_symbol) : '') .
							$csuffix;
			} else {
				$currency = '';
			}
			$space  = $locale[$letter.'_sep_by_space'] ? ' ' : '';

			$value = $currencyClass->numberFormat($value, $right, $locale['mon_decimal_point'],
					 $flags['nogroup'] ? '' : $locale['mon_thousands_sep'], $locale['mon_grouping']);
			$value = @explode($locale['mon_decimal_point'], $value);

			$n = strlen($prefix) + strlen($currency) + strlen($value[0]);
			if ($left > 0 && $left > $n) {
				$value[0] = str_repeat($flags['fillchar'], $left - $n) . $value[0];
			}
			$value = implode($locale['mon_decimal_point'], $value);
			if ($locale[$letter.'_cs_precedes']) {
				$local_format = $prefix . $currency . $space . '{number}' . $suffix;
			} else {
				$local_format = $prefix . '{number}' . $space . $currency . $suffix;
			}
			if ($width > 0) {
				$local_format = str_pad($local_format, $width, $flags['fillchar'], $flags['isleft'] ?
						 STR_PAD_RIGHT : STR_PAD_LEFT);
			}

			$format_positive = str_replace($fmatch[0], $local_format, $format);
			
			
			//negative format: 
			$positive = false; 
			$value = (float)$number;
			$letter = $positive ? 'p' : 'n';
			$prefix = $suffix = $cprefix = $csuffix = $signal = '';
			$signal = $positive ? $locale['positive_sign'] : $locale['negative_sign'];
			switch (true) {
				case $locale[$letter.'_sign_posn'] == 1 && $flags['usesignal'] == '+':
					$prefix = $signal;
					break;
				case $locale[$letter.'_sign_posn'] == 2 && $flags['usesignal'] == '+':
					$suffix = $signal;
					break;
				case $locale[$letter.'_sign_posn'] == 3 && $flags['usesignal'] == '+':
					$cprefix = $signal;
					break;
				case $locale[$letter.'_sign_posn'] == 4 && $flags['usesignal'] == '+':
					$csuffix = $signal;
					break;
				case $flags['usesignal'] == '(':
				case $locale[$letter.'_sign_posn'] == 0:
					$prefix = '(';
					$suffix = ')';
					break;
			}
			if (!$flags['nosimbol']) {
				$currency = $cprefix .
							($conversion[0] == 'i' ? $data->currency_code : $data->currency_symbol) .
							( isset($conversion[1]) ? ' '.( $conversion[1] == 'i' ? $data->currency_code : $data->currency_symbol) : '') .
							$csuffix;
			} else {
				$currency = '';
			}
			$space  = $locale[$letter.'_sep_by_space'] ? ' ' : '';

			$value = $currencyClass->numberFormat($value, $right, $locale['mon_decimal_point'],
					 $flags['nogroup'] ? '' : $locale['mon_thousands_sep'], $locale['mon_grouping']);
			$value = @explode($locale['mon_decimal_point'], $value);

			$n = strlen($prefix) + strlen($currency) + strlen($value[0]);
			if ($left > 0 && $left > $n) {
				$value[0] = str_repeat($flags['fillchar'], $left - $n) . $value[0];
			}
			$value = implode($locale['mon_decimal_point'], $value);
			if ($locale[$letter.'_cs_precedes']) {
				$local_format = $prefix . $currency . $space . '{number}' . $suffix;
			} else {
				$local_format = $prefix . '{number}' . $space . $currency . $suffix;
			}
			if ($width > 0) {
				$local_format = str_pad($local_format, $width, $flags['fillchar'], $flags['isleft'] ?
						 STR_PAD_RIGHT : STR_PAD_LEFT);
			}

			
			
			$format_negative = str_replace($fmatch[0], $local_format, $format);
			
			
		}
		
		
		// op_vendor_style = '1|&euro;|2|.|\'|3|0'; 
	
	
	$arr = array(); 
	$arr[0] = '1'; 
	$arr[1] = $currency; 
	$arr[2] = $right; 
	$arr[3] = $locale['mon_decimal_point']; 
	$arr[4] = $locale['mon_thousands_sep']; 
	// for now
	$arr[5] = '3';
	$arr[6] = '8';
	$arr[7] = '8';
	$arr[8] = $format_positive; 
	$arr[9] = $format_negative; 
	$vendor_currency_display_style = implode('|', $arr);
	
		
		//var op_vendor_style = \'1|£|2|.|,|3|8|8|{symbol}{number}|{symbol}{sign}{number}\';  ';
		return $vendor_currency_display_style;
	}
}