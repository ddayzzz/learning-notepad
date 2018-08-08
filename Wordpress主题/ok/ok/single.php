<?php get_header(); ?>
<div class="container v-clearfix">
	<div class="posts">
		<?php while ( have_posts() ) : the_post(); ?>			
			<div>
				<h2 class="article-title"><?php the_title();?></h2>
				<div class="postMeta"><p><?php the_author(); ?>发布于
<?php echo get_the_date('Y年n月j日');?> - <?php echo get_the_category_list(" ");?>分类 - <?php if ( function_exists('get_the_views') ) echo get_the_views($post->ID); ?>次阅读 - <?php echo get_post($post->ID)->comment_count?>条评论<?php edit_post_link('编辑',' - '); ?></p></div>
			</div>
			<div class="article-content">
				<?php the_content();?>
			</div>
			<?php comments_template( '', true ); ?>
		<?php endwhile; ?>
	</div>
	<?php get_sidebar(); ?>
</div>
<?php get_footer(); ?>