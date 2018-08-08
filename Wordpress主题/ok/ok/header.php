<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="initial-scale=1.0,user-scalable=no,minimal-ui" />
	<title><?php wp_title( '|', true, 'right' ); ?><?php bloginfo('name'); ?> - <?php bloginfo('description'); ?></title>
	<link rel="profile" href="http://gmpg.org/xfn/11" />
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
	<?php wp_head(); ?>
	<?php
		global $s, $post , $wp_query;
		$description = '';
		$blog_name = get_bloginfo('name');
		if ( is_singular() ) {
			$ID = $post->ID;
			$title = $post->post_title;
			$author = $post->post_author;
			$user_info = get_userdata($author);
			$post_author = $user_info->display_name;
			if (!get_post_meta($ID, "meta-description", true)) {$description = $title.' - 作者: '.$post_author.'，首发于'.$blog_name;}
			else {$description = get_post_meta($ID, "meta-description", true);}
		} elseif ( is_home () )	{ $description = '';
		} elseif ( is_tag() )	{ $description = single_tag_title('', false) . " - ". trim(strip_tags(tag_description()));
		} elseif ( is_category() )	{ $description = single_cat_title('', false) . " - ". trim(strip_tags(category_description()));
		} elseif ( is_archive() )	{ $description = $blog_name . "'" . trim( wp_title('', false) ) . "'";
		} elseif ( is_search() )	{ $description = $blog_name . ": '" . esc_html( $s, 1 ) . "' 的搜索结果";
		}  else { $description = $blog_name . "'" . trim( wp_title('', false) ) . "'";
		}
		$description = mb_substr( $description, 0, 220, 'utf-8' );
		echo "<meta name=\"description\" content=\"$description\">\n";
	?>
</head>

<body <?php body_class(); ?> >
<div class="site">
	<div class="metabar">
		<div class="header">
			<a href="<?php echo home_url(); ?>">
				<h1 class="site-logo"><?php bloginfo( 'name' ); ?></h1>
			</a>
			<p class="site-description"><?php bloginfo( 'description' ); ?></p>
			<?php 
				$temp = wp_nav_menu( array( 'theme_location' => 'primary', 'menu_class' => 'nav-menu', 'container' => 'ul', 'echo' => false ) );
				echo preg_replace('/[\n]+/i','',$temp );	// 如果相邻li之间有回车会导致有间隔
			?>
		</div>
	</div>