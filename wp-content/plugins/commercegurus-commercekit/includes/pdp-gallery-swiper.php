<?php
/**
 * Main CommerceGurus Gallery template
 *
 * @author   CommerceGurus
 * @package  CommerceGurus_Gallery
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $product, $cgkit_gallery_layout, $cgkit_gallery_caption;
$product_id        = $product->get_id();
$post_thumbnail_id = $product->get_image_id();
$wrapper_classes   = apply_filters(
	'woocommerce_single_product_image_gallery_classes',
	array(
		'images',
	)
);

$options        = get_option( 'commercekit', array() );
$pdp_lightbox   = ( ( isset( $options['pdp_lightbox'] ) && 1 === (int) $options['pdp_lightbox'] ) || ! isset( $options['pdp_lightbox'] ) ) ? true : false;
$pdp_autoplay   = ( ( isset( $options['pdp_video_autoplay'] ) && 1 === (int) $options['pdp_video_autoplay'] ) || ! isset( $options['pdp_video_autoplay'] ) ) ? true : false;
$pdp_thumbnails = isset( $options['pdp_desktop_thumbnails'] ) && ! empty( $options['pdp_desktop_thumbnails'] ) ? (int) $options['pdp_desktop_thumbnails'] : (int) commercekit_get_default_settings( 'pdp_desktop_thumbnails' );
$pdp_m_thumbs   = isset( $options['pdp_mobile_thumbnails'] ) && ! empty( $options['pdp_mobile_thumbnails'] ) ? (int) $options['pdp_mobile_thumbnails'] : (int) commercekit_get_default_settings( 'pdp_mobile_thumbnails' );
$pdp_thumbs_o   = (int) $pdp_thumbnails;
$pdp_thumbnails = apply_filters( 'commercekit_product_gallery_thumbnails', $pdp_thumbnails );
$pdp_m_thumbs   = $pdp_thumbs_o !== (int) $pdp_thumbnails ? (int) $pdp_thumbnails : $pdp_m_thumbs;
$pdp_m_thumbs   = apply_filters( 'commercekit_product_mobile_gallery_thumbnails', $pdp_m_thumbs );
$pdp_thub_count = 0;
$video_gallery  = get_post_meta( $product_id, 'commercekit_wc_video_gallery', true );

$cgkit_gallery_layout  = isset( $options['pdp_gallery_layout'] ) && ! empty( $options['pdp_gallery_layout'] ) ? $options['pdp_gallery_layout'] : commercekit_get_default_settings( 'pdp_gallery_layout' );
$cgkit_gallery_layout2 = get_post_meta( $product_id, 'commercekit_gallery_layout', true );
$preload_count         = 0;
if ( isset( $options['pdp_mobile_layout'] ) && 'show-edge' === $options['pdp_mobile_layout'] ) {
	$wrapper_classes[] = 'ckit-mobile-pdp-gallery-active';
	$wrapper_classes[] = 'ckit-mobile-show-edge';
	$preload_count     = 1;
} elseif ( ( isset( $options['pdp_mobile_layout'] ) && 'minimal' === $options['pdp_mobile_layout'] ) || ( isset( $options['pdp_mobile_optimized'] ) && 1 === (int) $options['pdp_mobile_optimized'] ) ) {
	$wrapper_classes[] = 'ckit-mobile-pdp-gallery-active';
}
if ( isset( $options['widget_pos_pdp_gallery'] ) && 1 === (int) $options['widget_pos_pdp_gallery'] ) {
	$wrapper_classes[] = 'ckit-shortcode-gallery';
}
if ( ! empty( $cgkit_gallery_layout2 ) ) {
	$cgkit_gallery_layout = $cgkit_gallery_layout2;
}
if ( 'horizontal' === $cgkit_gallery_layout && isset( $options['pdp_image_caption'] ) && 1 === (int) $options['pdp_image_caption'] ) {
	$cgkit_gallery_caption = true;
}
$wrapper_classes[] = 'cgkit-gallery-' . esc_attr( $cgkit_gallery_layout );

$image_ids      = array();
$one_slider_css = '';
if ( $post_thumbnail_id ) {
	$image_ids = $product->get_gallery_image_ids();
	array_unshift( $image_ids, $post_thumbnail_id );
	if ( 1 === count( $image_ids ) ) {
		$one_slider_css = 'cgkit-one-slider';
	}
} else {
	$one_slider_css = 'cgkit-one-slider';
}
$is_default_variation = false;
$query_variations     = array();
if ( $product->is_type( 'variable' ) ) {
	$default_attributes = $product->get_default_attributes();
	$variations         = commercekit_get_available_variations( $product );
	if ( is_array( $variations ) && count( $variations ) ) {
		foreach ( $variations as $variation ) {
			$variation_attributes = array();
			$variation_img_id     = isset( $variation['cgkit_image_id'] ) ? $variation['cgkit_image_id'] : get_post_thumbnail_id( $variation['variation_id'] );
			if ( isset( $variation['attributes'] ) && count( $variation['attributes'] ) && $variation_img_id ) {
				foreach ( $variation['attributes'] as $va_key => $va_val ) {
					$query_variations[ $va_key ][] = $va_val;

					$va_key = str_replace( 'attribute_', '', $va_key );

					$variation_attributes[ $va_key ] = $va_val;
				}
			}
			if ( $variation_attributes == $default_attributes ) { // phpcs:ignore
				$is_default_variation = true;
			}
		}
	}
}
if ( ! $is_default_variation ) {
	if ( count( $query_variations ) ) {
		foreach ( $query_variations as $va_key => $va_val ) {
			if ( isset( $_GET[ $va_key ] ) && in_array( $_GET[ $va_key ], $va_val ) ) { // phpcs:ignore
				$is_default_variation = true;
				break;
			}
		}
	}
}
$placeholder_image = '<li class="swiper-slide" itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject"><div class="woocommerce-product-gallery__image--placeholder">' . sprintf( '<img src="%s" alt="%s" class="wp-post-image" />', esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ), esc_html__( 'Awaiting product image', 'commercegurus-commercekit' ) ) . '</div></li>';
$vertical_thumbs   = apply_filters( 'commercekit_product_vertical_gallery_thumbnails', 5 );
?>
<style>
	.cg-main-swiper.swiper-container, .cg-thumb-swiper.swiper-container {
		width: 100%;
		height: 100%;
	}
	.cg-main-swiper ul.swiper-wrapper, .cg-thumb-swiper ul.swiper-wrapper {
		padding: 0;
		margin: 0;
	}
	.cg-main-swiper .swiper-slide, .cg-thumb-swiper .swiper-slide {
		text-align: center;
		font-size: 18px;
		background: #fff;
		/* Center slide text vertically */
		display: -webkit-box;
		display: -ms-flexbox;
		display: -webkit-flex;
		display: flex;
		-webkit-box-pack: center;
		-ms-flex-pack: center;
		-webkit-justify-content: center;
		justify-content: center;
		-webkit-box-align: center;
		-ms-flex-align: center;
		-webkit-align-items: center;
		align-items: center;
		height: auto;
		flex-flow: wrap;
	}
	.cg-main-swiper .swiper-slide:has(.cgkit-image-caption) {
		background-color: transparent;
	}
	.cgkit-image-caption {
		padding: 10px 1.5em;
		font-size: 13px;
	}
	.swiper-slide-imglink {
		height: auto;
		width: 100%;
	}
	.cg-main-swiper.swiper-container, .cg-thumb-swiper.swiper-container {
		width: 100%;
		margin-left: auto;
		margin-right: auto;
	}
	.cg-main-swiper {
		height: auto;
		width: 100%;
	}
	.cg-thumb-swiper {
		height: 20%;
		box-sizing: border-box;
		padding-top: 10px;
	}
	.cg-thumb-swiper .swiper-slide {
		height: 100%;
		opacity: 0.4;
	}
	.cg-thumb-swiper .swiper-slide:focus-visible {
		outline: 0.25rem solid #2491ff;
		outline-offset: 0;
	}
	.cg-thumb-swiper .swiper-slide-thumb-active {
		opacity: 1;
	}
	.cg-main-swiper .swiper-slide img, .cg-thumb-swiper .swiper-slide img {
		display: block;
		width: 100%;
		height: auto;
	}
	.cg-main-swiper .swiper-button-next, .cg-main-swiper .swiper-button-prev,
	.cg-thumb-swiper .swiper-button-next, .cg-thumb-swiper .swiper-button-prev {
		background-image: none;
	}
	.gallery-hide {
		display: none;
	}
	.gallery-show {
		display: block;
	}
	.cg-swiper-preloader {
		width: 42px;
		height: 42px;
		position: absolute;
		left: 50%;
		top: 50%;
		margin-left: -21px;
		margin-top: -21px;
		z-index: 10;
		transform-origin: 50%;
		animation: swiper-preloader-spin 1s infinite linear;
		box-sizing: border-box;
		border: 4px solid var(--swiper-preloader-color,var(--swiper-theme-color));
		border-radius: 50%;
		border-top-color: transparent;
	}
	.elementor-invisible {
		visibility: visible;
	}
	.cg-main-swiper .swiper-button-next.swiper-button-disabled,
	.cg-main-swiper .swiper-button-prev.swiper-button-disabled,
	.cg-thumb-swiper .swiper-button-next.swiper-button-disabled,
	.cg-thumb-swiper .swiper-button-prev.swiper-button-disabled {
		visibility: hidden;
	}
	.cg-thumbs-3.cg-thumb-swiper .swiper-slide { width: 33.3333%; }
	.cg-thumbs-4.cg-thumb-swiper .swiper-slide { width: 25%; }
	.cg-thumbs-5.cg-thumb-swiper .swiper-slide { width: 20%; }
	.cg-thumbs-6.cg-thumb-swiper .swiper-slide { width: 16.6666%; }
	.cg-thumbs-7.cg-thumb-swiper .swiper-slide { width: 14.2857%; }
	.cg-thumbs-8.cg-thumb-swiper .swiper-slide { width: 12.5%; }

	.pswp button.pswp__button {
		background-color: transparent;
	}

	/* Hide prev arrow if swiper not initialized */
	#commercegurus-pdp-gallery .swiper-container:not(.swiper-container-initialized) .swiper-button-prev {
		visibility: hidden;
	}

	/* If 2 or 3 gallery thumbnails present - center the thumbnails row initially to prevent CLS */
	.cg-thumbs-count-2:not(.swiper-container-initialized) .swiper-wrapper, 
	.cg-thumbs-count-3:not(.swiper-container-initialized) .swiper-wrapper {
		justify-content: center;
	}
	.cg-thumb-swiper.swiper-container {
		margin-left: -5px;
		width: calc(100% + 10px);
	}
	.rtl .cg-thumb-swiper.swiper-container {
		margin-left: 0;
		margin-right: -5px;
	}
	.cg-thumb-swiper .swiper-slide {
		padding-left: 5px;
		padding-right: 5px;
		background-color: transparent;
	}
	.product ul li.swiper-slide {
		margin: 0;
	}
	div.cgkit-play, div.cgkit-play svg {
		position:absolute;
		font-size: 100%;
		border-radius: 100px;
		top: 50%;
		left: 50%;
		width: 40px;
		height: 40px;
		transform: translate(-50%,-50%);
		z-index: 10;
	}
	div.cgkit-play:hover {
		background-color: rgba(0,0,0,.5);
	}
	div.cgkit-play:active, div.cgkit-play:focus {
		outline: 0;
		border: none;
		-moz-outline-style: none;
	}
	div.cg-main-swiper div.cgkit-play, div.cg-main-swiper div.cgkit-play svg,
	div.pswp__scroll-wrap div.cgkit-play, div.pswp__scroll-wrap div.cgkit-play svg {
		width: 80px;
		height: 80px;
	}
	div.cg-main-swiper div.cgkit-play svg,
	div.pswp__scroll-wrap div.cgkit-play svg {
		width: 100px;
		height: 100px;
	}
	div.cgkit-iframe-wrap {
		position: relative;
		padding-bottom: 56.25%;
		height: 0;	
	}
	div.cgkit-iframe-wrap iframe {
		position: absolute;
		top: 0;
		left: 1px;
		width: 100%;
		height: 100%;
	} 
	div.cgkit-video-wrap {
		position: relative;
		width: 100%;
	}
	div.cgkit-video-wrap video {
		width: 100%;
		height: auto;
		object-fit: fill;
		-o-object-fit: fill;
	}
	div.pswp__scroll-wrap div.cgkit-video-wrap {
		width: auto;
		height: 100%;
		margin: 0 auto;
	}
	div.pswp__scroll-wrap div.cgkit-video-wrap video {
		width: auto;
		height: 100%;
	}
	div.cgkit-video-wrap div.cgkit-play {
		display: none;
	}
	div.cgkit-video-wrap:hover div.cgkit-play,
	div.cgkit-video-wrap div.cgkit-play.not-autoplay {
		display: block;
	}
	div#elementor-lightbox-slideshow-single-img {
		display: none !important;
	}
	/* Full screen video on mobile */
	@media (max-width: 770px) {
		div.pswp__scroll-wrap div.cgkit-video-wrap {
			width: auto !important; /* Safari */
			display: flex;
			align-items: center;
		}
		div.pswp__scroll-wrap div.cgkit-video-wrap video {
			height: auto !important;
		}
		div.cg-main-swiper div.cgkit-video-wrap.autoplay div.cgkit-play svg,
		div.pswp__scroll-wrap div.cgkit-video-wrap.autoplay div.cgkit-play svg {
			display: none;
		}
		div.cgkit-video-wrap.autoplay div.cgkit-play {
			width: 100%;
			height: 100%;
			border-radius: 0px;
		}
		div.cgkit-video-wrap.autoplay div.cgkit-play:hover {
			background: none;
		}
		div.cgkit-video-wrap.autoplay video {
			display: block;
		}
		div.cgkit-video-wrap.autoplay img {
			display: none;
		}
		.cg-m-thumbs-3.cg-thumb-swiper .swiper-slide { width: 33.3333%; }
		.cg-m-thumbs-4.cg-thumb-swiper .swiper-slide { width: 25%; }
		.cg-m-thumbs-5.cg-thumb-swiper .swiper-slide { width: 20%; }
		.cg-m-thumbs-6.cg-thumb-swiper .swiper-slide { width: 16.6666%; }
		.cg-m-thumbs-7.cg-thumb-swiper .swiper-slide { width: 14.2857%; }
		.cg-m-thumbs-8.cg-thumb-swiper .swiper-slide { width: 12.5%; }
	}
	@media (min-width: 771px) {
		.cg-layout-vertical-right {
			display: flex;
		}
		.cg-layout-vertical-right .cg-main-swiper {
			flex: calc(100% - 100px);
			margin-left: 0px;
			margin-right: 5px;
			transition: all 0.1s ease-in;
		}
		.rtl .cg-layout-vertical-right .cg-main-swiper {
			margin-left: 5px;
			margin-right: 0;
		}
		.cg-layout-vertical-right .cg-thumb-swiper {
			flex: 100px;
			padding: 0px;
		}
		.cg-layout-vertical-right .cg-thumb-swiper .swiper-wrapper {
			display: block;
		}
		.cg-layout-vertical-right .cg-thumb-swiper .swiper-slide {
			width: 100px;
			height: 105px;
			display: flex;
			align-items: center;
			justify-content: center;
			box-sizing: border-box;
			cursor: pointer;
			overflow: hidden;
			position: relative;
			padding: 2.5px 0 2.5px 5px;
		}
		.rtl .cg-layout-vertical-right .cg-thumb-swiper .swiper-slide {
			padding: 2.5px 5px 2.5px 0px;
		}
		.cg-layout-vertical-right .cg-thumb-swiper .swiper-slide:first-child {
			padding-top: 0px;
		}
		.cg-layout-vertical-right .cg-thumb-swiper .swiper-slide img {
			width: 100%;
			height: 100%;
			object-fit: cover;
		}
		.cg-layout-vertical-left {
			display: flex;
		}
		.cg-layout-vertical-left .cg-main-swiper {
			flex: calc(100% - 100px);
			margin-left: 5px;
			margin-right: 0px;
			transition: all 0.1s ease-in;
			order: 2;
		}
		.rtl .cg-layout-vertical-left .cg-main-swiper {
			margin-right: 5px;
			margin-left: 0;
		}
		.cg-layout-vertical-left .cg-thumb-swiper {
			flex: 100px;
			padding: 0px;
			order: 1;
		}
		.cg-layout-vertical-left .cg-thumb-swiper .swiper-wrapper {
			display: block;
		}
		.cg-layout-vertical-left .cg-thumb-swiper .swiper-slide {
			width: 100px;
			height: 100px;
			display: flex;
			align-items: center;
			justify-content: center;
			box-sizing: border-box;
			cursor: pointer;
			overflow: hidden;
			position: relative;
			padding: 2.5px 0 2.5px 5px;
		}
		.rtl .cg-layout-vertical-left .cg-thumb-swiper .swiper-slide {
			padding: 2.5px 5px 2.5px 0px;
		}
		.cg-layout-vertical-left .cg-thumb-swiper .swiper-slide:first-child {
			padding-top: 0px;
		}
		.cg-layout-vertical-left .cg-thumb-swiper .swiper-slide img {
			width: 100%;
			height: 100%;
			object-fit: cover;
		}
		.cg-layout-vertical-left.cgkit-one-slider .cg-main-swiper,
		.cg-layout-vertical-right.cgkit-one-slider .cg-main-swiper {
			flex: 100%;
			margin-left: 0px;
			margin-right: 0px;
		}
		.cg-layout-vertical-left.cgkit-one-slider .cg-thumb-swiper,
		.cg-layout-vertical-right.cgkit-one-slider .cg-thumb-swiper {
			flex: 0%;
			margin-left: 0px;
			margin-right: 0px;
		}
		.cg-layout-vertical-left.cgkit-one-slider .swiper-button-next,
		.cg-layout-vertical-right.cgkit-one-slider .swiper-button-prev {
			display: none;
		}
		.cg-layout-vertical-left.cgkit-vlayout-<?php echo esc_attr( $vertical_thumbs ); ?> .cg-thumb-swiper li,
		.cg-layout-vertical-right.cgkit-vlayout-<?php echo esc_attr( $vertical_thumbs ); ?> .cg-thumb-swiper li {
			display: none;
		}
		.cg-layout-vertical-left.cgkit-vlayout-<?php echo esc_attr( $vertical_thumbs ); ?> .cg-thumb-swiper li:nth-child(-n+<?php echo esc_attr( $vertical_thumbs ); ?> ),
		.cg-layout-vertical-right.cgkit-vlayout-<?php echo esc_attr( $vertical_thumbs ); ?> .cg-thumb-swiper li:nth-child(-n+<?php echo esc_attr( $vertical_thumbs ); ?> ) {
			display: flex;
		}
		.cg-layout-vertical-left.cgkit-mb10 .cg-main-swiper .swiper-slide,
		.cg-layout-vertical-right.cgkit-mb10 .cg-main-swiper .swiper-slide {
			align-items: flex-start;
		}
	}
	/* Lightbox cursor */
	.cg-lightbox-active .swiper-slide-imglink {
		cursor: zoom-in;
	}
	/* SVG Arrows */
	#commercegurus-pdp-gallery .swiper-button-next:after,
	#commercegurus-pdp-gallery .swiper-button-prev:after {
		content: "";
		font-family: inherit;
		font-size: inherit;
		width: 22px;
		height: 22px;
		background: #111;
		-webkit-mask-position: center;
		-webkit-mask-repeat: no-repeat;
		-webkit-mask-size: contain;
	}
	#commercegurus-pdp-gallery .swiper-button-next,
	#commercegurus-pdp-gallery .swiper-button-prev {
		width: 42px;
		height: 42px;
		margin-top: -21px;
		padding: 0;
		background: hsla(0, 0%, 100%, 0.75);
		transition: background 0.5s ease;
		border-radius: 0.25rem;
		cursor: pointer;
	}
	#commercegurus-pdp-gallery .swiper-button-next:focus,
	#commercegurus-pdp-gallery .swiper-button-prev:focus {
		outline: 0;
	}
	#commercegurus-pdp-gallery .swiper-button-next:focus-visible,
	#commercegurus-pdp-gallery .swiper-button-prev:focus-visible {
		outline: 0.25rem solid #2491ff;
		outline-offset: 0;
	}
	#commercegurus-pdp-gallery .swiper-button-next:hover,
	#commercegurus-pdp-gallery .swiper-button-prev:hover {
		background: #fff;
	}
	#commercegurus-pdp-gallery .swiper-button-prev:after,
	#commercegurus-pdp-gallery .swiper-button-next:after  {
		-webkit-mask-image: url("data:image/svg+xml;charset=utf8,%3Csvg width='24' height='24' viewBox='0 0 24 24' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M15 19L8 12L15 5' stroke='%234A5568' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
		mask-image: url("data:image/svg+xml;charset=utf8,%3Csvg width='24' height='24' viewBox='0 0 24 24' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M15 19L8 12L15 5' stroke='%234A5568' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
	}
	#commercegurus-pdp-gallery .swiper-button-next:after {
		-webkit-transform: scaleX(-1);
		transform: scaleX(-1);
	}
	<?php if ( ! $pdp_lightbox ) { ?>
	.swiper-slide-imglink {
		cursor: default;
	}
	<?php } ?>
	<?php if ( in_array( $cgkit_gallery_layout, array( 'horizontal', 'vertical-left', 'vertical-right' ), true ) ) { ?>
	.swiper-container.cg-main-swiper .swiper-wrapper .swiper-slide {
		display: none;
	}
	.swiper-container.cg-main-swiper .swiper-wrapper .swiper-slide:first-child {
		display: flex;
	}
	.swiper-container.cg-main-swiper.swiper-container-initialized .swiper-wrapper .swiper-slide {
		display: flex;
	}
	<?php } ?>
	@media (max-width: 770px) {
		.swiper-container.cg-main-swiper .swiper-wrapper .swiper-slide {
			display: none;
		}
		.swiper-container.cg-main-swiper .swiper-wrapper .swiper-slide:first-child {
			display: block;
		}
		.swiper-container.cg-main-swiper.swiper-container-initialized .swiper-wrapper .swiper-slide {
			display: flex;
		}
		.theme-shoptimizer #commercegurus-pdp-gallery-wrapper.ckit-mobile-pdp-gallery-active {
			margin-left: -1em;
			width: calc(100% + 2em);
			margin-bottom: 10px;
		}
		.ckit-mobile-pdp-gallery-active .swiper-container.cg-thumb-swiper {
			cursor: auto !important;
			height: inherit;
			margin-top: 5px;
			padding: 0 1em;
		}
		.ckit-mobile-pdp-gallery-active .swiper-container.cg-thumb-swiper .swiper-wrapper {
			display: inline-block;
			width: 100%;
			text-align: center;
			transform: none !important;
			line-height: 1em;
		}
		.ckit-mobile-pdp-gallery-active .swiper-container.cg-thumb-swiper .swiper-wrapper .swiper-slide {
			display: inline-block;
			background: #000;
			opacity: 0.2;
			cursor: auto;
			border-radius: 50%;
			margin: 1px 2px;
			max-width: 8px;
			height: 8px;
			padding: 0;
		}
		.ckit-mobile-pdp-gallery-active .swiper-container.cg-thumb-swiper .swiper-wrapper .swiper-slide.swiper-slide-thumb-active {
			background: #555;
			opacity: 1;
		}
		.ckit-mobile-pdp-gallery-active .swiper-button-next,
		.ckit-mobile-pdp-gallery-active .swiper-button-prev {
			display: none;
		}
		.ckit-mobile-pdp-gallery-active .swiper-container.cg-thumb-swiper .swiper-wrapper .swiper-slide > * {
			display: none !important;
		}
		.ckit-mobile-pdp-gallery-active.ckit-mobile-show-edge .swiper-wrapper.cg-psp-gallery {
			display: flex;
			gap: 10px;
		}
		.ckit-mobile-pdp-gallery-active.ckit-mobile-show-edge .cgkit-one-slider .swiper-wrapper.cg-psp-gallery {
			gap: 0px;
		}
		.ckit-mobile-pdp-gallery-active.ckit-mobile-show-edge .swiper-wrapper.cg-psp-gallery .swiper-slide {
			display: flex;
			flex: 0 0 <?php echo esc_attr( commercekit_gallery_pdp_showedge_percent() ); ?>%;
		}
		.ckit-mobile-pdp-gallery-active.ckit-mobile-show-edge .swiper-wrapper.cg-psp-gallery .swiper-slide img {
			display: block;
			max-width: 100%;
			max-height: 100%;
			height: auto !important;
		}
		.ckit-mobile-pdp-gallery-active.ckit-mobile-show-edge .cg-main-swiper .swiper-wrapper .swiper-slide {
			display: none;
		}
		.ckit-mobile-pdp-gallery-active.ckit-mobile-show-edge .cg-main-swiper .swiper-wrapper .swiper-slide:first-child,
		.ckit-mobile-pdp-gallery-active.ckit-mobile-show-edge .cg-main-swiper .swiper-wrapper .swiper-slide:nth-child(2) {
			display: flex;
		}
		.ckit-mobile-pdp-gallery-active.ckit-mobile-show-edge .cg-main-swiper.swiper-container-initialized .swiper-wrapper .swiper-slide {
			display: flex;
		}
		.ckit-mobile-pdp-gallery-active.ckit-mobile-show-edge .cg-main-swiper.swiper-container-initialized .swiper-wrapper.cg-psp-gallery {
			gap: 0;
		}
		.ckit-mobile-pdp-gallery-active.ckit-mobile-show-edge .cg-main-swiper.swiper-container-initialized .swiper-wrapper.cg-psp-gallery .swiper-slide {
			flex: none;
		}
		.ckit-mobile-pdp-gallery-active .cgkit-one-slider .swiper-wrapper.cg-psp-gallery .swiper-slide {
			flex: none;
			width: 100%;
			display: block !important;
		}
		.cgkit-iframe-wrap {
			position: relative;
		}
		.cgkit-iframe-wrap.cgkit-iframe-autoplay::before,
		.cg-lightbox-active .cgkit-iframe-embed::before {
			content: "";
			background: transparent;
			width: 100%;
			height: 100%;
			min-height: 1px;
			position: absolute;
			top: 0;
			left: 0;
			z-index: 99;
		}
	}
