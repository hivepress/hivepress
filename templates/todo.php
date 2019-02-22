<div class="hp-listing hp-listing--summary">
	<div class="hp-listing__image">
		<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail(); ?></a>
	</div>
	<h4 class="hp-listing__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
	<time class="hp-listing__date" datetime="<?php echo esc_attr( get_the_time( 'Y-m-d' ) ); ?>"><?php printf( esc_html__( 'Added on %s', 'hivepress' ), get_the_date() ); ?></time>
	<div class="hp-listing__description"><?php the_content(); ?></div>
</div>
