<?php
/**
 * The Sidebar containing the main widget area.
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */

$options = twentyeleven_get_theme_options();
$current_layout = $options['theme_layout'];

if ( 'content' != $current_layout ) :
?>
		<div id="secondary" class="widget-area" role="complementary">
			<aside id="title" class="widget">
				<div id="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><img src="<?php bloginfo('template_directory'); ?>/images/jcast.png" /></a></div>
				<h2 id="site-description"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'description' ); ?></a></h2>
				 <?php get_search_form(); ?>
			</aside>
			<aside id="recent" class="widget">
			<div class="cat-grid">
			    <?php $count = 1; ?>
			    <?php if ( have_posts() ) : ?>
			        <?php query_posts($query_string . '&posts_per_page=5'); ?>
				<?php while ( have_posts() ) : the_post(); ?>
				    <?php $size = ($count==1) ? 'big-sidebar' : 'small-sidebar'; ?>
				    <?php $categories = get_the_category(); ?>
                		    <div class="view <?php echo $size; ?> recent-<?php echo $categories[0]->slug; ?>">
					<?php $thumb = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), $size ); ?>
					<img src="<?php echo $thumb['0']; ?>" />
                    			<div class="mask">
					    <?php $subject = get_post_meta($post->ID, 'subject'); ?>
                        		    <a href="<?php the_permalink(); ?>"><h2><?php the_title(); ?> <?php echo $subject[0]; ?></h2></a>
			                </div>
			            </div>
				    <?php $count++; ?>
				<?php endwhile; ?>
			    <?php endif; ?>
			</div>
			</aside>
			<aside id="series" class="widget">
				<div class="series-grid">
					<ul class="series"> 
						<?php wp_list_categories('child_of=2510&title_li='); ?>
					</ul>
				</div>
			</aside>
			<aside id="categories" class="widget">
				<div class="cat-grid">
					<ul class="categories"> 
						<?php wp_list_categories('child_of=13&title_li='); ?>
					</ul>
				</div>
			</aside>
			<!-- <aside id="pilots" class="widget">
				<div class="pilot-grid">
					<ul class="pilots"> 
						<?php wp_list_categories('child_of=2514&title_li='); ?>
					</ul>
				</div>
			</aside> -->
				<aside class="widget">
					<div class="links-grid">
						<ul class="links"> 
							<li><a title="Twitter do JCast" href="http://twitter.com/JCastBlog" target="_blank">@jcastblog</a></li>
							<li><a title="Twitter do JCast" href="http://twitter.com/darkonix" target="_blank">@darkonix</a></li>
							<li><a title="Twitter do JCast" href="http://twitter.com/Laivindil" target="_blank">@laivindil</a></li>
							<li><a title="Facebook" href="https://www.facebook.com/pages/JCast/138697306198739" target="_blank">Facebook</a></li>
							<li><a title="RSS" href="http://jcast.com.br/?feed=podcast" target="_blank">RSS</a></li>
							<li><a title="Email" href="mailto:darkonix@gmail.com" target="_blank">Email</a></li>
						</ul>
					</div>
				</aside>
				<aside class="widget">
					<div class="gurostick-grid">
						<ul class="gurostick"> 
							<li><a title="Grand Guignol" href="http://grandguignol.com.br">Grand Guignol</a></li>
							<li><a title="Gurostick" href="http://gurostick.com">Gurostick</a></li>
						</ul>
					</div>
				</aside>
				<aside class="widget">
					<div class="credits-grid">
						<ul class="credits"> 
							<li><a title="mramos.org" href="http://mramos.org">Desenvolvido por MRamos.org</a></li>
						</ul>
					</div>
				</aside>
		</div><!-- #secondary .widget-area -->
<?php endif; ?>