<?php
/** Unlisted application portfolio; selections link to the original content. @package John_Platt */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main" class="projects-page portfolio-page" tabindex="-1">
 <?php while ( have_posts() ) : the_post(); ?>
  <header class="projects-intro">
   <div class="projects-intro__heading"><h1 class="projects-intro__title"><?php the_title(); ?></h1></div>
   <?php if ( post_password_required() ) : ?>
    <div class="projects-intro__body"><?php echo get_the_password_form(); ?></div>
   <?php elseif ( '' !== trim( get_the_content() ) ) : ?>
    <div class="projects-intro__body"><?php the_content(); ?></div>
   <?php endif; ?>
  </header>
  <?php if ( ! post_password_required() ) : ?>
   <?php
   $portfolio_ids = john_platt_portfolio_item_ids( get_the_ID() );
   // An empty post__in would return every post: never query until selections exist.
   $portfolio_items = $portfolio_ids ? new WP_Query( array(
    'post_type' => array( 'post', 'project' ), 'post_status' => 'publish', 'has_password' => false,
    'post__in' => $portfolio_ids, 'orderby' => 'post__in', 'posts_per_page' => -1,
    'ignore_sticky_posts' => true, 'no_found_rows' => true,
   ) ) : null;
   ?>
   <div class="project-list">
    <?php if ( $portfolio_items && $portfolio_items->have_posts() ) : ?>
     <?php while ( $portfolio_items->have_posts() ) : $portfolio_items->the_post(); ?>
      <?php get_template_part( 'template-parts/content', 'project-chapter', array( 'number' => $portfolio_items->current_post + 1, 'portfolio' => true ) ); ?>
     <?php endwhile; ?>
    <?php else : ?>
     <p class="projects-empty"><?php esc_html_e( 'No selected work is available yet.', 'john-platt' ); ?></p>
    <?php endif; ?>
   </div>
   <?php wp_reset_postdata(); ?>
  <?php endif; ?>
 <?php endwhile; ?>
</main>
<?php get_footer(); ?>