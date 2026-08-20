<?php

/**
 * Gravity Forms
 *
 * @package      Custom
 * @since        1.0.0
 * @link         https://www.gravityforms.com/documentation/
 */
add_filter('gform_default_notification', '__return_false');
//add_filter( 'gform_disable_form_legacy_css', '__return_true' );
//add_filter( 'gform_disable_form_theme_css', '__return_true' );
add_filter('gform_form_theme_slug', function ($slug, $form) {
	return 'gravity-theme';
}, 10, 2);

add_filter('gform_enable_legacy_markup', '__return_false');

/**
 * Adds Bootstrap class markup to Gravity Forms field output.
 */
add_filter('gform_field_content', 'lvl_bootstrap_classes_to_fields', 10, 5);
function lvl_bootstrap_classes_to_fields($content, $field, $value, $lead_id, $form_id)
{
	if ($field['type'] != 'hidden' && $field['type'] != 'list' && $field['type'] != 'multiselect' && $field['type'] != 'checkbox' && $field['type'] != 'fileupload' && $field['type'] != 'date' && $field['type'] != 'html' && $field['type'] != 'address' && $field['type'] != 'select') {
		$content = str_replace('class=\'medium', 'class=\'form-control medium', $content);
		$content = str_replace('class=\'large', 'class=\'form-control large', $content);
	}
	if ($field['type'] == 'select') {
		$content = str_replace('gfield_select', 'gfield_select form-select rounded-0', $content);
	}
	if ($field['type'] == 'name' || $field['type'] == 'address') {
		$content = str_replace('<input ', '<input class=\'form-control\' ', $content);
	}
	if ($field['type'] == 'textarea') {
		$content = str_replace('class=\'textarea', 'class=\'form-control textarea', $content);
	}
	if ($field['type'] == 'checkbox') {
		$content = str_replace('li class=\'', 'li class=\'form-check ', $content);
		$content = str_replace('<input ', '<input class=\'form-check-input\' ', $content);
		$content = str_replace('<label class=\'gfield_label\'', '<label class=\'gfield_label form-check-label\' ', $content);
	}
	if ($field['type'] == 'radio') {
		$content = str_replace('li class=\'', 'li class=\'form-check ', $content);
		$content = str_replace('<input ', '<input class=\'form-check-input\' ', $content);
		$content = str_replace('<label class=\'gfield_label\'', '<label class=\'gfield_label form-check-label\' ', $content);
	}
	if ($field['type'] == 'fileupload') {
		$content = str_replace('<span class=\'gform_drop_instructions\'>Drop files here or </span>', '<label for=\'gform_browse_button_' . $form_id . '_' . $field['id'] . '\' class=\'gform_drop_instructions\'>Drop files here or </label>', $content);
		$content = str_replace('gform_drop_area', 'gform_drop_area form-control-file', $content);
	}
	if ($field['type'] == 'date') {
		$content = str_replace('class=\'datepicker', 'class=\'datepicker form-control', $content);
	}

	//gfield_choice_all_toggle gform-theme-button--size-sm
	if (str_contains($content, 'gform-theme-button--size-sm')) {
		$content = str_replace('gform-theme-button--size-sm', 'btn btn-info btn-sm', $content);
	}


	return $content;
}


/**
 * Change the gravity form submit input to a button
 */

add_filter('gform_submit_button', 'change_gform_submit_button', 10, 2);
function change_gform_submit_button($button, $form)
{
	$button_text = $form['button']['text'];

	return "<button class='gform_button button btn btn-primary-outline d-block px-4' id='gform_submit_button_{$form['id']}'><span class=''>" . $button_text . "</span></button>";
}

/**
 * Adds Event Tracking to failed validation for form submissions.
 */
add_filter('gform_validation_message', 'lvl_add_validation_tracking', 10, 2);
function lvl_add_validation_tracking($message, $form)
{

	$dataLayer = [
		'event'          => 'Send Event',
		'event_category' => 'Errors',
		'event_action'   => 'Form Validation Failed',
		'event_label'    => $form['title'],
		'event_value'    => 0,
	];

	$message .= '
	<script>
	try {
		dataLayer.push( ' . json_encode($dataLayer) . ' );
	} catch ( err ) {}
	</script>
	';

	return $message;
}


/**
 * Adds Event Tracking to inline confirmation messages based on form title.
 */
