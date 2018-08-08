<?php get_header(); ?>
<div class="container v-clearfix">
	<div class="posts">
		<div style="margin-bottom:20px;">
			<h1 class="article-title"><?php echo get_search_query();?></h1>
			<div><?php echo get_search_query().'的搜索结果'; ?></div>
		</div>
		<?php if ( have_posts() ) : ?>	
			<?php while ( have_posts() ) : the_post(); ?>
				<?php get_template_part( 'content', 'archive' ); ?>
			<?php endwhile; ?>

			<div class="navigation"><?php echo paginate_links(array(
				'prev_next'          => 0,
				'before_page_number' => '',
				'mid_size'           => 2,
				)); ?>
			</div>
		<?php else: ?>
			<p>无结果</p>
		<?php endif; ?>
	</div>
	<?php get_sidebar(); ?>
</div>
<?php get_footer(); ?>