<?php
/**
 * Site header.
 *
 * @package John_Platt
 */
defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php echo esc_attr( get_bloginfo( 'charset' ) ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'john-platt' ); ?></a>


<header class="site-header<?php echo is_front_page() ? ' site-header--homepage' : ''; ?>" data-site-header>
	<a class="site-header__name" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( sprintf( __( '%s — Home', 'john-platt' ), get_bloginfo( 'name' ) ) ); ?>">
		<span class="site-header__label"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
	</a>
	<button class="site-header__toggle" type="button" aria-expanded="false" aria-controls="primary-navigation" aria-label="Open menu" hidden><span aria-hidden="true"></span></button>
	<?php if ( is_front_page() ) : ?><div class="site-header__nav-target" data-header-navigation-target><?php endif; ?>
	<?php if ( ! is_front_page() && has_nav_menu( 'primary' ) ) : ?>
		<nav id="primary-navigation" class="site-header__nav" aria-label="<?php esc_attr_e( 'Primary', 'john-platt' ); ?>"<?php if ( is_front_page() ) : ?> data-home-navigation<?php endif; ?>>
			<?php wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'fallback_cb' => false, 'menu_class' => 'site-header__menu', 'depth' => 1, 'items_wrap' => '<ul id="%1$s" class="%2$s" role="list">%3$s</ul>' ) ); ?>
		</nav>
	<?php endif; ?>
	<?php if ( is_front_page() ) : ?></div><?php endif; ?>
</header>
