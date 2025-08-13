<?php
/* ======================================================
 # Login as User for WordPress - v1.4.4 (pro version)
 # -------------------------------------------------------
 # For WordPress
 # Author: Web357
 # Copyright @ 2014-2022 Web357. All rights reserved.
 # License: GNU/GPLv3, http://www.gnu.org/licenses/gpl-3.0.html
 # Website: https:/www.web357.com
 # Demo: https://demo.web357.com/wordpress/login-as-user/wp-admin/
 # Support: support@web357.com
 # Last modified: Tuesday 14 June 2022, 06:08:05 PM
 ========================================================= */
/**
 * Define the internationalization functionality
 */
class LoginAsUser_fieldsPro {

	function textField($args) 
	{ 
		$options = get_option('login_as_user_options');
		$class = (isset($args['_class'])) ? $args['_class'] : '';
		$placeholder = (isset($args['placeholder'])) ? $args['placeholder'] : '';
		$size = (isset($args['size'])) ? $args['size'] : 10;
		$maxlength = (isset($args['maxlength'])) ? $args['maxlength'] : 50;
		$default_value = (isset($args['default_value'])) ? $args['default_value'] : '';
		$desc = (isset($args['desc'])) ? $args['desc'] : '';
		$prefix = (isset($args['prefix'])) ? $args['prefix'] : '';
		?>
		<fieldset><?php echo (!empty($prefix) ? $prefix : ''); ?>
		<input 
			type='text' 
			name='login_as_user_options[<?php echo esc_attr($args['name']); ?>]' 
			id='<?php echo esc_attr($args['label-for']); ?>' 
			class='<?php echo esc_attr($class); ?>' 
			placeholder='<?php echo esc_html__($placeholder); ?>'
			value='<?php echo esc_attr(isset($options[$args['name']]) ? $options[$args['name']] : $default_value); ?>'
			size='<?php echo absint($size); ?>'
			maxlength='<?php echo absint($maxlength); ?>'
			>
		</fieldset>
		<?php if (!empty($desc)): ?>
        <p class="description">
			<?php echo wp_kses( __( $desc, 'login-as-user-pro' ), array( 'strong' => array(), 'br' => array() ) ); ?>
		</p>
		<?php endif; ?>
		<?php
	}
	
