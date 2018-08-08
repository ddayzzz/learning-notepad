<div class="sidebar">
	<?php if(is_singular()){
		dynamic_sidebar( 'sidebar-2' );
	}else{
		dynamic_sidebar( 'sidebar-1' );
	}?>
</div>