// TODO: deprecated, candidate for removal
//add_filter('gform_confirmation', 'lvl_custom_form_confirmation', 10, 4);
function lvl_custom_form_confirmation($confirmation, $form, $entry, $ajax)
{

	$dataLayer = [
		'event'          => 'Send Event',
		'event_category' => 'Conversions',
		'event_value'    => 500,
		'event_label'    => $form['id'],
	];

	switch ($form['id']) {
		default:
			$dataLayer['event_action'] = 'Form Submission - ' . $form['title'];
			break;
	}

	// Conditional added to check if confirmation is a redirect and reformat it in order to maintain tracking via script
	// if( isset( $confirmation['redirect'] ) ) {
	if (isset($confirmation['redirect']) || strpos($confirmation, 'gformRedirect') !== false) {
		if (isset($confirmation['redirect'])) {
			$url = esc_url_raw($confirmation['redirect']);
		} else {
			preg_match('/"((?:http|https)[^"]+)"/', $confirmation, $matches);
		}
		if (! empty($matches)) {
			$url = stripslashes($matches[1]);
		}
		$confirmation = '
<h2>Thanks!</h2>
<p>If you were not redirected your browser may have blocked it. Please use the link below to continue.</p>
<p><a class="btn btn-primary" href="' . $url . '" target="_blank">Continue <span class="align-text-bottom  d-inline-block" aria-hidden="true"><svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="chevron-right" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="svg-inline--fa fa-chevron-right fa-w-10 fa-3x" style="width: 1em;height: 1em;"><path fill="currentColor" d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z" class=""></path></svg></span></a></p>';
		$confirmation .= "
<script type=\"text/javascript\">var timeoutID = window.setTimeout(\"window.open('$url', '_self')\",500);</script>";
	}

	$confirmation .= '
	<script>
	try{
		dataLayer.push( ' . json_encode($dataLayer) . ' );
		dataLayer.push( { "event" : "Conversion" } );
		} catch ( err ) {}
		</script>
		';

	return $confirmation;
}


// emails the GF form submission notifcation to the appropriate person's email (without exposing it)
//add_filter( 'gform_notification_4', 'lvl_custom_gform_notification_4', 10, 4 );
function lvl_custom_gform_notification_4($notification, $form, $entry)
{
	$personid           = rgar($entry, '21');
	$email              = get_field('PrimaryEmail', $personid);
	$notification['to'] .= ',' . $email;

	return $notification;
}


// Append to form #3's confirmation message a link to download the current post's ungated resource.
// Also, set a cookie so other resources appear ungated.
// add_filter( 'gform_confirmation_3', 'lvl_custom_form_confirmation_3', 10, 4 );
// function lvl_custom_form_confirmation_3( $confirmation, $form, $entry, $ajax ) {
// 	$postid   = rgar( $entry, '7' );
// 	$resource = get_field( 'resource_file', $postid );
// 	if ( ! empty( $resource ) ) :
// 		setcookie( 'STYXKEY_ungated', 'jhbgr3467tnc', time() + 60 * 60 * 24 * 30 );

// 		return $confirmation . '<p class="text-center"><a href="' . esc_url( $resource['url'] ) . '" class="btn btn-primary mt-3" download>Download</a></p>';
// 	endif;

// 	return $confirmation;
// }

// Exclude a field from notifications assign the field the CSS Class Name gf_exclude and then use the {all_fields:exclude} merge tag
// add_filter( 'gform_merge_tag_filter', 'lvl_exclude_from_all_fields', 10, 4 );
function lvl_exclude_from_all_fields($value, $merge_tag, $options, $field)
{
	$options_array = explode(",", $options);
	$exclude       = in_array("exclude", $options_array);
	$class_array   = explode(" ", $field["cssClass"]);
	$exclude_class = in_array("gf_exclude", $class_array);

	if ($merge_tag == "all_fields" && $exclude == true && $exclude_class == true) {
		return false;
	} else {
		return $value;
	}
}


