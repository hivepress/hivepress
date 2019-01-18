<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $listing_query->have_posts() ) :
	?>
	<table>
		<thead>
			<tr>
				<th><?php esc_html_e( 'Listing', 'hivepress' ); ?></th>
				<th></th>
				<th><?php esc_html_e( 'Date', 'hivepress' ); ?></th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php
			while ( $listing_query->have_posts() ) :
				$listing_query->the_post();
				?>
				<tr class="hp-listing hp-listing--edit">
					<td class="hp-listing__title">
						<?php
						if ( get_post_status() === 'pending' ) :
							the_title();
						else :
							?>
							<a href="<?php echo esc_url( hivepress()->template->get_url( 'listing__edit', [ get_the_ID() ] ) ); ?>"><i class="hp-icon fas fa-edit"></i><span><?php the_title(); ?></span></a>
						<?php endif; ?>
					</td>
					<td class="hp-listing__status hp-listing__status--<?php echo esc_attr( get_post_status() ); ?>">
						<?php if ( get_post_status() === 'pending' ) : ?>
							<span><?php esc_html_e( 'Pending', 'hivepress' ); ?></span>
						<?php elseif ( get_post_status() === 'draft' ) : ?>
							<span><?php esc_html_e( 'Rejected', 'hivepress' ); ?></span>
						<?php endif; ?>
					</td>
					<td class="hp-listing__date">
						<time datetime="<?php echo esc_attr( get_the_time( 'Y-m-d' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
					</td>
					<td class="hp-listing__actions">
						<?php if ( get_post_status() === 'publish' ) : ?>
							<a href="<?php the_permalink(); ?>" target="_blank"><i class="hp-icon fas fa-external-link-alt"></i><span><?php esc_html_e( 'View', 'hivepress' ); ?></span></a>
						<?php endif; ?>
					</td>
				</tr>
			<?php endwhile; ?>
		</tbody>
	</table>
<?php else : ?>
	<div class="hp-no-results">
		<p><?php esc_html_e( 'No listings yet.', 'hivepress' ); ?></p>
	</div>
	<?php
endif;