/* -- RTL -- */
.rtl #commercegurus-pdp-gallery .swiper-button-next:after {
	-webkit-transform:  none;
	transform: none;
}
.rtl #commercegurus-pdp-gallery .swiper-button-prev:after {
	transform: scale(-1, 1);
}
#commercegurus-pdp-gallery-wrapper.ckit-shortcode-gallery {
	max-width: 100%;
	width: 100%;
}
#commercegurus-pdp-gallery.cg-layout-vertical-left .swiper-button-next,
#commercegurus-pdp-gallery.cg-layout-vertical-left .swiper-button-prev,
#commercegurus-pdp-gallery.cg-layout-vertical-right .swiper-button-next,
#commercegurus-pdp-gallery.cg-layout-vertical-right .swiper-button-prev {
	width: 42px;
	height: 42px;
	margin-top: -21px;
	background: hsla(0, 0%, 100%, .75);
	transition: background .5s ease;
	border-radius: 0;
	cursor: pointer;
}
@media (min-width: 771px) {
	#commercegurus-pdp-gallery.cg-layout-vertical-left .cg-thumb-swiper .swiper-button-next,
	#commercegurus-pdp-gallery.cg-layout-vertical-left .cg-thumb-swiper .swiper-button-prev,
	#commercegurus-pdp-gallery.cg-layout-vertical-right .cg-thumb-swiper .swiper-button-next,
	#commercegurus-pdp-gallery.cg-layout-vertical-right .cg-thumb-swiper .swiper-button-prev {
		display: flex;
		top: 20px;
		left: 5px;
		width: calc(100% - 5px);
		height: 20px;
	}
	.rtl #commercegurus-pdp-gallery.cg-layout-vertical-left .cg-thumb-swiper .swiper-button-next,
	.rtl #commercegurus-pdp-gallery.cg-layout-vertical-left .cg-thumb-swiper .swiper-button-prev,
	.rtl #commercegurus-pdp-gallery.cg-layout-vertical-right .cg-thumb-swiper .swiper-button-next,
	.rtl #commercegurus-pdp-gallery.cg-layout-vertical-right .cg-thumb-swiper .swiper-button-prev {
		left: auto;
		right: 5px;
	}
	#commercegurus-pdp-gallery.cg-layout-vertical-left .cg-thumb-swiper .swiper-button-next,
	#commercegurus-pdp-gallery.cg-layout-vertical-right .cg-thumb-swiper .swiper-button-next {
		top: auto;
		bottom: 0;
	}
	#commercegurus-pdp-gallery.cg-layout-vertical-left .cg-thumb-swiper .swiper-button-next:after,
	#commercegurus-pdp-gallery.cg-layout-vertical-right .cg-thumb-swiper .swiper-button-next:after {
		transform: rotate(270deg);
		width: 18px;
	}
	#commercegurus-pdp-gallery.cg-layout-vertical-left .cg-thumb-swiper .swiper-button-prev:after,
	#commercegurus-pdp-gallery.cg-layout-vertical-right .cg-thumb-swiper .swiper-button-prev:after {
		transform: rotate(90deg);
		width: 18px;
	}
}
</style>
<div id="commercegurus-pdp-gallery-wrapper" class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $wrapper_classes ) ) ); ?>">