// ADD A FIELD TO THE FORM WITH THE LABEL "field_summary" AND THE TYPE "hidden"
//add_action('gform_pre_submission', 'field_summary_builder');
function field_summary_builder($form)
{
	$form_field_summary_id = 0;
	$form_field_summary    = '';
	$form_fields_keyed     = [];

	foreach ($form['fields'] as $field) {
		$form_fields_keyed[$field->id] = $field;

		if ($field->label == 'field_summary') {
			$form_field_summary_id = $field->id;

			break;
		}
	}

	if ($form_field_summary_id) {
		foreach ($_POST as $key => $item) {
			if (str_contains($key, 'input_')) {
				if ($key !== 'input_' . $form_field_summary_id) {
					$form_field = $form_fields_keyed[str_replace('input_', '', $key)] ?? false;
					if ($form_field) {
						if ($form_field->type != 'html') {
							$form_field_summary .= '<p><strong>' . esc_attr($form_field->label) . '</strong>' . ': ' . esc_attr($item) . "</p>";
						}
					} else {
						$form_field_summary .= '<p><strong>' . esc_attr($key) . '</strong>' . ': ' . esc_attr($item) . "</p>";
					}
				}
			}
		}

		$_POST['input_' . $form_field_summary_id] = $form_field_summary;
	}
}


//add_filter('gform_field_value_vendor', 'lvl_gform_vendor');
function lvl_gform_vendor($value)
{
	if (get_post_type() !== 'vendor') {
		return $value;
	}

	$vendor_slug = get_post_field('post_name');

	return $vendor_slug;
}


//add_filter( 'gform_notification', 'lvl_custom_gform_notification_all', 10, 4 );
function lvl_custom_gform_notification_all($notification, $form, $entry)
{
	if (get_post_type() !== 'vendor') {
		return $notification;
	}

	$is_vendor_hidden_field = false;
	foreach ($form['fields'] as $field) {
		if (strtolower(trim($field->label)) == 'vendor') {
			$is_vendor_hidden_field = true;

			break;
		}
	}

	if (! $is_vendor_hidden_field) {
		return $notification;
	}

	$vendor                  = str_replace('Partner With', '', get_the_title());
	$notification['subject'] = 'Vendor Contact Form Submission: ' . $vendor;

	return $notification;
}

/**
 * Description: Checks if the current form field is a hidden field and if it is, adds the inputName as a data attribute.
 * This is used to pass the inputName to the frontend for use in the tracking.
 *
 * @param $input
 * @param $field
 * @param $value
 * @param $lead_id
 * @param $form_id
 *
 * @return mixed|string
 */
function lvl_hidden_field_markup($input, $field, $value, $lead_id, $form_id)
{
	if ($field->inputName !== '' && $field->type == 'hidden') {
		$input = "<input data-gfield-key='{$field->inputName}' type='{$field->type}' name='input_{$field->id}' class='{$field->inputName}' value='{$value}'>";
	}

	return $input;
}

add_filter('gform_field_input', 'lvl_hidden_field_markup', 10, 5);


// ======================================================================================================
// BEGIN MAILCHIMP INTEGRATIONS
//

/**
 * Add a new notification event for Mailchimp API issues.
 */
add_filter('gform_notification_events', function ($events) {
	$events['mailchimp_api_issue'] = 'Mailchimp API Issue';

	return $events;
});

/**
 * Trigger a notification when there is an issue with the Mailchimp API.
 */
add_action('gform_mailchimp_error', 'lvl_send_mailchimp_error_email', 10, 4);
function lvl_send_mailchimp_error_email($feed, $entry, $form, $error_message): void
{
	GFCommon::log_debug('gform_mailchimp_error: ' . __METHOD__ . '(): ' . print_r($error_message, true));
	GFAPI::send_notifications($form, $entry, 'mailchimp_api_issue');
}

//
// END MAILCHIMP INTEGRATIONS
// ======================================================================================================

// ======================================================================================================
// BEGIN WEBHOOKS INTEGRATIONS
//

/**
 * Add a new notification event for Webhooks API issues.
 */
add_filter('gform_notification_events', function ($events) {
	$events['webhooks_api_issue'] = 'Webhooks API Issue';

	return $events;
});

/**
 * Trigger a notification when there is an issue with the Webhooks API.
 */
add_action('gform_webhooks_error', 'lvl_send_webhooks_error_email', 10, 4);

function lvl_send_webhooks_error_email($feed, $entry, $form, $error_message): void
{
	GFCommon::log_debug('gform_webhooks_error: ' . __METHOD__ . '(): ' . print_r($error_message, true));
	GFAPI::send_notifications($form, $entry, 'webhooks_api_issue');
}

//
// END WEBHOOKS INTEGRATIONS
// ======================================================================================================