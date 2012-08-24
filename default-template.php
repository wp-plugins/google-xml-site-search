<?php get_header() ?>

<?php if (have_search_results()) : ?>

	<h1>Search results for '<?php the_search_query() ?>'</h1>

	<?php while (have_search_results()) : the_search_result(); ?>

		<h2><a href="<?php the_search_result_link() ?>"><?php the_search_result_title() ?></a></h2>
		<p><?php the_search_result_content() ?></p>

	<?php endwhile; ?>

	<?php
		// Optionally, to display the results with XSLT, use this instead of the loop above
		//transform_search_result()
	?>

	<?php the_search_result_nav() ?>


<?php else : ?>

	<h1>Nothing found for '<?php the_search_query() ?>'</h1>

	<p>Nothing was found. Try again with another query.</p>

	<?php get_search_form() ?>

<?php endif; ?>

<?php get_footer() ?>