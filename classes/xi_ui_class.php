<?php

	/*********************************
	* XIBLOX Generate Html Tag Class *
	*								 *
	* @class	xiblox_ui			 *
	* @package	XIBLOX/classes		 *
	* @author	itabix				 *
	*********************************/
	
	class xiblox_ui {
	
		public function __construct() {
			
		}
		
		// generate input tag
		public static function gen_input( $type, $name, $value='', $class='', $id='' ) {
			if ( ( $type == 'submit' ) || ( $type == 'button' ) ) 
				$name = '';
			else 
				$new_name = 'xiblox[' . $name . ']';
			$code = '
				<input type="' . $type . '" name="' . $new_name . '" value="' . $value . '" class="' . $name . '" id="' . $id . '" />
			';
			return $code;
		}
		
		// generate select tag
		public static function gen_select( $name, $options, $value='', $class='', $id='' ) {
			$code = '<select name="xiblox[' . $name . ']" class="' . $name . '" id="' . $id . '">';
			foreach ( $options as $key => $option ) {
				if ( $option == $value ) 
					$select_value = 'selected="selected"';
				else 
					$select_value = '';
				$code .= '<option value="' . $key . '" ' . $select_value . '>' . $option . '</option>';
			}
			$code .= '</select>';
			return $code;
		}
		
		// generate form tag
		public static function gen_form( $name, $innerContent, $class='', $id='' ) {
			$code = '<form name="xiblox[' . $name . ']" class="' . $name . '" id="' . $id . '">' . $innerContent . '</form>';
			return $code;
		}
		
		// generate style tag
		public function apply_style( $style ) {
			$code = '
				<style>
					' . $style . '
				</style>
			';
			return $code;
		}
		
		// generate label tag
		public static function gen_labeled_ctrl( $label, $control, $class='', $id='' ) {
			$code = '<p>
				<label>' . $label . '</label>' . $control . '
			</p>';
			return $code;
		}
		
		// generate p tag
		public static function gen_paragraph( $content, $class='', $id='' ) {
			$code = '<p class="' . $class . '", id="' . $id . '">' . $content . '</p>';
			return $code;
		}
		
		// genereate search form tag
		public static function gen_search_form( $fieldname, $class='', $id='' ) {
			$code ='
				<form class=\'' . $class . '\' id=\'' . $id . '\' action="<?php echo $_SERVER[\'REQUEST_URI\'] ?>" method="post">
					<input type="text" name="' . $fieldname . '" /><input type="submit" name="search" value="Search" />
				</form>
			';
		}
	}
?>