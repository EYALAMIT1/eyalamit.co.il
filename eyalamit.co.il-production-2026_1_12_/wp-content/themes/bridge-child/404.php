<?php global $qode_options_proya; ?>
<?php get_header(); ?>

			<?php get_template_part( 'title' ); ?>
			<div class="container">
                <?php if(isset($qode_options_proya['overlapping_content']) && $qode_options_proya['overlapping_content'] == 'yes') {?>
                    <div class="overlapping_content"><div class="overlapping_content_inner">
                <?php } ?>
				<div class="container_inner default_template_holder container_page_not_found">
					<div class="page_not_found">
						<h2><?php if($qode_options_proya['404_subtitle'] != ""): echo $qode_options_proya['404_subtitle']; else: ?> <?php _e('The page you are looking for is not found', 'qode'); ?> <?php endif;?></h2>
                        <p><?php if($qode_options_proya['404_text'] != ""): echo $qode_options_proya['404_text']; else: ?> <?php _e('The page you are looking for does not exist. It may have been moved, or removed altogether. Perhaps you can return back to the site’s homepage and see if you can find what you are looking for.', 'qode'); ?> <?php endif;?></p>
						<div class="separator  transparent center  " style="margin-top:15px;"></div>
						<p><a itemprop="url" class="qbutton with-shadow" href="<?php echo home_url(); ?>/"><?php if($qode_options_proya['404_backlabel'] != ""): echo $qode_options_proya['404_backlabel']; else: ?> <?php _e('Back to homepage', 'qode'); ?> <?php endif;?></a></p>
					
					</div>
					<div class="image_page_not_found">
						<img src="https://www.eyalamit.co.il/wp-content/uploads/2016/12/18952766_10154894677583425_4941419476651561210_n.jpg" width="500" class="alignleft size-full" />	</div>
					</div>
				</div>
                <?php if(isset($qode_options_proya['overlapping_content']) && $qode_options_proya['overlapping_content'] == 'yes') {?>
                    </div></div>
                <?php } ?>
	
<?php get_footer(); ?>
