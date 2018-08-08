	<div id="footer-widgets">
		<div class="v-clearfix" id="footer-widgets-wrapper">
		<?php dynamic_sidebar('Footer') ?>
		</div>
	</div>
	<a id="gotop" target="_self"></a>
	<div class="footer">
		<div class="section">
			<p>Powered by WordPress, Theme <a href="http://moooc.cc/archives/641" target="_blank">ok</a>.</p>
			<p><?php echo get_option('mooc_copyright')?></p>
		</div>
	</div>
</div>
<?php wp_footer(); ?>
</body>
</html>