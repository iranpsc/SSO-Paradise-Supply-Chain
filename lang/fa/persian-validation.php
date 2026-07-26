<?php

$invalidMsg = ':attribute معتبر نمی باشد.';
$incorrectMsg = ':attribute صحیح نمی باشد.';

return [
	'persian_alpha' 			    => ':attribute فقط میتواند شامل حروف فارسی باشد.',
	'persian_num'				    => ':attribute فقط میتواند شامل اعداد فارسی باشد.',
	'persian_alpha_num'			    => ':attribute فقط میتواند شامل حروف و اعداد فارسی باشد.',
	'persian_alpha_eng_num'			=> ':attribute فقط میتواند شامل حروف و اعداد فارسی و اعداد انگلیسی باشد.',
    'persian_not_accept'			=> ':attribute فقط میتواند شامل حروف یا اعداد لاتین باشد.',
    'persian_date'			        => $incorrectMsg,
    'persian_date_between_year'			=> ':attribute باید بین سال های :afterDate تا :beforeDate باشد.',
    'ir_mobile'				        => $incorrectMsg,
    'ir_phone' 				        => $incorrectMsg,
    'ir_phone_area_code'	                => $incorrectMsg,
    'ir_postal_code'			    => $invalidMsg,
    'ir_bank_card_number' 			=> $invalidMsg,
    'ir_iban'						=> $invalidMsg,
    'ir_national_id' 				=> $invalidMsg,
];
