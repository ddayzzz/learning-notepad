<?php get_header(); ?>
<div class="container v-clearfix">
	<div class="page-side">
		<?php wp_nav_menu( array( 'theme_location' => 'primary', 'menu_class' => 'pagemenu','container'=>'ul' ) ); ?>
	</div>
	<div class="page-content">
		<?php while ( have_posts() ) : the_post(); ?>
			<h1 class="article-title">
				<?php the_title();?>
			</h1>
			<div class="article-content">
				<?php the_content();?>
			</div>
		<?php endwhile;?>
		<?php comments_template( '', true ); ?>
	</div>
</div>
<?php get_footer(); ?>