	function textFieldForWeb357LicenseKey($args) 
	{ 
		$options = get_option('login_as_user_options');
		$class = (isset($args['_class'])) ? $args['_class'] : '';
		$placeholder = (isset($args['placeholder'])) ? $args['placeholder'] : '';
		$size = (isset($args['size'])) ? $args['size'] : 10;
		$maxlength = (isset($args['maxlength'])) ? $args['maxlength'] : 50;
		$default_value = (isset($args['default_value'])) ? $args['default_value'] : '';
		$desc = (isset($args['desc'])) ? $args['desc'] : '';
		$prefix = (isset($args['prefix'])) ? $args['prefix'] : '';
		$domain = $_SERVER['HTTP_HOST'];
		$value = esc_attr(isset($options[$args['name']]) ? $options[$args['name']] : $default_value); ?>

		<fieldset><?php echo (!empty($prefix) ? $prefix : ''); ?>
		<input 
			type='text' 
			name='login_as_user_options[<?php echo esc_attr($args['name']); ?>]' 
			id='<?php echo esc_attr($args['label-for']); ?>' 
			class='<?php echo esc_attr($class); ?>' 
			placeholder='<?php echo esc_html__($placeholder); ?>'
			value='<?php echo esc_attr(isset($options[$args['name']]) ? $options[$args['name']] : $default_value); ?>'
			size='<?php echo absint($size); ?>'
			maxlength='<?php echo absint($maxlength); ?>'
			>
		</fieldset>
		<?php if (!empty($desc)): ?>
        <p class="description">
			<?php echo __( $desc, 'login-as-user-pro' ); ?>
		</p>
		<?php endif; ?>

		<input type="hidden" name="w357_domain" id="w357_domain" value="<?php echo $domain; ?>" />
		<div id="apikey-container">
		<div class="web357-loading-gif text-center" style="display:none"></div>

		<?php
		if (!empty($value))
		{
			$html = '';
			// Get license key status
			$url = 'https://www.web357.com/wp-json/web357-api-key/v1/status/'.$value;
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);

			$resp = curl_exec($ch);

			curl_close($ch);

			$show_active_key_button = true;
			if ($resp === FALSE) 
			{
				$html .= '<div style="margin: 20px 0;" id="w357-activated-successfully-msg" class="w357-alert w357-alert-danger">'.__('Call with web357.com has been failed. Please, try again later or contact us at support@web357.com.', 'login-as-user-pro').'</div>';
			} 
			else 
			{
				$resp = json_decode($resp);

				if (isset($resp->status) && ($resp->status == 1 || $resp->status == 'old_api_key'))
				{
					$html .= '<div style="margin: 20px 0;" id="w357-activated-successfully-msg" class="w357-alert w357-alert-success">'.__('Your License Key <strong>' . $value . '</strong> is active and validated.', 'login-as-user-pro').'</div>';
					$show_active_key_button = false;
				}
				elseif (isset($resp->status) && $resp->status == 0)
				{
					$html .= '<div style="margin: 20px 0;" id="w357-activated-successfully-msg" class="w357-alert w357-alert-danger">'.__('Your License Key <strong>' . $value . '</strong> is valid, but is not activated yet.<br>Click the button below to activate it.', 'login-as-user-pro').'</div>';
				}
				
				elseif (isset($resp->code) && ($resp->code == 'error' && !empty($resp->message)))
				{
					$show_active_key_button = false;
					$html .= '<div style="margin: 20px 0;" id="w357-activated-successfully-msg" class="w357-alert w357-alert-danger">'.__($resp->message, 'login-as-user-pro').'</div>';
				}
				else
				{
					$html .= '<div style="margin: 20px 0;" id="w357-activated-successfully-msg" class="w357-alert w357-alert-danger">'.__('Call with Web357\'s License Manager has been failed. <br>Please, try again later or contact us at support@web357.com.', 'login-as-user-pro').'</div>';
				}
			}

			// show the button only if is not activated
			if ($show_active_key_button)
			{
				$html .= '<p class="web357_apikey_activation_html"></p>';
				$nonce = wp_create_nonce("web357_license_key_validation_nonce");
				$link = admin_url('admin-ajax.php?action=web357_license_key_validation&key='.$value.'&domain='.$domain.'&nonce='.$nonce);
				$html .= '<a class="button-secondary web357-activate-api-key-btn" data-domain="' . $domain . '" data-nonce="' . $nonce . '" data-key="' . $value . '" href="' . $link . '"><strong>'.__('Activate License Key', 'login-as-user-pro').'</strong></a>';
			}

			echo $html;
		} 
		?>
		</div>
		<?php 
	}
	

	function imageField($args) 
	{ 
		$options = get_option( 'login_as_user_options' );
		$name = $args['id'];
		$width = $args['width'];
		$height = $args['height'];
		$img_id = $args['img_id'];
		$default_image = '';

		// Set variables
		if ( !empty( $options[$name] ) ) {
			$image_attributes = wp_get_attachment_image_src( $options[$name], array( $width, $height ) );
			$src = $image_attributes[0];
			$value = $options[$name];
		} else {
			$src = $default_image;
			$value = '';
		}
		?>

		<div class="w357-imageField">

			<?php if (!empty($src)): ?>
					<img data-src="<?php echo esc_url($default_image); ?>" src="<?php echo esc_url($src); ?>" width="<?php echo absint($width); ?>px" height="<?php echo absint($height); ?>px" />		
			<?php else: ?>
				<img data-src="<?php echo esc_url($default_image); ?>" src="<?php echo esc_url($src); ?>" width="<?php echo absint($width); ?>px" height="<?php echo absint($height); ?>px" style="display:none" />		
			<?php endif; ?>

			<div>
				<input type="hidden" name="login_as_user_options[<?php echo $name; ?>]" id="login_as_user_options[<?php echo $name; ?>]" value="<?php echo esc_attr($value); ?>" />
				<button type="submit" class="upload_image_button button">Upload image</button>

				<?php if (!empty($src)): ?>
					<button type="submit" class="remove_image_button button">&times;</button>
				<?php else: ?>
					<button type="submit" class="remove_image_button button" style="display:none">&times;</button>
				<?php endif; ?>

			</div>
		</div>
		
		<?php
	}

	function hiddenField($args) 
	{ 
		$options = get_option('login_as_user_options');
		$default_value = (isset($args['default_value'])) ? $args['default_value'] : '';
		?>
		<input 
			type='hidden' 
			name='login_as_user_options[<?php echo esc_attr($args['name']); ?>]' 
			value='<?php echo esc_attr(isset($options[$args['name']]) ? $options[$args['name']] : $default_value); ?>'
			>
		<?php
	}

	function textareaWordpressEditorField($args) 
	{ 
		$options = get_option('login_as_user_options');
	    $editor_id = $args['name']; 
		$class = (isset($args['_class'])) ? $args['_class'] : '';
		$editor_settings = array('textarea_name' => 'login_as_user_options['.$args['name'].']', 'editor_class' => $class);
		$default_value = (isset($args['default_value'])) ? $args['default_value'] : '';
		$content = (isset($options[$args['name']])) ? $options[$args['name']] : $default_value;
		wp_editor( $content, $editor_id, $editor_settings );
	}

	function textareaField($args) 
	{ 
		$options = get_option('login_as_user_options');
		$class = (isset($args['_class'])) ? $args['_class'] : '';
		$default_value = (isset($args['default_value'])) ? $args['default_value'] : '';
		?>
		
		<textarea 
			id="<?php echo esc_attr($args['name']); ?>" 
			name="login_as_user_options[<?php echo esc_attr($args['name']); ?>]" 
			rows="<?php echo absint($args['rows']); ?>" 
			cols="<?php echo absint($args['cols']); ?>" 
			class="<?php echo esc_attr($class); ?>"
			placeholder="<?php echo esc_html__($args['placeholder']); ?>"><?php echo esc_textarea(isset($options[$args['name']]) && !empty($options[$args['name']]) ? $options[$args['name']] : $default_value); ?></textarea>
		<?php
	}

	function selectField($args)
	{ 
		$name = $args['id'];
		$default_value = $args['default_value'];
		$select_options = $args['options'];
		$options = get_option('login_as_user_options');
		$desc = (isset($args['desc'])) ? $args['desc'] : '';
		?>
		<select name="login_as_user_options[<?php echo $name; ?>]">

		<?php for ($i=0;$i<count($select_options);$i++): ?>

			<option value="<?php echo esc_attr($select_options[$i]['value']); ?>" <?php echo (($select_options[$i]['value'] == (isset($options[$name]) ? $options[$name] : $default_value) ) ? 'selected' : ''); ?>><?php echo $select_options[$i]['label']; ?></option>

		<?php endfor; ?>
		</select>
		<?php if (!empty($desc)): ?>
        <p class="description">
			<?php echo wp_kses( __( $desc, 'login-as-user-pro' ), array( 'strong' => array(), 'br' => array() ) ); ?>
		</p>
		<?php endif; ?>
		<?php
	}

	function radioField($args)
	{ 
		$name = $args['id'];
		$default_value = $args['default_value'];
		$radio_options = $args['options'];
		$field_description = (isset($args['field_description'])) ? $args['field_description'] : '';
		$options = get_option('login_as_user_options');

		for ($i=0;$i<count($radio_options);$i++): ?>

			<input 
				type='radio' 
				id='<?php echo $radio_options[$i]['id']; ?>' 
				name='login_as_user_options[<?php echo $name; ?>]' 
				value='<?php echo esc_attr($radio_options[$i]['value']); ?>'
				<?php if ( $radio_options[$i]['value'] == (isset($options[$name]) ? $options[$name] : $default_value) ) echo 'checked="checked"'; ?>
			>
			<label for="<?php echo $radio_options[$i]['id']; ?>" style="margin-right: 10px;"><?php echo $radio_options[$i]['label']; ?></label>

		<?php endfor; ?>

		<?php if (!empty($field_description)): ?>
			<div class="w357_settings_field_description"><?php echo $field_description; ?></div>
		<?php endif; ?>
		<?php
	}

	function checkboxField($args)
	{
		$name = $args['id'];
		$default_value = $args['default_value'];
		$ckeckbox_options = $args['options'];
		$field_description = (isset($args['field_description'])) ? $args['field_description'] : '';
		$options = get_option('login_as_user_options');

		for ($i=0;$i<count($ckeckbox_options);$i++):
		?>

			<input 
				type='checkbox' 
				id='<?php echo $ckeckbox_options[$i]['id']; ?>' 
				name='login_as_user_options[<?php echo $name; ?>][]' 
				value='<?php echo esc_attr($ckeckbox_options[$i]['value']); ?>'
				<?php if (in_array($ckeckbox_options[$i]['value'], (isset($options[$name]) ? $options[$name] : $default_value))) echo 'checked="checked"'; ?>
			>
			<label for="<?php echo $ckeckbox_options[$i]['id']; ?>" style="margin-right: 10px;"><?php echo $ckeckbox_options[$i]['label']; ?></label>

		<?php endfor; ?>

		<?php if (!empty($field_description)): ?>
			<div class="w357_settings_field_description"><?php echo $field_description; ?></div>
		<?php endif; ?>
		<?php
	}
}