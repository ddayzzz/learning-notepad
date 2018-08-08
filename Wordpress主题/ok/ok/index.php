<?php get_header(); ?>
<div class="container v-clearfix">
	<div class="posts">
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<?php get_template_part( 'content', get_post_format() ); ?>
			<?php endwhile; ?>
		<?php endif;?>

		<?php if ( is_home() ) { ?>
			<div class="loadmore"><?php ajax_show_more_button();?></div>
		<?php }else{ ?>
			<div class="navigation"><?php echo paginate_links(array(
				'prev_next'          => 0,
				'before_page_number' => '',
				'mid_size'           => 2,
				)); ?>
			</div>
		<?php } ?>
	</div>
	<?php get_sidebar(); ?>
</div>
<?php get_footer(); ?>