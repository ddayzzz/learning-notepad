<article class="post">
	<h2 class="article-title">
		<a href="<?php the_permalink();?>"><?php the_title();?></a>
	</h2>
	<div class="postMeta">
		<p><?php echo get_the_date('Y年n月j日');?> - <?php if ( function_exists('get_the_views') ) echo get_the_views($post->ID); ?>次阅读 - <?php echo get_post($post->ID)->comment_count?>条评论<?php edit_post_link('编辑',' - '); ?>
</p>
	</div>
	<div class="article-content">
		<?php the_content('Read more'); ?>
	</div>
</article>