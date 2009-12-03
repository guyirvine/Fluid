<?php

class Fluid_Ui_List {
	static function build_row( $name, $offset, $title, $description, $id=null ) {
		$row = array( 'name' => $name,
						'offset' => $offset,
						'title' => $title,
						'description' => $description,
						'id' => $id );
		
		return $row;
	}


	static function draw_item( $row ) {
?>
		<div id='bd<?php print $row["id"]; ?>' class='head'>
			<div style='cursor: pointer;' onclick="showDescription( 'd<?php print $row['id'] ?>' )" class="title" id='backlog_name_<?php print $row['id'] ?>'><?php print $row['name'] ?>
				<?php
					if ( strlen( $row['title'] ) > 0 ) {
						print '<span style="display: inline" class="title"> - ' . trim( $row['title'] ) . '</span>';
					}
				?>
				<div class="detail">
					<?php print $row['offset'] ?>
				</div>
			</div>

			<div id="d<?php print $row['id'] ?>" class="body" style="padding-left: 20px;">
				<?php print $row['description']; ?>
			</div>
		</div>
<?php
	}

    static function draw( $list, $generate_id=true ) {
?>
	<div id='fluid-ui-list'>
        <?php
		$open_list = array();
		$count = 1;
		foreach( $list as $row ) {
			if ( $generate_id )
				$row['id'] = $count;

			if ( isset( $row['open'] ) ) {
				$open_list[] = $row['id'];
			}

			Fluid_Ui_List::draw_item( $row );
        		$count++;
        	} 
        ?>
<script>
	<?php foreach( $open_list as $index ) { ?>
		showDescription( "d<?php print $index ?>" );
	<?php } ?>
</script>
    </div>
<?php
    }
}
