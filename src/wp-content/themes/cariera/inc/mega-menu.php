<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Extending Walker_Nav_Menu class for Cariera custom menu
 */
class Cariera_Mega_Menu_Walker extends Walker_Nav_Menu {

	/**
	 * Save current item so it can be used in start level.
	 *
	 * @var [type]
	 */
	private $cur_item;

	/**
	 * Current level of the menu
	 *
	 * @var [type]
	 */
	private $cur_lvl;

	/**
	 * Mega Menu
	 *
	 * @var boolean
	 */
	protected $megamenu = false;

	/**
	 * Starts the list before the elements are added.
	 *
	 * @see Walker::start_lvl()
	 *
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   An array of arguments. @see wp_nav_menu().
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$indent = str_repeat( "\t", $depth );

		$submenu        = ( $depth > 0 ) ? esc_attr( 'sub-menu' ) : '';
		$megamenu_width = get_post_meta( $this->cur_item->ID, '_menu-item-megamenuwidth', true );
		$style          = '';

		if ( $megamenu_width ) {
			$style = 'style="width:' . esc_attr( $megamenu_width ) . '"';
		}

		if ( ! $this->megamenu ) {
			$output .= "\n$indent<ul class=\"dropdown-menu $submenu depth_$depth\">\n";
		} else {
			if ( $depth == 0 ) {
				$output .= "\n$indent<ul class=\"dropdown-menu\" $style>\n$indent<li>\n$indent<div class=\"mega-menu-inner\">\n$indent<div class=\"row\">\n";
			} elseif ( $depth == 1 ) {
				$output .= "\n$indent<div class=\"mega-menu-submenu\"><ul class=\"sub-menu check\">\n";
			} else {
				$output .= "\n$indent<ul class=\"sub-menu check\">\n";
			}
		}
	}

	/**
	 * Ends the list of after the elements are added.
	 *
	 * @see Walker::end_lvl()
	 *
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   An array of arguments. @see wp_nav_menu()
	 */
	public function end_lvl( &$output, $depth = 0, $args = [] ) {
		$indent = str_repeat( "\t", $depth );

		if ( ! $this->megamenu ) {
			$output .= "\n$indent</ul>\n";
		} else {
			if ( $depth == 0 ) {
				$output .= "\n$indent</div>\n$indent</div>\n$indent</li>\n$indent</ul>\n";
			} elseif ( $depth == 1 ) {
				$output .= "\n$indent</ul>\n$indent</div>";
			} else {
				$output .= "\n$indent</ul>\n";
			}
		}
	}

	/**
	 * Starts the element output.
	 *
	 * @since 3.0.0
	 * @since 4.4.0 The {@see 'nav_menu_item_args'} filter was added.
	 *
	 * @see Walker::start_el()
	 *
	 * @param string   $output Used to append additional content (passed by reference).
	 * @param WP_Post  $item   Menu item data object.
	 * @param int      $depth  Depth of menu item. Used for padding.
	 * @param stdClass $args   An object of wp_nav_menu() arguments.
	 * @param int      $id     Current item ID.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

		// Save current item to private cur_item to use it in start_lvl.
		$this->cur_item = $item;

		$class_names  = '';
		$classes      = empty( $item->classes ) ? [] : (array) $item->classes;
		$classes[]    = 'menu-item-' . $item->ID;
		$classes[]    = 'parentid_' . get_post_meta( $item->ID, '_menu_item_menu_item_parent', true );
		$item_is_mega = apply_filters( 'cariera_menu_item_mega', get_post_meta( $item->ID, '_menu-item-megamenu', true ), $item->ID );

		/**
		 * Filters the arguments for a single nav menu item.
		 *
		 * @since 4.4.0
		 *
		 * @param stdClass $args  An object of wp_nav_menu() arguments.
		 * @param WP_Post  $item  Menu item data object.
		 * @param int      $depth Depth of menu item. Used for padding.
		 */
		$args = apply_filters( 'nav_menu_item_args', $args, $item, $depth );

		// Custom Cariera Mega Menu.
		$hidden_status = get_post_meta( $item->ID, '_menu-item-hiddenonmobile', true );

