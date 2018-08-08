<?php
/**
 * 主题初始化
 * 
 * @since puu 1.0.0
*/
function mooc_widgets_init() {
	register_sidebar( array(
		'name' => 'Main Sidebar',
		'id' => 'sidebar-1',
		'description' => '首页边栏',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );
	register_sidebar( array(
		'name' => 'Single Sidebar',
		'id' => 'sidebar-2',
		'description' => '内页边栏',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );
}
add_action( 'widgets_init', 'mooc_widgets_init' );

function mooc_setup() {
	register_nav_menu( 'primary', __( '基本菜单', 'Mooc' ) );
	add_filter('pre_option_link_manager_enabled','__return_true');
	add_editor_style();	
	remove_filter('the_content', 'wptexturize');
}
add_action( 'after_setup_theme', 'mooc_setup' );


/**
 * 引入样式文件及JS
 * 
 * @since puu 1.1.0
*/
function mooc_scripts_styles() {
	global $wp_styles;
	wp_enqueue_style( 'style', get_stylesheet_uri() );
	wp_enqueue_script( 'ajax-comment', get_template_directory_uri() . '/mooc.js', array( 'jquery' ), '20150620', true );
	wp_localize_script( 'ajax-comment', 'ajaxcomment', array(
		'ajax_url' => admin_url('admin-ajax.php')
	));
}
add_action( 'wp_enqueue_scripts', 'mooc_scripts_styles' );


/**
 * 浏览次数统计功能
 * 
 * @since puu 1.0.0
*/
function set_post_views() {
	global $post;
	$post_id = intval($post->ID);
	$count_key = 'views';
	$views = get_post_custom($post_id);
	$views = intval($views['views'][0]);
	if (!is_user_logged_in()) {
		if (is_single() || is_page()) {
			if(!update_post_meta($post_id, 'views', ($views + 1))) {
				add_post_meta($post_id, 'views', 1, true);
			}
		}
	}
}
add_action('get_header', 'set_post_views');
function get_the_views($post_id) {
	$count_key = 'views';
	$views = get_post_custom($post_id);
	$views = intval($views['views'][0]);
	$post_views = intval(post_custom('views'));
	if ($views == '') {
		return '0';
	} else {
		return $views;
	}
}


/**
 * 图片表情支持（非emoji）
 * 
 * @since zhi 1.2.5
*/
function mooc_smilies_reset() {
	global $wpsmiliestrans;
	// 如已禁用字符转义则不进行以下过程
	if ( !get_option( 'use_smilies' ) )
		return;
	if ( !isset( $wpsmiliestrans ) ) {
		$wpsmiliestrans = array(
			':mrgreen:' => 'icon_mrgreen.png',
			':neutral:' => 'icon_neutral.png',
			':twisted:' => 'icon_twisted.png',
			':arrow:' => 'icon_arrow.png',
			':shock:' => 'icon_eek.png',
			':smile:' => 'icon_smile.png',
			':???:' => 'icon_confused.png',
			':cool:' => 'icon_cool.png',
			':evil:' => 'icon_evil.png',
			':grin:' => 'icon_biggrin.png',
			':idea:' => 'icon_idea.png',
			':oops:' => 'icon_redface.png',
			':razz:' => 'icon_razz.png',
			':roll:' => 'icon_rolleyes.png',
			':wink:' => 'icon_wink.png',
			':cry:' => 'icon_cry.png',
			':eek:' => 'icon_surprised.png',
			':lol:' => 'icon_lol.png',
			':mad:' => 'icon_mad.png',
			':sad:' => 'icon_sad.png',
			'8-)' => 'icon_cool.png',
			'8-O' => 'icon_eek.png',
			':-(' => 'icon_sad.png',
			':-)' => 'icon_smile.png',
			':-?' => 'icon_confused.png',
			':-D' => 'icon_biggrin.png',
			':-P' => 'icon_razz.png',
			':-o' => 'icon_surprised.png',
			':-x' => 'icon_mad.png',
			':-|' => 'icon_neutral.png',
			';-)' => 'icon_wink.png',
			// This one transformation breaks regular text with frequency.
			'8O' => 'icon_eek.png',
			':(' => 'icon_sad.png',
			':)' => 'icon_smile.png',
			':?' => 'icon_confused.png',
			':D' => 'icon_biggrin.png',
			':P' => 'icon_razz.png',
			':o' => 'icon_surprised.png',
			':x' => 'icon_mad.png',
			':|' => 'icon_neutral.png',
			';)' => 'icon_wink.png',
			':!:' => 'icon_exclaim.png',
			':?:' => 'icon_question.png',
		);
	}
}
mooc_smilies_reset();	// 恢复emoji表情为图片
add_filter('smilies_src','mooc_smilies_src',1,10);
function mooc_smilies_src ($img_src, $img, $siteurl){
	return get_template_directory_uri().'/smilies/'.$img;
}
function get_wpsmiliestrans(){
	global $wpsmiliestrans;
	$wpsmilies = array_unique($wpsmiliestrans);
	foreach($wpsmilies as $alt => $src_path){
		$output .= '<a class="add-smily" data-smilies="'.$alt.'"><img class="wp-smiley" src="'.get_template_directory_uri() .'/smilies/'.$src_path.'" /></a>';
	}
	return $output;	// 输出所有表情
}


/**
 * Ajax评论提交
 * 
 * @since puu 1.1.0
 *
 * @require Wordpress 4.4
 * @updated by bigfa
*/
function fa_ajax_comment_scripts(){
    wp_localize_script( 'ajax-comment', 'ajaxcomment', array(
        'ajax_url'   => admin_url('admin-ajax.php'),
        'order' => get_option('comment_order'),
        'formpostion' => 'bottom', // 默认为bottom，如果你的表单在顶部则设置为top
    ) );
}
function fa_ajax_comment_err($a) {
    header('HTTP/1.0 500 Internal Server Error');
    header('Content-Type: text/plain;charset=UTF-8');
    echo $a;
    exit;
}
function fa_ajax_comment_callback(){
    $comment = wp_handle_comment_submission( wp_unslash( $_POST ) );
    if ( is_wp_error( $comment ) ) {
        $data = $comment->get_error_data();
        if ( ! empty( $data ) ) {
        	fa_ajax_comment_err($comment->get_error_message());
        } else {
            exit;
        }
    }
    $user = wp_get_current_user();
    do_action('set_comment_cookies', $comment, $user);
    $GLOBALS['comment'] = $comment;
    // 主题评论结构
    ?>
    <li <?php comment_class(); ?> <?php if( $depth > 2){ echo ' style="margin-left:-50px;"';} ?> id="li-comment-<?php comment_ID() ?>" itemscope="" itemprop="comment">
    <div class="comment-block">
        <div class="comment-info v-clearfix">
            <?php echo get_avatar( $comment, $size = '56');?>
            <div class="comment-meta">
                <div class="authorname" itemprop="author">
                <?php 
                    $url    = get_comment_author_url();
                    $author = get_comment_author();
                    if (empty( $url ))  echo $author;
                    else
                    echo '<a target="_blank" href="'.$url.'" rel="external nofollow" class="url">'.$author.'</a>';?>
                </div>
                <div class="publish-date" itemprop="datePublished"><?php echo get_comment_date('Y年n月j日'); ?></div>
            </div>
        </div>
        <div class="comment-content" itemprop="description"><?php comment_text(); ?></div>
    </div>
    </li>
    <?php die();
}
add_action('wp_enqueue_scripts', 'fa_ajax_comment_scripts' );
add_action('wp_ajax_nopriv_ajax_comment', 'fa_ajax_comment_callback');
add_action('wp_ajax_ajax_comment', 'fa_ajax_comment_callback');


/**
 * 评论回调
 * 
 * @since ok 1.0.2
*/
function mooc_comment($comment, $args, $depth) {
    $GLOBALS['comment'] = $comment;
    switch ( $comment->comment_type ) :
        case 'pingback' :
        case 'trackback' :
            ?>
            <li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
            <p><?php _e( 'Pingback:', 'mooc_comment' ); ?> <?php comment_author_link(); ?> </p>
            <?php
            break;
        default :
            global $post;
            ?>
                <li <?php comment_class(); ?> <?php if( $depth > 3){$d=50;$n=$depth-3; echo ' style="margin-left:-'.$d*$n.'px;"';} ?> id="li-comment-<?php comment_ID() ?>" itemscope="" itemprop="comment">
                <div class="comment-block">
                    <div class="comment-info v-clearfix">
                        <?php echo get_avatar( $comment, $size = '56');?>
                        <div class="comment-meta">
                            <div class="authorname" itemprop="author">
                            <?php 
                                $url    = get_comment_author_url();
                                $author = get_comment_author();
                                if (empty( $url ))    echo $author;
                                else
                                echo '<a target="_blank" href="'.$url.'" rel="external nofollow" class="url">'.$author.'</a>';
                            ?></div>
                            <div class="publish-date" itemprop="datePublished"><?php echo get_comment_date('Y年n月j日'); ?></div>
                        </div>
                    </div>
                    <div class="comment-content" itemprop="description">
                        <?php comment_text(); ?>
                    </div>
                    <div class="reply"><?php comment_reply_link(array_merge( $args, array('reply_text' => '回复','depth' => $depth, 'max_depth' => $args['max_depth']))) ?></div>
                </div>
                </li>
            <?php
            break;
    endswitch;
}


/**
 * 评论邮件回复（SMTP & wp_mail）
 * 
 * @since puu 1.3.0
*/
add_action('phpmailer_init', 'mail_smtp');
function mail_smtp( $phpmailer ) {
	$phpmailer->IsSMTP();
	$phpmailer->SMTPAuth = true;
	$phpmailer->Port = get_option('mooc_smtp_port');
	$phpmailer->SMTPSecure ="ssl";
	$phpmailer->Host = get_option('mooc_smtp_host');
	$phpmailer->Username = get_option('mooc_smtp_username');
	$phpmailer->Password = get_option('mooc_smtp_password');
}
function comment_mail_notify($comment_id) {
	if (get_option('mooc_send_notify_mail') != 'checked') return;
	$comment = get_comment($comment_id);
	$parent_id = $comment->comment_parent ? $comment->comment_parent : '';
	$spam_confirmed = $comment->comment_approved;
	if (($parent_id != '') && ($spam_confirmed != 'spam')) {
		// $wp_email = 'no-reply@' . preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME']));
		$wp_email = get_option('mooc_smtp_username');
		$to = trim(get_comment($parent_id)->comment_author_email);
		$subject = '您在[' . get_option('blogname') . ']的留言有了新回复';
		$message = '
        	<div align="center" style="background-color: #eee; color: #777; padding:66px 0px;">
        	<div style="text-align: left; background-color: #fff; width: 700px">
		<h2 style="  text-align: center;padding-top: 40px;border-top: #777 solid 3px;">'.$subject.'</h2>
		<div style="margin: 0px 60px 30px;  padding: 30px 0;border-top: #777 solid 1px;border-bottom: #ddd dashed 2px;">
		<p><strong>' . trim(get_comment($parent_id)->comment_author) . '，你好!</strong></p>
		<p class="message"><strong>您曾在《' . get_the_title($comment->comment_post_ID) . '》留言：</strong><br />'
		. trim(get_comment($parent_id)->comment_content) . '</p>
		<p><strong>' . trim($comment->comment_author) . ' 给你的回复是：</strong><br />'
		. trim($comment->comment_content) . '<br /></p>
		<p>你可以点击此链接 <a href="' . htmlspecialchars(get_comment_link($parent_id)) . '">查看完整内容</a> | 欢迎再次来访<a href="' . home_url() . '">' . get_option('blogname') . '</a></p></div>
		<div style="padding-bottom: 30px;  margin: 0px auto;  width: 580px;">
		<p style="color: #aaa; font-size: 12px">©' . get_option('blogname') . ' 2015</p>
		<p style="color: #aaa; font-size: 12px">' . get_bloginfo('description') .'</p>
		</div></div></div>';
		$from = "From: \"" . get_option('blogname') . "\" <$wp_email>";
		$headers = "$from\nContent-Type: text/html; charset=" . get_option('blog_charset') . "\n";
		wp_mail( $to, $subject, $message, $headers );
	}
}
add_action('comment_post', 'comment_mail_notify');


/**
 * Ajax文章无限加载（通过按钮）
 *
 * @since puu 1.1.5
 */
add_action('wp_ajax_nopriv_ajax_index_post', 'ajax_index_post');
add_action('wp_ajax_ajax_index_post', 'ajax_index_post');
function ajax_index_post(){
	$paged = $_POST["paged"];
	$total = $_POST["total"];
	$category = $_POST["category"];
	$author = $_POST["author"];
	$tag = $_POST["tag"];
	$search = $_POST["search"];
	$the_query = new WP_Query( array("posts_per_page"=>get_option('posts_per_page'),"cat"=>$category,"tag"=>$tag,"author"=>$author,"post_status"=>"publish","post_type"=>"post","paged"=>$paged,"s"=>$search) );
	while ( $the_query->have_posts() ){
		$the_query->the_post();
		get_template_part( 'content', get_post_format() );
	}
	wp_reset_postdata();
	$nav = '';
	if($category) $cat_id = ' data-cate="'.$category.'"';
	if($author) $author = ' data-author="'.$author.'"';
	if($tag) $tag = ' data-tag="'.$tag.'"';
	if($search) $search = ' data-search="'.$search.'"';
	if ( $total > $paged ) $nav = '<div class="loadmore"><a id="show-more"'.$cat_id.$author.$search.' data-total="'.$total.'" data-paged = "'.($paged + 1).'" class="show-more m-feed--loader"></a></div>';
	echo $nav;
	die;
}
function ajax_show_more_button(){
	global $wp_query;
	if( 2 > $GLOBALS["wp_query"]->max_num_pages){
		return;
	}
	if(is_category()) $cat_id = ' data-cate="'.get_query_var( 'cat' ).'"';	
	if(is_author()) $author = ' data-author="'.get_query_var('author').'"';
	if(is_tag()) $tag = ' data-tag="'.get_query_var('tag').'"';
	if(is_search()) $search = ' data-search="'.get_query_var('s').'"';
	echo '<a id="show-more"'.$cat_id.' data-paged = "2"'.$author.$tag.$search.' data-total="'.$GLOBALS["wp_query"]->max_num_pages.'" class="show-more m-feed--loader"></a>';
}


/**
 * 短代码
 * 
 * [progress width="50%"]
 *
 * @since puu 1.1.5
*/
function progressbar($atts) {
	extract(shortcode_atts(array(
	"width" => '50%',
	), $atts));
	return '<div class="progress-bar"><span style="width: '.$width.'"></span></div>';
}
add_shortcode('progress', 'progressbar');


/**
 * 文章编辑页面添加自定义按钮
 * 
 * @since puu 1.2.5
*/
add_action('media_buttons_context', 'smilies_custom_button',10);
function smilies_custom_button($context) {
	$context .= '<style>.smilies-wrap{background:#fff;border: 1px solid #ccc;box-shadow: 2px 2px 3px rgba(0, 0, 0, 0.24);padding: 10px;width:400px;position:absolute;display:none;}.smilies-wrap img{height:24px;width:24px;cursor:pointer;margin-bottom:5px} .is-active.smilies-wrap{display:block}</style>
	<button type="button" id="insert-media-button" style="position:relative;" class="button insert-smilies add_smilies" title="添加表情" data-editor="content" href="javascript:;"><span style="vertical-align:text-top;width:18px;height:18px;margin:-3px 2px;" class="dashicons dashicons-smiley"></span>添加表情</button>
	<div class="smilies-wrap">'.get_wpsmiliestrans().'</div>
	<script>jQuery(document).ready(function(){jQuery(document).on("click", ".insert-smilies",function() { if(jQuery(".smilies-wrap").hasClass("is-active")){jQuery(".smilies-wrap").removeClass("is-active");}else{jQuery(".smilies-wrap").addClass("is-active");}});jQuery(document).on("click", ".add-smily",function() { send_to_editor(" " + jQuery(this).data("smilies") + " ");jQuery(".smilies-wrap").removeClass("is-active");return false;});});</script>';
	return $context;
}

add_action('media_buttons_context', 'shortcodes_custom_button',11);
function shortcodes_custom_button($context) {
	$context .= '<style>.shortcodes-wrap{background:#fff;border: 1px solid #ccc;box-shadow: 2px 2px 3px rgba(0, 0, 0, 0.24);padding: 10px;width:400px;position:absolute;display:none;}.shortcodes-wrap img{height:24px;width:24px;cursor:pointer;margin-bottom:5px} .is-active.shortcodes-wrap{display:block}</style>
	<button type="button" id="insert-media-button" style="position:relative;" class="button insert-shortcodes add_shortcodes" title="短代码" data-editor="content" href="javascript:;"><span style="vertical-align:text-top;width:18px;height:18px;margin:-3px 2px;" class="dashicons dashicons-admin-plugins"></span>短代码</button>
	<div class="shortcodes-wrap">
		<a class="add-sc" data-shortcodes="[progress width=&quot;50%&quot;]"><img class="wp-smiley" src="'.get_template_directory_uri() .'/shortcodes/progressbar.png" style="width:30px;height:12px;" /></a>
	</div>
	<script>jQuery(document).ready(function(){jQuery(document).on("click", ".insert-shortcodes",function() { if(jQuery(".shortcodes-wrap").hasClass("is-active")){jQuery(".shortcodes-wrap").removeClass("is-active");}else{jQuery(".shortcodes-wrap").addClass("is-active");}});jQuery(document).on("click", ".add-sc",function() { send_to_editor(" " + jQuery(this).data("shortcodes") + " ");jQuery(".shortcodes-wrap").removeClass("is-active");return false;});});</script>';
	return $context;
}


/**
 * 自定义侧栏小工具
 * 
 * 热门文章、热评文章
 *
 * @since puu 1.3.0
*/
class widget_mostview extends WP_Widget
{
	function widget_mostview(){   
		$widget_options = array('classname'=>'set_contact','description'=>'显示近期热门文章');   
		$this->WP_Widget(false,'近期热门(朴)',$widget_options);   
	}
	function widget($args, $instance) {
		extract($args);
		echo $before_widget;
		echo $before_title.'近期热门'.$after_title;
		?>
		<ul>
			<?php
				$ode = '';
				$limit = 10;	// 显示文章数
				$days = 80;	// 时间限制
				global $wpdb, $post;
				$output = '';
				// database query
				$most_viewed = $wpdb->get_results("SELECT ID, post_date, post_title, (meta_value+0) AS views FROM $wpdb->posts LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id) $inr_join WHERE post_status = 'publish' AND post_password = ''  AND post_type='post' AND TO_DAYS(now()) - TO_DAYS(post_date) < $days AND meta_key = 'views' GROUP BY ID ORDER BY views DESC LIMIT $limit");
				if ($most_viewed) {
					foreach ($most_viewed as $viewed) {
						$post_ID    = $viewed->ID;
						$post_views = number_format($viewed->views);
						$post_title = esc_attr($viewed->post_title);
						$get_permalink = esc_attr(get_permalink($post_ID));
						$output .= "<li><a href= \"".$get_permalink."\" rel=\"bookmark\" title=\"".$post_title."\">".$post_title."</a> (".$post_views.")</li>";
					}
				} else {
					$output = "无数据";
				}
				echo $output;
			?>
		</ul>
		<?php echo $after_widget;
	}
}
add_action('widgets_init',create_function('', 'return register_widget("widget_mostview");'));

class widget_mostcomments extends WP_Widget
{
	function widget_mostcomments(){   
		$widget_options = array('classname'=>'set_contact','description'=>'显示近期热评文章');   
		$this->WP_Widget(false,'热评文章(朴)',$widget_options);   
	}
function widget($args, $instance) {
		extract($args);
		echo $before_widget;
		echo $before_title.'热评文章'.$after_title;
		?>
		<ul>
			<?php
				$posts_num=8;	// 显示文章数
				$days=80;	// 时间限制
				global $wpdb;
				$sql = "SELECT ID , post_title , comment_count FROM $wpdb->posts WHERE post_type = 'post' AND post_status = 'publish' AND post_password = '' AND TO_DAYS(now()) - TO_DAYS(post_date) < $days ORDER BY comment_count DESC LIMIT 0 , $posts_num ";
				$posts = $wpdb->get_results($sql);
				$output = "";
				foreach ($posts as $post){
					$output .= "\n<li><a href= \"".get_permalink($post->ID)."\" rel=\"bookmark\" title=\"".$post->post_title."\">".$post->post_title."</a> (".$post->comment_count.")</li>";
				}
				echo $output;
			?>
		</ul>
		<?php echo $after_widget;
	}
}   
add_action('widgets_init',create_function('', 'return register_widget("widget_mostcomments");'));


/**
 * 添加主题设置页面
 * 
 * @since puu 1.3.0
*/
if ( $_POST['update_themeoptions'] == 'true' ) { themeoptions_update(); }
function themeoptions_page() {
?>
<div class="wrap">
    <div id="icon-themes" class="icon32"><br /></div>
    <h1>主题设置</h1>
    <form method="POST" action="">
        <input type="hidden" name="update_themeoptions" value="true" />
	<h2>个性设置</h2>
        <p><input type="text" name="copyright" id="copyright" size="32" value="<?php echo get_option('mooc_copyright'); ?>"/> 底部版权信息</p>
        <h2>SMTP设置</h2>
        <h4><input type="checkbox" name="send_notify_mail" id="send_notify_mail" <?php echo get_option('mooc_send_notify_mail'); ?> /> 回复评论后发送通知邮件</h4>
        <p><input type="text" name="smtp_host" id="smtp_host" size="32" value="<?php echo get_option('mooc_smtp_host'); ?>"/>* Host (例如smtp.qq.com)</p>
        <p><input type="text" name="smtp_port" id="smtp_port" size="32" value="<?php echo get_option('mooc_smtp_port'); ?>"/>* Port (例如465)</p>
        <p><input type="text" name="smtp_username" id="smtp_username" size="32" value="<?php echo get_option('mooc_smtp_username'); ?>"/>* 邮箱账户 (例如88888@qq.com)</p>
        <p><input type="password" name="smtp_password" id="smtp_password" size="32" value="<?php echo get_option('mooc_smtp_password'); ?>"/>* 邮箱密码</p>
        <p><input type="submit" class="button-primary" name="bcn_admin_options" value="更新数据"/></p>
    </form>
</div>
<?php
}
function themeoptions_update() {
    // 数据更新验证
    update_option('mooc_copyright', $_POST['copyright']);
    update_option('mooc_smtp_host', $_POST['smtp_host']);
    update_option('mooc_smtp_port', $_POST['smtp_port']);
    update_option('mooc_smtp_username', $_POST['smtp_username']);
    update_option('mooc_smtp_password', $_POST['smtp_password']);
    if ($_POST['send_notify_mail']=='on') { $display = 'checked'; } else { $display = ''; }
    update_option('mooc_send_notify_mail', $display);
}
function themeoptions_admin_menu() {
	add_theme_page("主题设置", "主题选项", 'edit_themes', basename(__FILE__), 'themeoptions_page');
}
add_action('admin_menu', 'themeoptions_admin_menu');


/**
 * 生成文章归档
 * 
 * @since ok 1.0.2
*/
function get_archive_by_category($hide_empty = false){
    $output = '<div class="archive-category-groups">';
    $cateargs = array(
        'hide_empty' => $hide_empty
    );
    $categories = get_categories($cateargs);
    foreach($categories as $category) {
        $output .= '<div class="archive-category-group"><h3 class="archive-category-title"><a href="' . get_category_link( $category->term_id ) . '" title="' . $category->name . '" ' . '>' . $category->name.'</a> </h3><p class="archive-category-description">'. $category->description . '</p><div class="archive-category-postcount v-textAlignCenter">'. $category->count . '</div>';
        $args = array(
            'category' => $category->term_id,
            'numberposts' => -1
        );
        $output .= '<div class="archive-category-posts">';
        $posts = get_posts($args);
        foreach($posts as $post){
            $output .= '<div class="archive-category-post"><a class="archive-category-post-title" href="'.get_permalink($post->ID).'">'.$post->post_title.'</a><time class="archive-category-post-time">'.human_time_diff(strtotime($post->post_date_gmt),time()).'前 </time></div>';
        }
        $output .= '</div></div>';
    }
    $output .= '</div>';
    return $output;
}


/**
 * 内容链接自动 no-follow
 * 
 * @since puu 1.0.0
*/
function content_nofollow($content) {
	preg_match_all('/href="(.*?)"/',$content,$matches);
	if($matches){
		foreach($matches[1] as $val){
		if( strpos($val,home_url())===false )
			$content=str_replace("href=\"$val\"","href=\"$val\" rel=\"external nofollow\" ",$content);  
		}
	}
return $content;
}
add_filter('the_content','content_nofollow',999);


/**
 * 解决 Gravatar 被墙
 * 
 * @since puu 1.0.0
*/
function get_ssl_avatar($avatar) {
	$avatar = str_replace(array("www.gravatar.com", "0.gravatar.com", "1.gravatar.com", "2.gravatar.com"), "cn.gravatar.com", $avatar);
	return $avatar;
}
add_filter('get_avatar', 'get_ssl_avatar');


/**
 * 移除js/css后的wordpress版本号
 * 
 * @since puu 1.0.0
*/
function mooc_remove_cssjs_ver( $src ) {
	if( strpos( $src, 'ver=' ) )
		$src = remove_query_arg( 'ver', $src );
	return $src;
}
add_filter( 'style_loader_src', 'mooc_remove_cssjs_ver', 999 );
add_filter( 'script_loader_src', 'mooc_remove_cssjs_ver', 999 );


/**
 * 移除不必要的功能
 * 
 * @since puu 1.1.0
*/
function remove_wp_open_sans() {
	wp_deregister_style( 'open-sans' );
	wp_register_style( 'open-sans', false );
}
add_action('wp_enqueue_scripts', 'remove_wp_open_sans');
function remove_unnecessary_funcs() {
	// 移除谷歌字体
	wp_deregister_style( 'open-sans' );
	wp_register_style( 'open-sans', false );
	wp_enqueue_style('open-sans','');

	// 禁用emoji
	remove_action('wp_head', 'print_emoji_detection_script', 7);
	remove_action('admin_print_scripts', 'print_emoji_detection_script');
	remove_action('wp_print_styles', 'print_emoji_styles');
	remove_action('admin_print_styles', 'print_emoji_styles');
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );  
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );


	// 清理header
	add_filter('pre_option_link_manager_enabled','__return_true');
	remove_filter( 'the_content', 'wptexturize');
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 );
	remove_action( 'wp_head', 'start_post_rel_link', 10, 0 );
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
	remove_action( 'wp_head', 'feed_links_extra', 3 );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'locale_stylesheet' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head', 10, 0 );
}
add_action( 'init', 'remove_unnecessary_funcs' );

?>