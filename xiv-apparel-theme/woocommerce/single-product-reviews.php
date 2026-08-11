<?php
/**
 * Single product reviews + rating.
 *
 * Custom editorial layout for XIV Apparel. Keeps every WooCommerce hook/filter
 * intact (comments are stored as `review` comments with `_rating` meta), and
 * adds an average rating summary with a 5→1 breakdown bar.
 *
 * @package XIV_Apparel
 * @version 11.0.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! comments_open() ) {
	return;
}

$rating_enabled = wc_review_ratings_enabled();
$count          = $product->get_review_count();
$average        = (float) $product->get_average_rating();
$rating_counts  = (array) $product->get_rating_counts();
$rating_counts  = $rating_counts + array( 5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0 );
?>

<div id="reviews" class="woocommerce-Reviews xiv-max-w-4xl">
	<div id="comments" class="xiv-grid xiv-gap-12 lg:xiv-grid-cols-[minmax(0,1fr)_18rem] lg:xiv-items-start">

		<div class="xiv-order-2 lg:xiv-order-1 xiv-min-w-0">
			<h2 class="woocommerce-Reviews-title xiv-text-sm xiv-font-black xiv-uppercase xiv-tracking-widest xiv-mb-6">
				<?php
				if ( $count && $rating_enabled ) {
					$reviews_title = sprintf(
						esc_html( _n( '%1$s review for %2$s', '%1$s reviews for %2$s', $count, 'woocommerce' ) ),
						esc_html( $count ),
						'<span class="xiv-text-xiv-gray-text">' . esc_html( get_the_title() ) . '</span>'
					);
					echo apply_filters( 'woocommerce_reviews_title', $reviews_title, $count, $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				} else {
					esc_html_e( 'Reviews', 'woocommerce' );
				}
				?>
			</h2>

			<?php if ( have_comments() ) : ?>
				<ol class="commentlist xiv-list-none xiv-p-0 xiv-m-0 xiv-space-y-8">
					<?php
					wp_list_comments(
						apply_filters(
							'woocommerce_product_review_list_args',
							array(
								'callback' => 'woocommerce_comments',
								'style'    => 'ol',
							)
						)
					);
					?>
				</ol>

				<?php
				if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :
					echo '<nav class="woocommerce-pagination xiv-mt-8">';
					paginate_comments_links(
						apply_filters(
							'woocommerce_comment_pagination_args',
							array(
								'prev_text' => '&larr;',
								'next_text' => '&rarr;',
								'type'      => 'list',
							)
						)
					);
					echo '</nav>';
				endif;
				?>
			<?php else : ?>
				<p class="woocommerce-noreviews xiv-text-sm xiv-text-xiv-gray-text xiv-uppercase xiv-tracking-widest"><?php esc_html_e( 'There are no reviews yet.', 'woocommerce' ); ?></p>
			<?php endif; ?>

			<?php if ( get_option( 'woocommerce_review_rating_verification_required' ) === 'no' || wc_customer_bought_product( '', get_current_user_id(), $product->get_id() ) ) : ?>

				<div id="review_form_wrapper" class="xiv-mt-12 xiv-pt-8 xiv-border-t xiv-border-xiv-gray-light">
					<div id="review_form" class="xiv-review-form">
						<?php
						$commenter    = wp_get_current_commenter();
						$name_email   = (bool) get_option( 'require_name_email', 1 );
						$comment_form = array(
							'title_reply'         => have_comments() ? esc_html__( 'ADD A REVIEW', 'woocommerce' ) : sprintf( esc_html__( 'BE THE FIRST TO REVIEW “%s”', 'woocommerce' ), get_the_title() ),
							'title_reply_to'      => esc_html__( 'Leave a Reply to %s', 'woocommerce' ),
							'title_reply_before'  => '<span id="reply-title" class="comment-reply-title xiv-text-sm xiv-font-black xiv-uppercase xiv-tracking-widest">',
							'title_reply_after'   => '</span>',
							'comment_notes_after' => '',
							'label_submit'        => esc_html__( 'SUBMIT REVIEW', 'woocommerce' ),
							'logged_in_as'        => '',
							'comment_field'       => '',
							'fields'              => array(
								'author' => array(
									'label'    => __( 'Name', 'woocommerce' ),
									'type'     => 'text',
									'value'    => $commenter['comment_author'],
									'required' => $name_email,
								),
								'email'  => array(
									'label'    => __( 'Email', 'woocommerce' ),
									'type'     => 'email',
									'value'    => $commenter['comment_author_email'],
									'required' => $name_email,
								),
							),
						);

						$comment_form['comment_field'] = wc_comment_rating_field();

						$comment_form['comment_field'] .= '<textarea id="comment" name="comment" cols="45" rows="8" required placeholder="' . esc_attr__( 'Your review…', 'woocommerce' ) . '"></textarea>';

						comment_form( apply_filters( 'woocommerce_product_review_comment_form_args', $comment_form ) );
						?>
					</div>
				</div>

			<?php else : ?>
				<p class="woocommerce-verification-required xiv-mt-10 xiv-text-sm xiv-text-xiv-gray-text xiv-uppercase xiv-tracking-widest"><?php esc_html_e( 'Only logged in customers who have purchased this product may leave a review.', 'woocommerce' ); ?></p>
			<?php endif; ?>
		</div>

		<?php if ( $rating_enabled && $count ) : ?>
			<aside class="xiv-order-1 lg:xiv-order-2 lg:xiv-border xiv-border-xiv-gray-light xiv-p-6" aria-label="<?php esc_attr_e( 'Rating summary', 'woocommerce' ); ?>">
				<p class="xiv-text-5xl xiv-font-display xiv-font-extrabold xiv-tracking-tighter xiv-m-0"><?php echo esc_html( number_format( $average, 1, ',', '.' ) ); ?></p>
				<div class="xiv-mt-2"><?php echo wc_get_rating_html( $average ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<p class="xiv-text-[11px] xiv-font-mono xiv-uppercase xiv-tracking-widest xiv-text-xiv-gray-text xiv-mt-1.5">
					<?php echo esc_html( sprintf( _n( '%s review', '%s reviews', $count, 'woocommerce' ), $count ) ); ?>
				</p>

				<div class="xiv-mt-6 xiv-space-y-2">
					<?php foreach ( array( 5, 4, 3, 2, 1 ) as $star ) : ?>
						<?php
						$star_count = (int) ( $rating_counts[ $star ] ?? 0 );
						$pct        = $count ? round( $star_count / $count * 100 ) : 0;
						?>
						<div class="xiv-flex xiv-items-center xiv-gap-3" aria-hidden="true">
							<span class="xiv-w-6 xiv-text-[11px] xiv-font-mono xiv-text-xiv-gray-text"><?php echo esc_html( $star ); ?> ★</span>
							<span class="xiv-flex-1 xiv-h-1 xiv-bg-xiv-gray-light xiv-overflow-hidden">
								<span class="xiv-block xiv-h-full xiv-bg-xiv-black" style="width:<?php echo esc_attr( $pct ); ?>%"></span>
							</span>
							<span class="xiv-w-7 xiv-text-right xiv-text-[11px] xiv-font-mono xiv-text-xiv-gray-text"><?php echo esc_html( $star_count ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			</aside>
		<?php endif; ?>
	</div>

	<div class="clear"></div>
</div>
