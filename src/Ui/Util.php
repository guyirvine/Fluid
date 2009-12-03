<?php
class Fluid_Ui_Util {

	static function select( $name, $list, $selected_id, $size='1', $id='' ) {
		$id = empty( $id ) ? $name : $id;

		print "<select name='$name' size='$size' id='$id'>\n";
		foreach( $list as $option ) {
			$selected = ( $option['id'] == $selected_id ) ? " selected" : "";
			print "<option $selected value='" . $option['id'] . "'>" . $option['name'] . "</option>\n";
		}
		print "</select>\n";

	}

	static function radio( $name, $list, $selected_id, $size='1', $id='' ) {
		foreach( $list as $option ) {
			$id = $name . '-' . $option['id'];
			$checked = ( $option['id'] == $selected_id ) ? " checked='checked'" : "";
			print "<input type='radio' name='$name' id='$id' value='" . $option['id'] . "' $checked> <label for='$id'>" . $option['name'] . "</label><br>\n";
		}

		return $buffer;
	}


	static function checkbox_list( $name, $list, $_selected_list, $size='10', $id='' ) {
		$selected_list = array();
		foreach( $_selected_list as $item )
			$selected_list[$item['id']] = $item;
	
		$_id = $name;
		$name .= '[]';
?>
<div>
	<div class='ui-util-checkbox-list'>
		<table id='<?php print $id ?>-table' class='ui-util-checkbox-table'>
			<?php
				foreach( $list as $option ) {
					$id = $_id . '-' . $option['id'];
					$checked = isset( $selected_list[$option['id']] ) ? " checked='checked'" : "";
					$option_id = $option['id'];
					$option_name = $option['name'];
					?>
						<tr class='filter-tr'>
							<td>
								<label for='<?php print $id ?>' class='filter-entry'><?php print $option_name ?></label>
							</td>
							<td>
								<input type='checkbox' name='<?php print $name ?>' id='<?php print $id ?>' value='<?php print $option_id ?>' <?php print $checked ?>>
							</td>
						</tr>
					<?php 
				}
			?>        
		</table>
	</div>
</div>

<?php
	}

}
