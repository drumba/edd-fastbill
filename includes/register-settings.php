<?php
/**
 * Register Settings
 *
 * @package     FastBill Integration for Easy Digital Downloads
 * @subpackage  Register Settings
 * @copyright   Copyright (c) 2013, Markus Drubba (dev@markusdrubba.de)
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

function drubba_fb_register_fastbill_section( $sections ) {
	$sections['edd-fastbill'] = __( 'FastBill', 'edd-fastbill' );

	return $sections;
}

add_filter( 'edd_settings_sections_extensions', 'drubba_fb_register_fastbill_section', 10, 1 );

/**
 * Register Settings
 *
 * Registers the required settings for the plugin and adds them to the 'Misc' tab.
 *
 * @access      private
 * @since       1.0.0
 * @return      void
 */
function drubba_fb_register_settings( $settings ) {
	$fastbill_settings = array(
		array(
			'id'   => 'drubba_fastbill',
			'name' => '<h3>' . __( 'FastBill Settings', 'edd-fastbill' ) . '</h3>',
			'desc' => '',
			'type' => 'header',
		),
		array(
			'id'   => 'drubba_fb_fastbill_email',
			'name' => __( 'FastBill Email', 'edd-fastbill' ),
			'type' => 'text',
		),
		array(
			'id'   => 'drubba_fb_fastbill_api_key',
			'name' => __( 'FastBill API Key', 'edd-fastbill' ),
			'type' => 'text',
		),
		array(
			'id'      => 'drubba_fb_fastbill_invoice_template',
			'name'    => __( 'Direct payment invoice template', 'edd-fastbill' ),
			'desc'    => __( 'Choose invoice template. If you edit a Template in FastBill, you have to reassign it here.', 'edd-fastbill' ),
			'std'     => '',
			'type'    => 'select',
			'options' => drubba_fb_get_invoice_templates()
		),
		array(
			'id'      => 'drubba_fb_fastbill_invoice_template_advance_payment',
			'name'    => __( 'Advance payment invoice template', 'edd-fastbill' ),
			'desc'    => __( 'Choose invoice template. If you edit a Template in FastBill, you have to reassign it here.', 'edd-fastbill' ),
			'std'     => '',
			'type'    => 'select',
			'options' => drubba_fb_get_invoice_templates()
		),
		array(
			'id'   => 'drubba_fb_fastbill_sendbyemail',
			'name' => __( 'Send invoice', 'edd-fastbill' ),
			'desc' => __( 'Send invoice to customer via email (is sent via FastBill)', 'edd-fastbill' ),
			'type' => 'checkbox',
		),
		array(
			'id'   => 'drubba_fb_fastbill_online_invoice',
			'name' => __( 'Online invoice', 'edd-fastbill' ),
			'desc' => __( 'I activated the online invoice functionalty within my Fastbill account to accessing the generated invoice.', 'edd-fastbill' ),
			'type' => 'checkbox',
		),
		array(
			'id'   => 'drubba_fb_fastbill_advance_payment_gateways_headline',
			'desc' => __( 'For <em>advance payment</em> methods it\'s sometimes needed to send the invoice to the custumer before he/she can process the payment. In the following settings you can activate those payment gateways that are not sending the money directly to your account (like PayPal does).', 'edd-fastbill' ),
			'type' => 'descriptive_text',
		),
		array(
			'id'      => 'drubba_fb_fastbill_advance_payment_gateways',
			'name'    => __( 'Advance payment gateways', 'edd-fastbill' ),
			'type'    => 'gateways',
			'options' => edd_get_payment_gateways(),
		),
	);


	if ( drubba_fb_cfm_active() ) { // @since 1.1.0


		$fastbill_settings[] = array( // @since 1.1.0
			'id'   => 'drubba_fastbill_fields',
			'name' => '<h3>' . __( 'FastBill Customer Fields', 'edd-fastbill' ) . '</h3>',
			'desc' => '',
			'type' => 'header',
		);

		$fields = drubba_fb_get_customer_fields();
		if ( $fields ) {

			foreach ( $fields as $key => $value ) {

				$desc                = isset( $value['desc'] ) ? $value['desc'] : '';
				$fastbill_settings[] = array(
					'id'      => 'drubba_fb_fastbill_' . $key,
					'name'    => $value['name'],
					'desc'    => $desc,
					'type'    => 'select',
					'options' => drubba_fb_get_checkout_fields()
				);

			}

		}
	}

	$fastbill_settings[] = array(
		'id'   => 'drubba_fb_fastbill_debug_log',
		'name' => __( 'Debug mode', 'edd-fastbill' ),
		'desc' => __( 'Write debug logs into the database.', 'edd-fastbill' ),
		'type' => 'checkbox',
	);

	$fastbill_settings[] = array(
		'id'   => 'drubba_fb_fastbill_reset_debug_log',
		'name' => __( 'Reset debug entries', 'edd-fastbill' ),
		'desc' => __( 'Remove debug logs from database.', 'edd-fastbill' ),
		'type' => 'checkbox',
	);

	if ( version_compare( EDD_VERSION, 2.5, '>=' ) ) {
		$fastbill_settings = array( 'edd-fastbill' => $fastbill_settings );
	}

	return array_merge( $settings, $fastbill_settings );
}