<?php do_action( 'commercekit_before_gallery' ); ?>

<div id="commercegurus-pdp-gallery" class="cg-layout-<?php echo esc_attr( $cgkit_gallery_layout ); ?> cgkit-mb10 cgkit-layout-<?php echo esc_attr( $pdp_thumbnails ); ?> cgkit-vlayout-<?php echo esc_attr( $vertical_thumbs ); ?> <?php echo esc_attr( $one_slider_css ); ?> <?php echo true === $pdp_lightbox ? 'cg-lightbox-active' : ''; ?>" data-layout-class="cg-layout-<?php echo esc_attr( $cgkit_gallery_layout ); ?>" <?php echo true === $is_default_variation ? 'style="visibility: hidden"' : ''; ?>>
	<div style="--swiper-navigation-color: #fff; --swiper-pagination-color: #fff" class="swiper-container cg-main-swiper">
		<ul class="swiper-wrapper cg-psp-gallery" itemscope itemtype="http://schema.org/ImageGallery">
			<?php
			if ( $post_thumbnail_id ) {
				if ( isset( $video_gallery[ $post_thumbnail_id ] ) && ! empty( $video_gallery[ $post_thumbnail_id ] ) ) {
					$html = commercegurus_get_product_gallery_video_html( $video_gallery[ $post_thumbnail_id ], true, $pdp_autoplay, $post_thumbnail_id ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
				} else {
					$html = commercegurus_get_main_gallery_image_html( $post_thumbnail_id, true, '', true );
				}
				$pdp_thub_count++;
			} else {
				$html = $placeholder_image;
			}
			echo $html; // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
			$attachment_ids = $product->get_gallery_image_ids();
			if ( $attachment_ids && $product->get_image_id() ) {
				$pdp_thub_count += count( $attachment_ids );
				$img_counter     = 1;
				foreach ( $attachment_ids as $attachment_id ) {
					if ( isset( $video_gallery[ $attachment_id ] ) && ! empty( $video_gallery[ $attachment_id ] ) ) {
						$main_video = $preload_count >= $img_counter ? true : false;
						echo commercegurus_get_product_gallery_video_html( $video_gallery[ $attachment_id ], $main_video, $pdp_autoplay, $attachment_id ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
					} else {
						if ( $preload_count >= $img_counter ) {
							echo commercegurus_get_main_gallery_image_html( $attachment_id, true, '', true ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
						} else {
							echo commercegurus_get_main_gallery_image_lazy_html( $attachment_id ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
						}
					}
					$img_counter++;
				}
			}
			?>
		</ul>
		<button class="swiper-button-next" aria-label="Next slide"></button>
		<button class="swiper-button-prev" aria-label="Previous slide"></button>
	</div>
	<div thumbsSlider="" class="swiper-container cg-thumb-swiper cg-thumbs-<?php echo esc_attr( $pdp_thumbnails ); ?> cg-m-thumbs-<?php echo esc_attr( $pdp_m_thumbs ); ?> cg-thumbs-count-<?php echo esc_attr( $pdp_thub_count ); ?>">
		<ul class="swiper-wrapper flex-control-nav" itemscope itemtype="http://schema.org/ImageGallery">
			<?php
			$html  = '';
			$index = 0;
			if ( $post_thumbnail_id ) {
				$css_class = '';
				if ( isset( $video_gallery[ $post_thumbnail_id ] ) && ! empty( $video_gallery[ $post_thumbnail_id ] ) ) {
					$css_class = 'pdp-video';
				}
				$html = commercegurus_get_thumbnail_gallery_image_html( $post_thumbnail_id, true, $index++, $css_class );
			}
			$attachment_ids = $product->get_gallery_image_ids();
			if ( $attachment_ids && $product->get_image_id() ) {
				foreach ( $attachment_ids as $attachment_id ) {
					$css_class = '';
					if ( isset( $video_gallery[ $attachment_id ] ) && ! empty( $video_gallery[ $attachment_id ] ) ) {
						$css_class = 'pdp-video';
					}
					$html .= commercegurus_get_thumbnail_gallery_image_html( $attachment_id, false, $index++, $css_class );
				}
			}
			if ( count( $attachment_ids ) ) {
				echo $html; // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
			}
			?>
		</ul>
		<?php if ( isset( $options['pdp_thumb_arrows'] ) && 1 === (int) $options['pdp_thumb_arrows'] ) { ?>
		<button class="swiper-button-next swiper-button-disabled" aria-label="Next slide"></button>
		<button class="swiper-button-prev swiper-button-disabled" aria-label="Previous slide"></button>
		<?php } ?>
	</div>

</div>
<?php do_action( 'commercekit_after_gallery' ); ?>
</div>
<div id="cgkit-pdp-gallery-outside" style="height:0px;"></div>
<?php if ( $pdp_lightbox ) { ?>
<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true" id="pswp">
	<div class="pswp__bg"></div>
	<div class="pswp__scroll-wrap">
		<div class="pswp__container">
			<div class="pswp__item"></div>
			<div class="pswp__item"></div>
			<div class="pswp__item"></div>
		</div>
		<div class="pswp__ui pswp__ui--hidden">
			<div class="pswp__top-bar">
				<div class="pswp__counter"></div>
				<button class="pswp__button pswp__button--close" aria-label="Close (Esc)"></button>
				<button class="pswp__button pswp__button--share" aria-label="Share"></button>
				<button class="pswp__button pswp__button--fs" aria-label="Toggle fullscreen"></button>
				<button class="pswp__button pswp__button--zoom" aria-label="Zoom in/out"></button>
				<div class="pswp__preloader">
					<div class="pswp__preloader__icn">
						<div class="pswp__preloader__cut">
							<div class="pswp__preloader__donut"></div>
						</div>
					</div>
				</div>
			</div>
		<div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
			<div class="pswp__share-tooltip"></div>
		</div>
		<button class="pswp__button pswp__button--arrow--left" aria-label="Previous (arrow left)"></button>
		<button class="pswp__button pswp__button--arrow--right" aria-label="Next (arrow right)">
		</button>
		<div class="pswp__caption">
			<div class="pswp__caption__center"></div>
		</div>
		</div>
	</div>
</div>
<?php } ?>