		if ( $hidden_status == 'hide' ) {
			$classes[] = 'hide-on-mobile';
		}

		// Check if this is top level and is mega menu.
		if ( ! $depth ) {
			$this->megamenu = $item_is_mega;
		}

		// Add active class for current menu item.
		$active_classes = [
			'current-menu-item',
			'current-menu-parent',
			'current-menu-ancestor',
		];

		$is_active = array_intersect( $classes, $active_classes );
		if ( ! empty( $is_active ) ) {
			$classes[] = 'active';
		}

		if ( in_array( 'menu-item-has-children', $classes, true ) ) {
			if ( ! $depth ) {
				$classes[] = 'dropdown';
			}
			if ( ! $depth && $this->megamenu ) {
				$classes[] = 'mega-menu';
			}
			if ( $depth && ! $this->megamenu ) {
				$classes[] = 'dropdown-submenu';
			}
		}

		/**
		 * Filters the CSS classes applied to a menu item's list item element.
		 *
		 * @since 3.0.0
		 * @since 4.1.0 The `$depth` parameter was added.
		 *
		 * @param string[] $classes Array of the CSS classes that are applied to the menu item's `<li>` element.
		 * @param WP_Post  $item    The current menu item.
		 * @param stdClass $args    An object of wp_nav_menu() arguments.
		 * @param int      $depth   Depth of menu item. Used for padding.
		 */
		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
		$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

		/**
		 * Filters the ID applied to a menu item's list item element.
		 *
		 * @since 3.0.1
		 * @since 4.1.0 The `$depth` parameter was added.
		 *
		 * @param string   $menu_id The ID that is applied to the menu item's `<li>` element.
		 * @param WP_Post  $item    The current menu item.
		 * @param stdClass $args    An object of wp_nav_menu() arguments.
		 * @param int      $depth   Depth of menu item. Used for padding.
		 */
		$id = apply_filters( 'nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args, $depth );
		$id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

		// $widthclass = get_post_meta( $item->ID, '_menu-item-columns', true);
		$parent     = get_post_meta( $item->ID, '_menu_item_menu_item_parent', true );
		$widthclass = get_post_meta( $parent, '_menu-item-columns', true );

		if ( $depth == 1 && $this->megamenu ) {
			$output .= $indent . '<div' . $id . ' class="col-md-' . $widthclass . '">' . "\n";
			$output .= $indent . '<div class="menu-item-mega">';
		} else {
			$output .= $indent . '<li' . $id . $class_names . '>';
		}

		$atts           = [];
		$atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
		$atts['target'] = ! empty( $item->target ) ? $item->target : '';
		if ( '_blank' === $item->target && empty( $item->xfn ) ) {
			$atts['rel'] = 'noopener';
		} else {
			$atts['rel'] = $item->xfn;
		}
		$atts['href']         = ! empty( $item->url ) ? $item->url : '';
		$atts['aria-current'] = $item->current ? 'page' : '';

		if ( in_array( 'menu-item-has-children', $classes, true ) ) {
			$atts['class']         = 'dropdown-toggle';
			$atts['role']          = 'button';
			$atts['data-toggle']   = 'dropdown';
			$atts['aria-haspopup'] = 'true';
			$atts['aria-expanded'] = 'false';
		}

		/**
		 * Filters the HTML attributes applied to a menu item's anchor element.
		 *
		 * @since 3.6.0
		 * @since 4.1.0 The `$depth` parameter was added.
		 *
		 * @param array $atts {
		 *     The HTML attributes applied to the menu item's `<a>` element, empty strings are ignored.
		 *
		 *     @type string $title        Title attribute.
		 *     @type string $target       Target attribute.
		 *     @type string $rel          The rel attribute.
		 *     @type string $href         The href attribute.
		 *     @type string $aria-current The aria-current attribute.
		 * }
		 * @param WP_Post  $item  The current menu item.
		 * @param stdClass $args  An object of wp_nav_menu() arguments.
		 * @param int      $depth Depth of menu item. Used for padding.
		 */
		$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

		$attributes = '';
		foreach ( $atts as $attr => $value ) {
			if ( is_scalar( $value ) && '' !== $value && false !== $value ) {
				$value       = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
				$attributes .= ' ' . $attr . '="' . $value . '"';
			}
		}

		/** This filter is documented in wp-includes/post-template.php */
		$title = apply_filters( 'the_title', $item->title, $item->ID );

		/**
		 * Filter a menu item's title.
		 *
		 * @since 4.4.0
		 *
		 * @param string $title The menu item's title.
		 * @param object $item  The current menu item.
		 * @param array  $args  An array of {@see wp_nav_menu()} arguments.
		 * @param int    $depth Depth of menu item. Used for padding.
		 */
		$title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );

		// Custom: Assign badges to the menu item if selected.
		$badge = get_post_meta( $item->ID, '_menu-item-badge', true );

		if ( $badge == 'no-badge' ) {
			$badge = '';
		} elseif ( $badge == 'new-badge' ) {
			$badge = '<span class="items-badge"><span class="new-badge">' . esc_html__( 'New', 'cariera' ) . '</span></span>';
		} elseif ( $badge == 'hot-badge' ) {
			$badge = '<span class="items-badge"><span class="hot-badge">' . esc_html__( 'Hot', 'cariera' ) . '</span></span>';
		} elseif ( $badge == 'trending-badge' ) {
			$badge = '<span class="items-badge"><span class="trending-badge">' . esc_html__( 'Trending', 'cariera' ) . '</span></span>';
		} else {
			$badge = '';
		}

		// Custom: Menu Icons.
		$icons = get_post_meta( $item->ID, '_menu-item-icons', true );
		$icon  = get_post_meta( $item->ID, '_menu-item-icon', true );

		if ( $icons ) {
			$icon = '<i class="' . esc_attr( $icon ) . '"></i>';
		} else {
			$icon = '';
		}

		if ( $depth == 1 && $this->megamenu ) {
			$item_output = '<a ' . $attributes . '>' . $icon . $title . $badge . '</a>';
		} else {
			$item_output  = $args->before;
			$item_output .= '<a' . $attributes . '>';
			$item_output .= $args->link_before . $icon . $title . $badge . $args->link_after;
			$item_output .= '</a>';
			$item_output .= $args->after;
		}

		/**
		 * Filter a menu item's starting output.
		 *
		 * The menu item's starting output only includes $args->before, the opening <a>,
		 * the menu item's title, the closing </a>, and $args->after. Currently, there is
		 * no filter for modifying the opening and closing <li> for a menu item.
		 *
		 * @since 3.0.0
		 *
		 * @see wp_nav_menu()
		 *
		 * @param string $item_output The menu item's starting HTML output.
		 * @param object $item        Menu item data object.
		 * @param int    $depth       Depth of menu item. Used for padding.
		 * @param array  $args        An array of wp_nav_menu() arguments.
		 */
		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}

	/**
	 * Ends the element output, if needed.
	 *
	 * @since 3.0.0
	 *
	 * @see Walker::end_el()
	 *
	 * @param string   $output Used to append additional content (passed by reference).
	 * @param WP_Post  $item   Page data object. Not used.
	 * @param int      $depth  Depth of page. Not Used.
	 * @param stdClass $args   An object of wp_nav_menu() arguments.
	 */
	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		if ( $depth == 1 && $this->megamenu ) {
			$output .= "</div>\n";
			$output .= "</div>\n";
		} else {
			$output .= "</li>\n";
		}
	}
}


/**
 * Adding custom menu fields to the menu editing backend
 *
 * @since  1.5.4
 */
