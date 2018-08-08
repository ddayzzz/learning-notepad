<?php if ( post_password_required() ) return;?>

<div id="comments" class="comments-area">
<meta content="UserComments:<?php echo number_format_i18n( get_comments_number() );?>" itemprop="interactionCount">
	<?php if ( have_comments() ) : ?>
		<h2 class="comments-title">
			<?php printf( _n( '1条评论', '%1$s条评论', get_comments_number(), 'aladdin' ),
				number_format_i18n( get_comments_number() ) );?>
		</h2>
		<div class="commentshow">
			<ol class="commentlist">
				<?php wp_list_comments( array( 'callback' => 'mooc_comment', 'style' => 'ol' ) ); ?>
			</ol>
			<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // are there comments to navigate through ?>
				<nav class="commentnav" data-fuck="<?php echo $post->ID?>"><?php paginate_comments_links('prev_next=0');?></nav>
			<?php endif; ?>
		</div>
		<?php if ( ! comments_open() && get_comments_number() ) : ?>
			<p class="nocomments"><?php _e( 'Comments are closed.', 'aladdin' ); ?></p>
		<?php endif; ?>
	<?php endif; ?>

	<?php if(comments_open()) : ?>
		<div id="respond" class="respond" role="form">
			<h3 id="reply-title" class="comments-title">添加评论<small class="cancel-reply"><?php cancel_comment_reply_link(); ?></small></h3>
			<?php if ( get_option('comment_registration') && !$user_ID ) : ?>
				<p><?php _e( 'You must be', 'aladdin' ); ?> <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?redirect_to=<?php echo urlencode(get_permalink()); ?>"><?php _e( 'logged in', 'aladdin' ); ?></a> <?php _e( 'to post a comment.', 'aladdin' ); ?></p>
			<?php else : ?>
				<form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform">
					<?php if ( $user_ID ) : ?>
						<p class="logged-in-as"><?php _e( 'Logged in as', 'aladdin' ); ?> <a href="<?php echo get_option('siteurl'); ?>/me/setting/"><?php echo $user_identity; ?></a> | <a href="<?php echo wp_logout_url(get_permalink()); ?>" title="Log out of this account"><?php _e( 'Log out', 'aladdin' ); ?></a></p>

					<?php else : ?>
						<p class="comment-form-author">
							
							<input id="author" type="text" aria-required="true" size="30" value="<?php echo $comment_author; ?>" placeholder="Name*" name="author"><label for="author">
								<?php _e( 'Name', 'aladdin' ); ?>
								<span class="required">*</span>
							</label>
						</p>
						<p class="comment-form-email">
							
							<input id="email" type="text" aria-required="true" size="30" value="<?php echo $comment_author_email; ?>" placeholder="E-mail*" name="email"><label for="email">
								<?php _e( 'Email', 'aladdin' ); ?>
								<span class="required">*</span>
							</label>
						</p>
						<p class="comment-form-url">
							
							<input id="url" type="text" size="30" value="<?php echo $comment_author_url; ?>" placeholder="Site Url" name="url"><label for="url"><?php _e( 'Website', 'aladdin' ); ?></label>
						</p>

					<?php endif; ?>

					<p class="comment-form-comment">					
						<textarea id="comment" aria-required="true" rows="8" cols="45" name="comment"></textarea>
					</p>
					<div class="smilies-wrap">
						<?php echo get_wpsmiliestrans(); ?>
					</div>
					<p class="form-submit">
						<input id="submit" type="submit" value="<?php _e( '发布', 'aladdin' ); ?>" name="submit">
					</p>
					
					<?php comment_id_fields(); ?>
					<?php do_action('comment_form', $post->ID); ?>
				</form>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>