add_filter( 'edd_settings_extensions', 'drubba_fb_register_settings', 10, 1 );

/**
 * drubba_fb_cfm_active()
 *
 * check if edd-checkout-fields extension is active
 *
 * @since 1.1.0
 * @return bool
 */
function drubba_fb_cfm_active() {
	return class_exists( 'EDD_Checkout_Fields_Manager' );
}

/**
 * drubba_fb_get_checkout_fields()
 *
 * get checkout fields from edd-checkout-fields extension
 *
 * @since 1.1.0
 * @return array|bool
 */
function drubba_fb_get_checkout_fields() {
	$cfm_id = get_option( 'edd_cfm_id' );
	if ( ! $cfm_id ) {
		return false;
	}

	$return     = array( '' => '' );
	$cfm_fields = get_post_meta( $cfm_id, 'edd-checkout-fields', true );
	foreach ( $cfm_fields as $field ) {
		if ( isset( $field['name'] ) && isset( $field['label'] ) ) {
			$return[ $field['name'] ] = $field['label'];
		}
	}

	return $return;
}

/**
 * drubba_fb_get_customer_fields()
 *
 * define the default CUSTOMER fields for FASTBILL API
 *
 * @since 1.1.0
 * @return array
 */
function drubba_fb_get_customer_fields() {
	return array(
		'ORGANIZATION' => array(
			'name' => __( 'Organization', 'edd-fastbill' ),
			'desc' => __( 'If set by customer, than Business', 'edd-fastbill' )
		), // (* if business )
		'SALUTATION'   => array(
			'name' => __( 'Salutation', 'edd-fastbill' ),
			'desc' => __( 'Use Herr, Hr., Hr, Mister, Mr, Mr. & Frau, Fr., Fr, Misses, Miss, Mrs.', 'edd-fastbill' )
		), // (mr|mrs|family) // maybe we can check for common values, but it is not perfect
		'PHONE'        => array( 'name' => __( 'Phone', 'edd-fastbill' ) ),
		'FAX'          => array( 'name' => __( 'Fax', 'edd-fastbill' ) ),
		'MOBILE'       => array( 'name' => __( 'Mobile', 'edd-fastbill' ) ),
	);
}

/**
 * drubba_fb_get_invoice_templates()
 *
 * get invoice templates out of the fastbill account
 *
 * @since 1.1.0
 * @return array|bool
 */
function drubba_fb_get_invoice_templates() {
	$templates = array(
		'' => __( 'Default', 'edd-fastbill' )
	);

	try {

		$fastbill = new \drumba\EDD\FastBill\FastBill();

	} catch ( \Exception $e ) {

		return $templates;

	}

	$templates_array = $fastbill->templates_get();

	if ( ! isset( $templates_array['TEMPLATE'] ) ) {
		return $templates;
	}

	foreach ( $templates_array['TEMPLATE'] as $template ) {
		if ( isset( $template['TEMPLATE_NAME'] ) && isset( $template['TEMPLATE_ID'] ) ) {
			$templates[ $template['TEMPLATE_ID'] ] = $template['TEMPLATE_NAME'];
		}
	}

	return $templates;
}

/**
 * Display field for debug output
 *
 * @param $html
 * @param $args
 *
 * @return string
 */
function drubba_fb_fastbill_show_debug_log_field( $html, $args ) {
	if ( $args['id'] == 'drubba_fb_fastbill_debug_log' ) {
		$current_log = get_option( 'edd_fastbill_error_log', '' );
		if ( ! empty( $current_log ) ) {
			$html = $html . '<br><br><textarea style="width:100%;height:400px;" readonly>' . $current_log . '</textarea>';
		}
	}

	return $html;
}

add_filter( 'edd_after_setting_output', 'drubba_fb_fastbill_show_debug_log_field', 10, 2 );

/**
 * Reset database debug log
 *
 * @param $new_value
 * @param $old_value
 *
 * @return mixed
 */
function drubba_fb_fastbill_reset_debug_log( $new_value, $old_value ) {
	if ( isset( $new_value['drubba_fb_fastbill_reset_debug_log'] ) && $new_value['drubba_fb_fastbill_reset_debug_log'] == 1 ) {
		update_option( 'edd_fastbill_error_log', '' );
		$new_value['drubba_fb_fastbill_reset_debug_log'] = - 1;
	}

	return $new_value;
}

add_action( 'pre_update_option_edd_settings', 'drubba_fb_fastbill_reset_debug_log', 10, 2 );