function cariera_custom_menu_fields( $item_id, $item, $depth ) {
	$icon_checkbox   = '';
	$icons           = get_post_meta( $item_id, '_menu-item-icons', true );
	$icon            = get_post_meta( $item_id, '_menu-item-icon', true );
	$fa_icons        = cariera_fontawesome_icons_list();
	$sl_icons        = cariera_simpleline_icons_list();
	$badge           = get_post_meta( $item_id, '_menu-item-badge', true );
	$mobile_checkbox = '';
	$mobilestatus    = get_post_meta( $item_id, '_menu-item-hiddenonmobile', true );

	if ( $icons != '' ) {
		$icon_checkbox = "checked='checked'";
	}

	if ( $mobilestatus == 'hide' ) {
		$mobile_checkbox = "checked='checked'";
	}
	?>

	<!-- Custom Menu Icon Enabler -->
	<p class="field-menu-columns description description-wide">
		<label for="edit-menu-item-icons-<?php echo esc_attr( $item_id ); ?>">
			<input type="checkbox" id="edit-menu-item-icons-<?php echo esc_attr( $item_id ); ?>" value="_blank" name="menu-item-icons[<?php echo esc_attr( $item_id ); ?>]"<?php echo esc_attr( $icon_checkbox ); ?> />
			<?php esc_html_e( 'Enable Icons', 'cariera' ); ?>
		</label>
	</p>

	<!-- Custom Menu Icon Picker -->
	<p class="field-menu-columns description description-wide">
		<label for="edit-menu-item-icon-<?php echo esc_attr( $item_id ); ?>"><?php esc_html_e( 'Icon', 'cariera' ); ?>
			<select id="edit-menu-item-icon-<?php echo esc_attr( $item_id ); ?>" class="cariera-icon-select widefat edit-menu-item-icon" name="menu-item-icon[<?php echo esc_attr( $item_id ); ?>]">
				<?php
				// Fontawesome icons.
				foreach ( $fa_icons as $key => $value ) {
					echo '<option value="' . $key . '" ';
					if ( isset( $icon ) && $icon == $key ) {
						echo ' selected="selected"';
					}
					echo '>' . $value . '</option>';
				}

				// Simpleline icons.
				foreach ( $sl_icons as $key => $value ) {
					echo '<option value="icon-' . $key . '" ';
					if ( isset( $icon ) && $icon == 'icon-' . $key ) {
						echo ' selected="selected"';
					}
					echo '>' . $value . '</option>';
				}

				// Iconsmind icons.
				if ( get_option( 'cariera_font_iconsmind' ) ) {
					$im_icons = cariera_iconsmind_list();
					foreach ( $im_icons as $key ) {
						echo '<option value="iconsmind-' . $key . '" ';
						if ( isset( $icon ) && $icon == 'iconsmind-' . $key ) {
							echo ' selected="selected"';
						}
						echo '>' . $key . '</option>';
					}
				}
				?>
			</select>
		</label>
	</p>

	<!-- Mega Menu Elements -->
	<?php
	if ( $depth === 0 ) {
		$mega_checkbox  = '';
		$megamenu       = get_post_meta( $item_id, '_menu-item-megamenu', true );
		$megamenu_width = get_post_meta( $item_id, '_menu-item-megamenuwidth', true );
		$col            = get_post_meta( $item_id, '_menu-item-columns', true );

		if ( $megamenu != '' ) {
			$mega_checkbox = "checked='checked'";
		}
		?>

		<p class="field-megamenu description description-wide">
			<label for="edit-menu-item-megamenu-<?php echo esc_attr( $item_id ); ?>">
				<input type="checkbox" id="edit-menu-item-megamenu-<?php echo esc_attr( $item_id ); ?>" value="_blank" name="menu-item-megamenu[<?php echo esc_attr( $item_id ); ?>]"<?php echo esc_attr( $mega_checkbox ); ?> />
				<?php esc_html_e( 'Enable megamenu', 'cariera' ); ?>
			</label>
		</p>

		<p class="field-megamenu-width description description-wide">
			<label for="edit-menu-item-megamenuwidth-<?php echo esc_attr( $item_id ); ?>">
				<?php esc_html_e( 'Mega Menu Width. For example "55%"', 'cariera' ); ?><br />
				<input type="text" id="edit-menu-item-megamenuwidth-<?php echo esc_attr( $item_id ); ?>" class="widefat code edit-menu-item-megamenuwidth" name="menu-item-megamenuwidth[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $megamenu_width ); ?>" />
			</label>
		</p>

		<p class="field-menu-columns description description-wide">
			<label for="edit-menu-item-columns-<?php echo esc_attr( $item_id ); ?>"><?php esc_html_e( 'Number of columns', 'cariera' ); ?>
				<select id="edit-menu-item-columns-<?php echo esc_attr( $item_id ); ?>" class="widefat edit-menu-item-columns" name="menu-item-columns[<?php echo esc_attr( $item_id ); ?>]">
					<option value="6" 
					<?php
					if ( $col == '6' ) {
						echo 'selected'; }
					?>
					><?php esc_html_e( '2 columns', 'cariera' ); ?></option>
					<option value="4" 
					<?php
					if ( $col == '4' ) {
						echo 'selected'; }
					?>
					><?php esc_html_e( '3 columns', 'cariera' ); ?></option>
					<option value="3" 
					<?php
					if ( $col == '3' ) {
						echo 'selected'; }
					?>
					><?php esc_html_e( '4 columns', 'cariera' ); ?></option>
				</select>
			</label>
		</p>
	<?php } ?>

	<!-- Custom Menu Item Badges -->
	<p class="field-menu-columns description description-wide">
		<label for="edit-menu-item-badge-<?php echo esc_attr( $item_id ); ?>"><?php esc_html_e( 'Set a badge for your menu item', 'cariera' ); ?>
			<select id="edit-menu-item-badge-<?php echo esc_attr( $item_id ); ?>" class="widefat edit-menu-item-badge" name="menu-item-badge[<?php echo esc_attr( $item_id ); ?>]">
				<option value="no-badge" <?php echo $badge === 'no-badge' ? esc_attr( 'selected' ) : ''; ?>><?php esc_html_e( 'No Badge', 'cariera' ); ?></option>
				<option value="new-badge" <?php echo $badge === 'new-badge' ? esc_attr( 'selected' ) : ''; ?>><?php esc_html_e( 'New Badge', 'cariera' ); ?></option>
				<option value="hot-badge" <?php echo $badge === 'hot-badge' ? esc_attr( 'selected' ) : ''; ?>><?php esc_html_e( 'Hot Badge', 'cariera' ); ?></option>
				<option value="trending-badge" <?php echo $badge === 'trending-badge' ? esc_attr( 'selected' ) : ''; ?>><?php esc_html_e( 'Trending Badge', 'cariera' ); ?></option>
			</select>
		</label>
	</p>

	<!-- Hide on mobile -->
	<p class="field-hiddenonmobile description description-wide">
		<label for="edit-menu-item-hiddenonmobile-<?php echo esc_attr( $item_id ); ?>">
			<input type="checkbox" id="edit-menu-item-hiddenonmobile-<?php echo esc_attr( $item_id ); ?>" value="hide" name="menu-item-hiddenonmobile[<?php echo esc_attr( $item_id ); ?>]" <?php echo esc_attr( $mobile_checkbox ); ?> />
			<?php esc_html_e( 'Hide on mobile navigation', 'cariera' ); ?>
		</label>
	</p>
	<?php
}

add_action( 'wp_nav_menu_item_custom_fields', 'cariera_custom_menu_fields', 10, 3 );

/**
 * Save and update the custom fields for the menu
 *
 * @since  1.3.5
 */
function cariera_update_menu( $menu_id, $menu_item_db ) {
	$check = [ 'icons', 'icon', 'megamenu', 'megamenuwidth', 'columns', 'badge', 'hiddenonmobile' ];

	foreach ( $check as $key ) {
		if ( ! isset( $_POST[ 'menu-item-' . $key ][ $menu_item_db ] ) ) {
			$_POST[ 'menu-item-' . $key ][ $menu_item_db ] = '';
		}

		$value = $_POST[ 'menu-item-' . $key ][ $menu_item_db ];
		update_post_meta( $menu_item_db, '_menu-item-' . $key, $value );
	}
}

add_action( 'wp_update_nav_menu_item', 'cariera_update_menu', 100, 3 );

/**
 * Replace menu item url for handling demo pages
 *
 * @since  1.5.5
 */
function cariera_format_menu_link( $atts, $item, $args, $depth ) {
	$atts['href'] = str_replace( '/__site_url', get_site_url(), $atts['href'] );

	return $atts;
}

add_filter( 'nav_menu_link_attributes', 'cariera_format_menu_link', 10, 4 );
