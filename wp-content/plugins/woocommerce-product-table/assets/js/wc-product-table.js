( function( $, window, document, params, undefined ) {
	"use strict";

	const blockConfig = {
		message: null,
		overlayCSS: {
			background: '#fff',
			opacity: 0.6
		}
	};

	function addRowAttributes( $row ) {
		return function( key, value ) {
			if ( 'class' === key ) {
				$row.addClass( value );
			} else {
				$row.attr( key, value );
			}
		};
	}

	function appendFilterOptions( $select, options, depth ) {
		depth = ( typeof depth !== 'undefined' ) ? depth : 0;

		// Add each term to filter drop-down
		$.each( options, function( i, option ) {
			let name = option.name,
				value = 'slug' in option ? option.slug : name,
				pad = '';

			if ( depth ) {
				pad = Array( ( depth * 2 ) + 1 ).join( '\u00a0' ) + '\u2013\u00a0';
			}

			$select.append( '<option value="' + value + '">' + pad + name + '</option>' );

			if ( 'children' in option ) {
				appendFilterOptions( $select, option.children, depth + 1 );
			}
		} );
	}

	// Reduce the filter options to only those which are in the required list or are parents of such items.
	function reduceFilterOptions( options, required ) {
		let option = null,
			result = JSON.parse( JSON.stringify( options ) ); // clone the options, so the original is unmodified.

		for ( let i = result.length - 1; i >= 0; i-- ) {
			option = result[i];

			if ( option.hasOwnProperty( 'children' ) ) {
				option.children = reduceFilterOptions( option.children, required );

				if ( 0 === option.children.length ) {
					// No children left, so delete property from term.
					delete option.children;
				}
			}

			// Keep the term if it's found in required or it has children.
			if ( -1 === required.indexOf( option.slug ) && ! option.hasOwnProperty( 'children' ) ) {
				result.splice( i, 1 );
			}
		}

		return result;
	}

	function setFilterOptions( $select, heading, options ) {
		// Add the default option to the list.
		let allOptions = [ { slug: "", name: heading } ].concat( options );

		// Add the options to the filter.
		$select.empty();

		// Add the <option> elements to filter
		appendFilterOptions( $select, allOptions );
	}

	function flattenObjectArray( arr, childProp ) {
		let result = [];

		for ( let i = 0; i < arr.length; i++ ) {
			if ( typeof arr[i] !== 'object' ) {
				continue;
			}
			result.push( arr[i] );

			for ( let prop in arr[i] ) {
				if ( prop === childProp ) {
					Array.prototype.push.apply( result, flattenObjectArray( arr[i][prop], childProp ) );
					delete arr[i][prop];
				}
			}
		}

		return result;
	}

	function getCurrentUrlWithoutFilters() {
		let url = window.location.href.split( '?' )[0];

		if ( window.location.search ) {
			let params = window.location.search.substring( 1 ).split( '&' ),
				newParams = [];

			for ( let i = 0; i < params.length; i++ ) {
				if ( params[i].indexOf( 'min_price' ) === -1 &&
					params[i].indexOf( 'max_price' ) === -1 &&
					params[i].indexOf( 'filter_' ) === -1 &&
					params[i].indexOf( 'rating_filter' ) === -1 &&
					params[i].indexOf( 'query_type' ) === -1
				) {
					newParams.push( params[i] );
				}
			}

			if ( newParams.length ) {
				url += '?' + newParams.join( '&' );
			}
		}

		return url;
	}

	function initContent( $el ) {
		initMedia( $el );
		initVariations( $el );
		initProductAddons( $el );
	}

	function initMedia( $el ) {
		if ( ! $el || ! $el.length ) {
			return;
		}

		// Replace our custom class names with the correct ones before running the media init functions.
		$el.find( '.wcpt-playlist' ).addClass( 'wp-playlist' );
		$el.find( '.wcpt-video-shortcode' ).addClass( 'wp-video-shortcode' );
		$el.find( '.wcpt-audio-shortcode' ).addClass( 'wp-audio-shortcode' );

		if ( typeof WPPlaylistView !== 'undefined' ) {
			// Initialise audio and video playlists
			$el.find( '.wp-playlist' ).filter( function() {
				return $( '.mejs-container', this ).length === 0; // exclude playlists already initialized
			} ).each( function() {
				return new WPPlaylistView( { el: this } );
			} );
		}

		// Initialise audio and video shortcodes.
		if ( 'wp' in window && 'mediaelement' in window.wp ) {
			$( window.wp.mediaelement.initialize );
		}

		// Run fitVids to ensure videos in table have correct proportions.
		if ( $.fn.fitVids ) {
			$el.fitVids();
		}
	}

	function initProductAddons( $el ) {
		// Get all visible addon containers in the table.
		let $addonsContainer = $el.find( '.wc-pao-addons-container' );

		if ( ! $addonsContainer.length ) {
			return;
		}

		// Initialise addons for all visible products by triggering the 'quick-view-displayed' event.
		$el.trigger( 'quick-view-displayed' );

		// Required checkbox logic - copied from Product Addons as there's no way to initialise it after page load in addons.js.
		$addonsContainer.find( '.wc-pao-addon-checkbox-group-required' )
			.each( function() {
				let checkboxesGroup = this;

				/*
				 * Require at least one checkbox in a required group to be checked.
				 * If at least one is checked then remove the required attribute from all of the group checkboxes.
				 * With all of the required attributes removed the form can be submitted even if some of the checkboxes are un-checked.
				 *
				 * This requires HTML5 to work.
				 */
				$( checkboxesGroup )
					.find( '.wc-pao-addon-checkbox' )
					.change( function() {
						if ( $( checkboxesGroup ).find( 'input:checked' ).length > 0 ) {
							$( checkboxesGroup ).removeClass(
								'wc-pao-addon-checkbox-required-error'
							);
							$( checkboxesGroup )
								.find( 'input' )
								.each( function() {
									$( this ).attr( 'required', false );
								} );
						} else {
							$( checkboxesGroup ).addClass(
								'wc-pao-addon-checkbox-required-error'
							);
							$( checkboxesGroup )
								.find( 'input' )
								.each( function() {
									$( this ).attr( 'required', true );
								} );
						}
					} );
			} );
	}

	function initVariations( $el ) {
		if ( ! $el || ! $el.length || ( typeof $.fn.wc_variation_form !== 'function' ) || ( typeof wc_add_to_cart_variation_params === 'undefined' ) ) {
			return;
		}

		$el.find( '.wpt_variations_form' ).filter( function() {
			return ! $( this ).hasClass( 'initialised' ); // exclude variations already initialized
		} ).each( function() {
			$( this ).wc_variation_form();
		} );
	}

	/*
	 * A renderer for $.fn.DataTables.Responsive to display hidden data when using responsive child rows.
	 *
	 * @see https://datatables.net/reference/option/responsive.details.renderer
	 */
	function responsiveRendererListHidden() {
		return function( api, rowIdx, columns ) {
			let rowClass = api.row( rowIdx ).node().className;

			let data = $.map( columns, function( col ) {
				let klass = col.className ? 'class="' + col.className + '"' : '';

				return col.hidden ?
					'<li ' + klass + ' data-dtr-index="' + col.columnIndex + '" data-dt-row="' + col.rowIndex + '" data-dt-column="' + col.columnIndex + '">' +
					'<span class="dtr-title">' + col.title + '</span> ' +
					'<span class="dtr-data">' + col.data + '</span>' +
					'</li>' :
					'';
			} ).join( '' );

			return data ?
				$( '<ul data-dtr-index="' + rowIdx + '" class="dtr-details ' + rowClass + '" />' ).append( data ) :
				false;
		}
	}

	/*
	 * A renderer for $.fn.DataTables.Responsive to display all visible content for a row when using modal responsive display.
	 *
	 * @see https://datatables.net/reference/option/responsive.details.renderer
	 */
	function responsiveRendererAllVisible( options ) {
		options = $.extend( {
			tableClass: ''
		}, options );

		return function( api, rowIdx, columns ) {
			let rowClass = api.row( rowIdx ).node().className;

			let innerData = $.map( columns, function( col ) {
				// Bail if column data is hidden.
				if ( ! api.column( col.columnIndex ).visible() ) {
					return '';
				}

				let klass = col.className ? 'class="' + col.className + '"' : '';

				return '<li ' + klass + ' data-dtr-index="' + col.columnIndex + '" data-dt-row="' + col.rowIndex + '" data-dt-column="' + col.columnIndex + '">' +
					'<span class="dtr-title">' + col.title + '</span> ' +
					'<span class="dtr-data">' + col.data + '</span>' +
					'</li>';
			} ).join( '' );

			let data = '<ul data-dtr-index="' + rowIdx + '" class="dtr-details ' + rowClass + '" >' + innerData + '</ul>';
			let $modal = $( '<div class="' + options.tableClass + '" />' ).append( data );

			initContent( $modal );
			return $modal;
		};
	}

	function selectWooEnabled() {
		return ( 'selectWoo' in $.fn ) && params.enable_select2;
	}

	function setMultiCartMessage( message, $multiCartForm ) {
		$multiCartForm.closest( '.wc-product-table-controls' ).append( $( '<div class="multi-cart-message"></div>' ).append( message ) );
	}

	function setVariationImage( $form, variation ) {
		let $productRow = $form.closest( 'tr' );

		// If variations form is in a parent row, check for image in child row and vice versa
		if ( $productRow.hasClass( 'parent' ) ) {
			$productRow = $productRow.add( $productRow.next( '.child' ) );
		} else if ( $productRow.hasClass( 'child' ) ) {
			$productRow = $productRow.add( $productRow.prev( '.parent' ) );
		}

		let $productImg = $productRow.find( 'img.product-thumbnail' ).eq( 0 );

		if ( ! $productImg.length ) {
			return;
		}

		let props = false,
			$productGalleryWrap = $productImg.closest( '.woocommerce-product-gallery__image', $productRow ).eq( 0 ),
			$productGalleryLink = false;

		if ( $productGalleryWrap.length ) {
			$productGalleryLink = $productGalleryWrap.find( 'a' ).eq( 0 );
		}

		if ( variation ) {
			if ( 'image' in variation ) {
				props = variation.image;
			} else if ( 'image_src' in variation ) {
				// Back compat: different object structure used in WC < 3.0
				props = {
					src: variation.image_src,
					src_w: '',
					src_h: '',
					full_src: variation.image_link,
					full_src_w: '',
					full_src_h: '',
					thumb_src: variation.image_src,
					thumb_src_w: '',
					thumb_src_h: '',
					srcset: variation.image_srcset,
					sizes: variation.image_sizes,
					title: variation.image_title,
					alt: variation.image_alt,
					caption: variation.image_caption
				};
			}
		}

		if ( props && props.thumb_src.length ) {
			$productImg.wc_set_variation_attr( 'src', props.thumb_src );
			$productImg.wc_set_variation_attr( 'title', props.title );
			$productImg.wc_set_variation_attr( 'alt', props.alt );
			$productImg.wc_set_variation_attr( 'data-src', props.full_src );
			$productImg.wc_set_variation_attr( 'data-caption', props.caption );
			$productImg.wc_set_variation_attr( 'data-large_image', props.full_src );
			$productImg.wc_set_variation_attr( 'data-large_image_width', props.full_src_w );
			$productImg.wc_set_variation_attr( 'data-large_image_height', props.full_src_h );

			if ( $productGalleryWrap.length ) {
				$productGalleryWrap.wc_set_variation_attr( 'data-thumb', props.thumb_src );
			}

			if ( $productGalleryLink.length ) {
				$productGalleryLink.wc_set_variation_attr( 'href', props.full_src );
			}
		} else {
			$productImg.wc_reset_variation_attr( 'src' );
			$productImg.wc_reset_variation_attr( 'width' );
			$productImg.wc_reset_variation_attr( 'height' );
			$productImg.wc_reset_variation_attr( 'title' );
			$productImg.wc_reset_variation_attr( 'alt' );
			$productImg.wc_reset_variation_attr( 'data-src' );
			$productImg.wc_reset_variation_attr( 'data-caption' );
			$productImg.wc_reset_variation_attr( 'data-large_image' );
			$productImg.wc_reset_variation_attr( 'data-large_image_width' );
			$productImg.wc_reset_variation_attr( 'data-large_image_height' );

			if ( $productGalleryWrap.length ) {
				$productGalleryWrap.wc_reset_variation_attr( 'data-thumb' );
			}

			if ( $productGalleryLink.length ) {
				$productGalleryLink.wc_reset_variation_attr( 'href' );
			}
		}
	}

	function updateMultiHiddenField( field, val, $multiCheck ) {
		if ( ! field || ! $multiCheck || ! $multiCheck.length ) {
			return;
		}

		// Find the multi-cart input which corresponds to the changed cart input
		let $multiCartInput = $multiCheck.find( 'input[data-input-name="' + field + '"]' );

		if ( $multiCartInput.length ) {
			// Update the hidden input to match the cart form value
			$multiCartInput.val( val );
		}
	}

	/******************************************
	 * PRODUCTTABLE PROTOTYPE
	 ******************************************/

	let ProductTable = function( $table ) {
		// Properties
		this.$table = $table;
		this.id = $table.attr( 'id' );
		this.dataTable = null;
		this.config = null;
		this.initialState = null;
		this.ajaxData = [];
		this.hasAdminBar = $( '#wpadminbar' ).length > 0;

		this.$filters = [];
		this.$tableWrapper = [];
		this.$pagination = [];
		this.$tableControls = [];

		// Register events
		$table
			.on( 'draw.dt', { table: this }, onDraw )
			.on( 'init.dt', { table: this }, onInit )
			.on( 'page.dt', { table: this }, onPage )
			.on( 'processing.dt', { table: this }, onProcessing )
			.on( 'responsive-display.dt', { table: this }, onResponsiveDisplay )
			.on( 'search.dt', { table: this }, onSearch )
			.on( 'stateLoadParams.dt', { table: this }, onStateLoadParams )
			.on( 'xhr.dt', { table: this }, onAjaxLoad )
			.on( 'submit.wcpt', '.cart', { table: this }, onAddToCart );

		$( window ).on( 'load.wcpt', { table: this }, onWindowLoad );

		// Show the table - loading class removed on init.dt
		$table.addClass( 'loading' ).css( 'visibility', 'visible' );
	};

	ProductTable.prototype.buildConfig = function() {
		let config = {
			retrieve: true, // so subsequent calls to DataTable() return the same API instance
			responsive: $.fn.dataTable.Responsive.defaults,
			orderMulti: false, // disable ordering by multiple columns at once
			stateSave: true,
			language: params.language
		};

		// Get config for this table instance.
		let tableConfig = this.$table.data( 'config' );

		if ( tableConfig ) {
			// We need to do deep copy for the 'language' property to be merged correctly.
			config = $.extend( true, {}, config, tableConfig );
		}

		// Build AJAX data for loading products.
		let ajaxData = {
			table_id: this.id,
			action: 'wcpt_load_products'
		};

		// If query string present, add parameters to data to send (e.g. filter attributes)
		// .substring(1) removes the '?' at the beginning
		if ( window.location.search ) {
			let vars = window.location.search.substring( 1 ).split( '&' );

			for ( let i = 0; i < vars.length; i++ ) {
				let pair = vars[i].split( '=', 2 );

				if ( 2 === pair.length ) {
					ajaxData[pair[0]] = pair[1].replace( /%2C/g, ',' );
				}
			}
		}

		// If English language, replace 'products' with 'product' when there's only 1 result.
		if ( 'info' in config.language && -1 !== config.language.info.indexOf( 'products' ) ) {
			config.infoCallback = function( settings, start, end, max, total, pre ) {
				if ( pre && 1 === total ) {
					return pre.replace( 'products', 'product' );
				}
				return pre;
			};
		}

		// Config for server-side processing
		if ( config.serverSide && 'ajax_url' in params ) {
			config.deferRender = true;
			config.ajax = {
				url: params.ajax_url,
				type: 'POST',
				data: ajaxData,
				xhrFields: {
					withCredentials: true
				}
			};
		}

		// Set responsive display and renderer functions
		if ( ( typeof config.responsive === 'object' ) && 'details' in config.responsive && 'display' in config.responsive.details ) {
			if ( 'child_row' === config.responsive.details.display ) {
				config.responsive.details.display = $.fn.dataTable.Responsive.display.childRow;
				config.responsive.details.renderer = responsiveRendererListHidden();
			} else if ( 'child_row_visible' === config.responsive.details.display ) {
				config.responsive.details.display = $.fn.dataTable.Responsive.display.childRowImmediate;
				config.responsive.details.renderer = responsiveRendererListHidden();
			} else if ( 'modal' === config.responsive.details.display ) {
				config.responsive.details.display = $.fn.dataTable.Responsive.display.modal();
				config.responsive.details.renderer = responsiveRendererAllVisible( { tableClass: 'wc-product-table' } );
			}
		}

		// Legacy config for language (we now use Gettext for translation).
		if ( 'lang_url' in params ) {
			config.language = { url: params.lang_url };
		}

		return config;
	};

	ProductTable.prototype.checkFormAttributeSupport = function( $form ) {
		let table = this;

		// Check for support for HTML5 form attribute
		if ( ! $form.is( 'form' ) ) {
			return table;
		}

		if ( ! $form[0] || ! ( 'elements' in $form[0] ) ) {
			return table;
		}

		if ( $form[0].elements.length > 2 ) {
			// If we have more than 2 form elements (i.e. the form button and hidden 'multi_cart' field)
			// then HTML5 form attribute must be supported natively in browser, so no need to continue.
			return table;
		}

		table.getDataTable()
			.$( '.multi-cart-check input[type="checkbox"]' ) // get all multi checkboxes in table
			.add( table.$table.find( 'td.child .multi-cart-check input[type="checkbox"]' ) ) // including checkboxes in responsive child rows
			.filter( ':checked:enabled' ) // just the selected and enabled products
			.each( function() {
				// Then add all multi fields for checked products to the parent multi-cart form
				$( this ).clone().appendTo( $form );
				$( this ).siblings( 'input[type="hidden"]' ).clone().appendTo( $form );
			} );

		return table;
	};

	ProductTable.prototype.getDataTable = function() {
		if ( ! this.dataTable ) {
			this.init();
		}

		return this.dataTable;
	};

	ProductTable.prototype.init = function() {
		let table = this;

		table.$table.trigger( 'preInit.wcpt', [ table ] );

		// Initialize DataTables instance.
		table.config = table.buildConfig();
		table.dataTable = table.$table.DataTable( table.config );

		return table;
	};

	ProductTable.prototype.initFilters = function() {
		let table = this,
			filtersData = table.$table.data( 'filters' );

		if ( ! filtersData ) {
			return table;
		}

		let dataTable = table.getDataTable(),
			$filtersWrap = $( '<div class="wc-product-table-select-filters" id="' + table.id + '_select_filters" />' ),
			savedColumnSearches = {},
			filtersAdded = 0;

		if ( 'filterBy' in params.language && params.language.filterBy ) {
			$filtersWrap.append( '<label class="filter-label">' + params.language.filterBy + '</label>' );
		}

		// Setup initial state (if using).
		if ( table.initialState && 'columns' in table.initialState ) {
			// If we have an initial state, convert to a more workable object of the form: { 'column_name': 'previous search' }
			for ( let i = 0; i < table.initialState.columns.length; i++ ) {
				if ( ! ( 'search' in table.initialState.columns[i] ) || ! table.initialState.columns[i].search.search ) {
					continue;
				}

				if ( ( 0 === dataTable.column( i ).length ) || typeof dataTable.column( i ).dataSrc() !== 'string' ) {
					continue;
				}

				let search = table.initialState.columns[i].search.search;

				if ( search && table.initialState.columns[i].search.regex ) {
					search = search.replace( '(^|, )', '' ).replace( '(, |$)', '' );
				}

				// Bug in DataTables - column().name() not working so we need to pull name from header node
				savedColumnSearches[$( dataTable.column( i ).header() ).data( 'name' )] = search;
			}
		}

		// Build the filters
		for ( const tax in filtersData ) {
			let filterData = filtersData[tax];

			// Don't add the filter if there are no items.
			if ( ! ( 'terms' in filterData ) || 0 === filterData.terms.length ) {
				continue;
			}

			let selectAtts = {
				'name': 'wcpt_filter_' + tax,
				'data-tax': tax,
				'data-column': filterData.column,
				'data-search-column': filterData.searchColumn,
				'aria-label': filterData.heading,
				'data-placeholder': filterData.heading
			};

			if ( filterData.class ) {
				selectAtts['class'] = filterData.class;
			}

			// Create the <select> element.
			let $select = $( '<select/>' ).attr( selectAtts );

			// Append the options.
			setFilterOptions( $select, filterData.heading, filterData.terms );

			// Determine the initial filter selection (if any)
			let value = '';

			if ( 'selected' in filterData && $select.children( 'option[value="' + filterData.selected + '"]' ).length ) {
				// Set selection based on active filter widget
				value = filterData.selected;
			} else if ( filterData.column in savedColumnSearches ) {
				// Set selection based on previous saved table state
				let prevSearch = savedColumnSearches[filterData.column];

				// Flatten terms to make searching through them easier
				let flatTerms = flattenObjectArray( filterData.terms, 'children' );

				// Search the filter terms for the previous search value, which will be the <option> text rather than its value.
				// We could use Array.find() here if browser support was better.
				$.each( flatTerms, function( i, term ) {
					if ( 'name' in term && term.name === prevSearch ) {
						value = 'slug' in term ? term.slug : term.name;
						return false; // break the $.each loop
					}
				} );
			}

			// Set the initial value and append select to wrapper
			$select
				.val( value )
				.on( 'change.wcpt', { table: table }, onFilterChange )
				.appendTo( $filtersWrap );

			filtersAdded++;
		} // foreach filter

		// Add filters to table - before search box if present, otherwise as first element above table
		if ( filtersAdded > 0 ) {
			// Add filters to table
			let $searchBox = table.$tableControls.find( '.dataTables_filter' );

			if ( $searchBox.length ) {
				$filtersWrap.prependTo( $searchBox.closest( '.wc-product-table-controls' ) );
			} else {
				$filtersWrap.prependTo( table.$tableControls.filter( '.wc-product-table-above' ) );
			}
		}

		// Store filters here as we use this when searching columns.
		table.$filters = table.$tableControls.find( '.wc-product-table-select-filters select' );

		// Update filters so only applicable options are shown (for standard loading).
		table.updateFilterOptions( table.$filters );

		return table;
	};

	ProductTable.prototype.initMultiCart = function() {
		let table = this;

		if ( ! table.config.multiAddToCart || ! table.$tableWrapper.length ) {
			return table;
		}

		if ( ! ( 'multiCartButton' in params.language ) ) {
			params.language.multiCartButton = 'Add to cart';
		}

		// Create the multi cart form and append above/below table
		let $multiForm =
			$( '<form class="multi-cart-form" method="post" />' )
				.append( '<input type="submit" class="' + params.multi_cart_button_class + '" value="' + params.language.multiCartButton + '" />' )
				.append( '<input type="hidden" name="multi_cart" value="1" />' )
				.on( 'submit.wcpt', { table: table }, onAddToCartMulti );

		$multiForm = $( '<div class="wc-product-table-multi-form" />' ).append( $multiForm );

		if ( $.inArray( table.config.multiCartLocation, [ 'top', 'both' ] ) > -1 ) {
			table.$tableControls.filter( '.wc-product-table-above' ).append( $multiForm );
		}

		if ( $.inArray( table.config.multiCartLocation, [ 'bottom', 'both' ] ) > -1 ) {
			table.$tableControls.filter( '.wc-product-table-below' ).append( $multiForm.clone( true ) );
		}

		table.registerMultiCartEvents();
		return table;
	};

	ProductTable.prototype.initPhotoswipe = function() {
		let table = this;

		if ( typeof PhotoSwipe === 'undefined' || typeof PhotoSwipeUI_Default === 'undefined' ) {
			return table;
		}

		table.$table
			.children( 'tbody' )
			.off( 'click.wcpt', '.woocommerce-product-gallery__image a' )
			.on( 'click.wcpt', '.woocommerce-product-gallery__image a', onOpenPhotoswipe );

		return table;
	};

	ProductTable.prototype.initQuickViewPro = function() {
		let table = this;

		if ( ! window.WCQuickViewPro ) {
			return table;
		}

		// If links should open in Quick View, register events.
		if ( params.open_links_in_quick_view ) {
			// Handle clicks on single product links.
			table.$table.on( 'click.wcpt', '.single-product-link', WCQuickViewPro.handleQuickViewClick );

			// Handle clicks on loop read more buttons (e.g. 'Select options', 'View products', etc).
			table.$table.on( 'click.wcpt', '.add-to-cart-wrapper a[data-product_id]', function( event ) {
				// Don't open quick view for external products.
				if ( $( this ).hasClass( 'product_type_external' ) ) {
					return true;
				}

				WCQuickViewPro.handleQuickViewClick( event );
				return false;
			} );
		}

		return table;
	};

	ProductTable.prototype.initResetButton = function() {
		let table = this;

		if ( ! table.config.resetButton || ! ( 'resetButton' in params.language ) ) {
			return table;
		}

		let $resetButton =
			$( '<div class="wc-product-table-reset"><a class="reset" href="#">' + params.language.resetButton + '</a></div>' )
				.on( 'click.wcpt', 'a', { table: table }, onReset );

		// Append reset button
		let $firstChild = table.$tableControls
			.filter( '.wc-product-table-above' )
			.children( '.wc-product-table-select-filters, .dataTables_length, .dataTables_filter' )
			.eq( 0 );

		if ( $firstChild.length ) {
			$firstChild.append( $resetButton );
		} else {
			table.$tableControls
				.filter( '.wc-product-table-above' )
				.prepend( $resetButton );
		}

		return table;
	};

	ProductTable.prototype.initSearchOnClick = function() {
		let table = this;

		if ( table.config.clickFilter ) {
			// 'search_on_click' - add click handler for relevant links. When clicked, the table will filter by the link text.
			table.$table.on( 'click.wcpt', 'a[data-column]', { table: table }, onClickToSearch );
		}

		return table;
	};

	ProductTable.prototype.initSelectWoo = function() {
		let table = this;

		if ( ! selectWooEnabled() ) {
			return table;
		}

		let selectWooOpts = {
			dropdownCssClass: 'wc-product-table-dropdown'
		};

		// Initialize selectWoo for search filters.
		if ( table.$filters.length ) {

			// Maybe adjust width of filters prior to initializing select2.
			table.$filters.each( function() {
				if ( $( this ).innerWidth() === $( this ).width() ) {
					// No padding on select element (e.g. Safari) so we adjust the width upwards slightly to ensure
					// select2 element is wide enough for select items.
					$( this ).width( $( this ).width() + 22 );
				}
			} );

			table.$filters.selectWoo(
				Object.assign( selectWooOpts, { minimumResultsForSearch: 7 } )
			);
		}

		// Initialize selectWoo for page length - minimumResultsForSearch of -1 disables the search box.
		table.$tableControls.find( '.dataTables_length select' ).selectWoo(
			Object.assign( selectWooOpts, { minimumResultsForSearch: -1 } )
		);

		return table;
	};

	ProductTable.prototype.processAjaxData = function() {
		let table = this;

		if ( ! table.config.serverSide || ! table.ajaxData.length ) {
			return table;
		}

		let $rows = table.$table.find( '> tbody > tr' );

		// Add row attributes to each row in table
		if ( $rows.length ) {
			for ( let i = 0; i < table.ajaxData.length; i++ ) {
				if ( '__attributes' in table.ajaxData[i] && $rows.eq( i ).length ) {
					$.each( table.ajaxData[i].__attributes, addRowAttributes( $rows.eq( i ) ) );
				}
			}
		}

		return table;
	};

	ProductTable.prototype.registerMultiCartEvents = function() {
		let table = this;

		if ( ! table.config.multiAddToCart ) {
			return table;
		}

		// Quantity changed - update in multi-cart fields.
		table.$table.on( 'change', '.cart .qty', function() {
			let $cart = $( this ).closest( '.cart' ),
				$multiFields = $cart.siblings( '.multi-cart-check' ),
				$multiCheckbox = $multiFields.children( 'input[type="checkbox"]' ),
				qtyFloat = parseFloat( $( this ).val() );

			if ( ! isNaN( qtyFloat ) && ! $multiCheckbox.prop( 'disabled' ) ) {
				if ( 0 === qtyFloat ) {
					// Untick multi checkbox if quantity is 0.
					$multiCheckbox.prop( 'checked', false );
				} else {
					let multiQtyFloat = parseFloat( $multiFields.children( 'input[data-input-name="quantity"]' ).val() );

					if ( ! isNaN( multiQtyFloat ) && qtyFloat !== multiQtyFloat ) {
						// Tick multi checkbox if quantity has changed.
						$multiCheckbox.prop( 'checked', true );
					}
				}
			}

			// Update quantity field
			updateMultiHiddenField( 'quantity', qtyFloat, $multiFields );
		} );

		// Variation found - update variation ID and attributes in multi-cart fields.
		table.$table.on( 'found_variation', '.wpt_variations_form', function( event, variation ) {
			let $cart = $( this ),
				$multiFields = $cart.siblings( '.multi-cart-check' );

			// Set variation ID.
			if ( 'variation_id' in variation ) {
				updateMultiHiddenField( 'variation_id', variation.variation_id, $multiFields );
			}

			// Set variation attributes - we get values from the selects, as the 'variation' object passed to this event is unreliable.
			$( '.variations select', $cart ).each( function() {
				let fieldName = $( this ).prop( 'name' );
				updateMultiHiddenField( fieldName, $( this ).val(), $multiFields );
			} );
		} );

		// Enable or disable multi checkbox based on whether current variation is purchasable.
		table.$table.on( 'show_variation', '.wpt_variations_form', function( event, variation, purchasable ) {
			let $cart = $( this );

			// Only update checkbox after the variations form has initialised. This ensures we only update in response to
			// user input and prevents checking the box during initial load when a default variation is set.
			if ( ! $cart.hasClass( 'initialised' ) ) {
				return true;
			}

			let $checkbox = $cart.siblings( '.multi-cart-check' ).children( 'input[type="checkbox"]' );

			if ( purchasable ) {
				$checkbox.prop( { disabled: false, checked: true } );
			} else {
				$checkbox.prop( { disabled: true, checked: false } );
			}
		} );

		// Disable multi checkbox on variation hide.
		table.$table.on( 'hide_variation', '.wpt_variations_form', function() {
			let $multiFields = $( this ).siblings( '.multi-cart-check' );

			$multiFields
				.children( 'input[type="checkbox"]' )
				.prop( { disabled: true, checked: false } );

			updateMultiHiddenField( 'variation_id', '', $multiFields );
		} );

		// Product Addons - update multi cart fields when updated.
		table.$table.on( 'updated_addons', function( event ) {
			let $cart = $( event.target ),
				$multiFields = $cart.siblings( '.multi-cart-check' );

			if ( ! $multiFields.length || ! $cart.is( '.cart' ) ) {
				return;
			}

			// Loop through each addon and update the corresponding hidden field in the .multi-cart-check section.
			$cart.find( '.wc-pao-addon-field' ).each( function() {
				let $input = $( this ),
					val = $input.val(),
					inputName = $input.prop( 'name' );

				if ( ! inputName || 'quantity' === inputName ) { // quantity change handled above.
					return;
				}

				// For checkbox addons the input names are arrays, e.g. addon-check[].
				// We need to add an integer index to the name to make sure we update the correct hidden field
				if ( 'checkbox' === $input.attr( 'type' ) ) {
					// Pull the index from the parent wrapper class (e.g. wc-pao-addon-123-collection-0)
					let match = $input.closest( '.form-row', $cart.get( 0 ) ).attr( 'class' ).match( /(wc-pao-addon-).+?-(\d+)($|\s)/ );

					if ( match && 4 === match.length ) {
						// match[2] is the index of the checkbox within the checkbox group.
						inputName = inputName.replace( '[]', '[' + match[2] + ']' );
					}

					// Clear value if unchecked.
					if ( ! $input.prop( 'checked' ) ) {
						val = '';
					}

					updateMultiHiddenField( inputName, val, $multiFields );
				} else if ( 'radio' === $input.attr( 'type' ) ) {
					if ( $input.prop( 'checked' ) ) {
						// Replace [] at the end of the input name, so 'radio-field[]' becomes 'radio-field'.
						// Needed to match hidden field in multi cart section.
						inputName = inputName.replace( /\[\]$/, '' );

						updateMultiHiddenField( inputName, val, $multiFields );
					}
				} else {
					updateMultiHiddenField( inputName, val, $multiFields );
				}

			} ); // each addon field
		} ); // on updated_addons

		return table;
	};

	ProductTable.prototype.registerVariationEvents = function() {
		let table = this;

		if ( 'dropdown' !== this.config.variations ) {
			return table;
		}

		// Add class when form initialised so we can filter these out later
		table.$table.on( 'wc_variation_form', '.wpt_variations_form', function() {
			$( this ).addClass( 'initialised' );
		} );

		// Update image column when variation found
		table.$table.on( 'found_variation', '.wpt_variations_form', function( event, variation ) {
			setVariationImage( $( this ), variation );
		} );

		// Show variation and enable cart button
		table.$table.on( 'show_variation', '.wpt_variations_form', function( event, variation, purchasable ) {
			$( this ).find( '.added_to_cart' ).remove();
			$( this ).find( '.single_add_to_cart_button' ).removeClass( 'added disabled' );
			$( this ).find( '.single_variation' ).slideDown( 200 );
		} );

		// Hide variation and disable cart button
		table.$table.on( 'hide_variation', '.wpt_variations_form', function() {
			$( this ).find( '.single_add_to_cart_button' ).addClass( 'disabled' );
			$( this ).find( '.single_variation' ).slideUp( 200 );
		} );

		// Reset the variation image
		table.$table.on( 'reset_image', '.wpt_variations_form', function() {
			setVariationImage( $( this ), false );
		} );

		return table;
	};

	ProductTable.prototype.resetMultiCartCheckboxes = function( $cart ) {
		let table = this,
			$multiFields;

		if ( $cart && $cart.length && $cart.is( '.cart' ) ) {
			$multiFields = $cart.siblings( '.multi-cart-check' );
		} else {
			$multiFields = table.getDataTable()
				.$( '.multi-cart-check' )
				.add( table.$table.find( 'tr.child .multi-cart-check' ) );
		}

		$multiFields
			.children( 'input[type="checkbox"]' )
			.prop( 'checked', false );

		return table;
	};

	ProductTable.prototype.resetQuantities = function( $cart ) {
		let table = this;

		// If no cart given, reset all visible cart forms in table.
		if ( ! $cart || ! $cart.length ) {
			$cart = table.getDataTable()
				.$( '.cart' )
				.add( table.$table.find( 'tr.child .cart' ) );
		}

		$cart.find( 'input[name="quantity"]' ).val( function( index, value ) {
			if ( $.isNumeric( $( this ).attr( 'value' ) ) ) {
				value = $( this ).attr( 'value' );
			}
			return value;
		} ).trigger( 'change' );

		return table;
	};

	ProductTable.prototype.resetProductAddons = function( $cart ) {
		let table = this;

		// If no cart given, reset all visible cart forms in table.
		if ( ! $cart || ! $cart.length ) {
			$cart = table.getDataTable()
				.$( '.cart' )
				.add( table.$table.find( 'tr.child .cart' ) );
		}

		let $addons = $cart.find( '.wc-pao-addon, .product-addon' );

		$addons.find( 'select, textarea' ).val( '' );

		$addons.find( 'input' ).each( function() {
			if ( 'radio' === $( this ).attr( 'type' ) || 'checkbox' === $( this ).attr( 'type' ) ) {
				$( this ).prop( 'checked', false );
			} else {
				$( this ).val( '' );
			}
		} );

		// Reset all multi-cart addon data (hidden fields).
		let $multiCartCheck = $cart.siblings( '.multi-cart-check' );

		if ( $multiCartCheck.length ) {
			// @see Barn2\Plugin\WC_Product_Table\Integration::MULTI_FIELD_NAME_PREFIX
			$multiCartCheck.children( 'input[data-input-name^="addon-"]' ).val( '' );
		}

		return table;
	};

	ProductTable.prototype.scrollToTop = function() {
		let table = this,
			scroll = table.config.scrollOffset;

		if ( false !== scroll && ! isNaN( scroll ) ) {
			let tableOffset = table.$tableWrapper.offset().top - scroll;

			if ( table.hasAdminBar ) { // Adjust offset for WP admin bar
				tableOffset -= 32;
			}
			$( 'html,body' ).animate( { scrollTop: tableOffset }, 300 );
		}

		return table;
	};

	ProductTable.prototype.showHidePagination = function() {
		let table = this;

		// Hide pagination if we only have 1 page
		if ( table.$pagination.length ) {
			let pageInfo = table.getDataTable().page.info();

			if ( pageInfo && pageInfo.pages <= 1 ) {
				table.$pagination.hide( 0 );
			} else {
				table.$pagination.show();
			}
		}

		return table;
	};

	ProductTable.prototype.updateFilterOptions = function( $filters ) {
		let table = this;

		// Updating filter options based on table contents is only supported for standard loading.
		if ( ! $filters.length || table.config.serverSide ) {
			return table;
		}

		let filtersData = table.$table.data( 'filters' );

		$filters.each( function() {
			let $select = $( this ),
				tax = $select.data( 'tax' ),
				val = $select.val(); // Store value so we can reset later.

			if ( ! ( tax in filtersData ) ) {
				return;
			}

			let filterData = filtersData[tax],
				options = filterData.terms;

			// Find all data in search column so we can restrict filter to relevant data only.
			let searchData = table.getDataTable()
				.column( $select.data( 'searchColumn' ) + ':name', { search: 'applied' } )
				.data()
				.filter( function( val ) {
					return val.length > 0;
				} );

			if ( searchData.any() ) {
				let sep = params.filter_term_separator;
				options = reduceFilterOptions( options, searchData.join( sep ).split( sep ) );
			} else {
				// No search data so filter will be empty.
				options = [];
			}

			// Update the filter options.
			setFilterOptions( $select, filterData.heading, options );

			// Restore previous selected value.
			$select.val( val );
		} );

		return table;
	};

	/******************************************
	 * EVENTS
	 ******************************************/

	function onAddToCart( event ) {
		let table = event.data.table,
			$cart = $( this ),
			$button = $cart.find( '.single_add_to_cart_button' ),
			productId = $cart.find( '[name="add-to-cart"]' ).val();

		// If not using AJAX, set form action to blank so current page is reloaded, rather than single product page
		if ( ! table.config.ajaxCart ) {
			$cart.attr( 'action', '' );
			return true;
		}

		if ( ! productId || ! $cart.length || $button.hasClass( 'disabled' ) ) {
			return true;
		}

		event.preventDefault();

		$cart.find( 'p.cart-error' ).remove();
		table.$tableControls.find( '.multi-cart-message' ).remove();

		$button
			.removeClass( 'added' )
			.addClass( 'loading' )
			.siblings( 'a.added_to_cart' )
			.remove();

		let data = $cart.serializeObject();
		delete data['add-to-cart']; // Make sure 'add-to-cart' isn't included as we use 'product_id'

		data.product_id = productId;
		data.action = 'wcpt_add_to_cart';
		data._ajax_nonce = params.ajax_nonce;

		$( document.body ).trigger( 'adding_to_cart', [ $button, data ] );

		$.ajax( {
			url: params.ajax_url,
			type: 'POST',
			data: data,
			xhrFields: {
				withCredentials: true
			}
		} ).done( function( response ) {
			if ( response.error ) {
				if ( response.error_message ) {
					$cart.append( response.error_message );
				}
				return;
			}

			// Product sucessfully added - redirect to cart or show 'View cart' link.
			if ( 'yes' === wc_add_to_cart_params.cart_redirect_after_add ) {
				window.location = wc_add_to_cart_params.cart_url;
				return;
			} else {
				// Reset stuff on successful addition.
				table
					.resetQuantities( $cart )
					.resetProductAddons( $cart )
					.resetMultiCartCheckboxes( $cart );

				// Trigger WooCommerce added_to_cart event - add-to-cart.js in WooCommerce will handle adding
				// the 'View cart' link, add classes to $button, and update the cart fragments.
				$cart.trigger( 'added_to_cart', [ response.fragments, response.cart_hash, $button ] );
			}
		} ).always( function() {
			$button.removeClass( 'loading' );
		} );

		return false;
	}

	// Submit event for multi add to cart form
	function onAddToCartMulti( event ) {
		let table = event.data.table,
			dataTable = table.getDataTable(),
			$multiForm = $( this ),
			data = {};

		// Add id="multi-cart" to form via JS as we can have several multi cart forms on a single page.
		// This keeps the HTML valid and makes sure each form can be submitted correctly.
		$multiForm.attr( 'id', 'multi-cart' );

		// Remove any previous multi cart message.
		table.$tableControls.find( '.multi-cart-message' ).remove();

		// Reset add to cart buttons for (single products added to cart).
		dataTable.$( '.cart p.cart-error, a.added_to_cart' ).remove();
		dataTable.$( '.cart .single_add_to_cart_button.added' ).removeClass( 'added' );

		// Find all checked products and loop through each to build product IDs and quantities.
		// dataTable.$() doesn't work in child rows, so we need add these manually to the result set.
		dataTable
			.$( '.multi-cart-check > input[type="checkbox"]:checked' ) // all checkboxes
			.add( table.$table.find( '.child .multi-cart-check > input[type="checkbox"]:checked' ) ) // add checkboxes in child rows
			.each( function() {
				// Add all the hidden fields to our data to be posted
				$.extend( true, data, $( this ).siblings( 'input[type="hidden"]' ).serializeObject() );
			} );

		// Show error if no products were selected
		if ( $.isEmptyObject( data ) && ( 'multiCartNoSelection' in params.language ) ) {
			setMultiCartMessage( '<p class="cart-error">' + params.language.multiCartNoSelection + '</p>', $multiForm );
			return false;
		}

		// Return here if we're not using AJAX.
		if ( ! table.config.ajaxCart ) {
			table.checkFormAttributeSupport( $multiForm );
			return true;
		}

		// AJAX enabled, so block table and do the AJAX post
		table.$tableWrapper.block( blockConfig );

		data.action = 'wcpt_add_to_cart_multi';
		data._ajax_nonce = params.ajax_nonce;

		$( document.body ).trigger( 'adding_to_cart', [ $multiForm, data ] );

		$.ajax( {
			url: params.ajax_url,
			type: 'POST',
			data: data,
			xhrFields: {
				withCredentials: true
			}
		} ).done( function( response ) {
			if ( response.error ) {
				if ( response.error_message ) {
					setMultiCartMessage( response.error_message, $multiForm );
				}
				return;
			}

			if ( 'yes' === wc_add_to_cart_params.cart_redirect_after_add ) {
				// Redirect after add to cart.
				window.location = wc_add_to_cart_params.cart_url;
				return;
			} else {
				// Replace fragments
				if ( response.fragments ) {
					$.each( response.fragments, function( key, value ) {
						$( key ).replaceWith( value );
					} );
				}

				if ( response.cart_message ) {
					setMultiCartMessage( response.cart_message, $multiForm );
				}

				// Reset all the things.
				table
					.resetQuantities()
					.resetProductAddons()
					.resetMultiCartCheckboxes();

				// Trigger event so themes can refresh other areas.
				$( document.body ).trigger( 'added_to_cart', [ response.fragments ] );
			}
		} ).always( function() {
			table.$tableWrapper.unblock();
			$multiForm.removeAttr( 'id' );
		} );

		return false;
	}

	function onAjaxLoad( event, settings, json ) {
		let table = event.data.table;

		if ( null !== json && 'data' in json && $.isArray( json.data ) ) {
			table.ajaxData = json.data;
		}

		table.$table.trigger( 'lazyload.wcpt', [ table ] );
	}

	function onClickToSearch( event ) {
		let $link = $( this ),
			table = event.data.table,
			columnName = $link.data( 'column' ),
			slug = $link.children( '[data-slug]' ).length ? $link.children( '[data-slug]' ).data( 'slug' ) : '';

		// Bail if no term slug to search.
		if ( '' === slug ) {
			return true;
		}

		// If we have filters, update selection to match the value being searched for, and let onFilterChange handle the column searching.
		if ( table.$filters.length ) {
			let $filter = table.$filters.filter( '[data-column="' + columnName + '"]' ).first();

			// Check if the filter for this column exists and has the clicked value present. If so, we use the filter to perform the search and exit early.
			if ( $filter.length && $filter.children( 'option[value="' + slug + '"]' ).length ) {
				$filter.val( slug ).trigger( 'change' );

				table.scrollToTop();
				return false;
			}
		}

		let dataTable = table.getDataTable(),
			column = dataTable.column( columnName + ':name' );

		if ( table.config.serverSide ) {
			column.search( slug ).draw();
		} else {
			// Standard loading uses the link text to search column.
			let searchVal = '(^|, )' + $.fn.dataTable.util.escapeRegex( $link.text() ) + '(, |$)';
			column.search( searchVal, true, false ).draw();
		}

		table.scrollToTop();
		return false;
	}

	function onDraw( event ) {
		let table = event.data.table;

		// Add row attributes to each <tr> if using lazy load
		if ( table.config.serverSide ) {
			table.processAjaxData();
		}

		initContent( table.$table );

		if ( table.config.multiAddToCart && table.$tableWrapper.length ) {
			table.$tableWrapper.find( '.multi-cart-message' ).remove();
		}

		table.showHidePagination();
		table.$table.trigger( 'draw.wcpt', [ table ] );
	}

	function onFilterChange( event, setValueOnly ) {
		let $select = $( this ),
			table = event.data.table;

		if ( setValueOnly ) {
			return true;
		}

		// Disable onSearch as we run a custom updateFilterOptions method on filter change.
		table.$table.off( 'search.dt', onSearch );

		let value = $select.val(),
			taxonomy = $select.data( 'tax' ),
			dataTable = table.getDataTable(),
			searchColumn = dataTable.column( $select.data( 'searchColumn' ) + ':name' );

		if ( table.config.serverSide ) {
			// Lazy load search.
			searchColumn.search( value ).draw();
		} else {
			// Standard load search.
			let sep = params.filter_term_separator;

			if ( '' !== value ) {
				// Escape search value before adding to search regex pattern.
				value = $.fn.dataTable.util.escapeRegex( value );
				value = `(^|${sep})${value}(${sep}|$)`;
			}

			searchColumn.search( value, true, false ).draw();
		}

		let $thisFilterGroup = table.$filters.filter( '[data-tax="' + taxonomy + '"]' ),
			$otherFilters = table.$filters.not( $thisFilterGroup );

		// If we have filters above and below table, update corresponding filter to match.
		$thisFilterGroup
			.not( $select[0] )
			.val( value )
			.trigger( 'change', [ true ] );

		// Update other filters to show only relevant search items.
		table.updateFilterOptions( $otherFilters );

		// Re-enable onSearch.
		table.$table.on( 'search.dt', { table: table }, onSearch );
	}

	function onInit( event ) {
		let table = event.data.table;

		table.$tableWrapper = table.$table.parent();
		table.$pagination = table.$tableWrapper.find( '.dataTables_paginate' );
		table.$tableControls = table.$tableWrapper.find( '.wc-product-table-controls' );

		table
			.initFilters()
			.initSelectWoo()
			.initResetButton()
			.registerVariationEvents()
			.initMultiCart()
			.initSearchOnClick()
			.initPhotoswipe()
			.initQuickViewPro()
			.showHidePagination();

		table.$table
			.removeClass( 'loading' )
			.trigger( 'init.wcpt', [ table ] );
	}

	function onOpenPhotoswipe( event ) {
		event.stopPropagation();

		// Only open for click events.
		if ( 'click' !== event.type ) {
			return false;
		}

		let pswpElement = $( '.pswp' )[0],
			$target = $( event.target ),
			$galleryImage = $target.closest( '.woocommerce-product-gallery__image' ),
			items = [];

		if ( $galleryImage.length > 0 ) {
			$galleryImage.each( function( i, el ) {
				let img = $( el ).find( 'img' ),
					large_image_src = img.attr( 'data-large_image' ),
					large_image_w = img.attr( 'data-large_image_width' ),
					large_image_h = img.attr( 'data-large_image_height' ),
					item = {
						src: large_image_src,
						w: large_image_w,
						h: large_image_h,
						title: ( img.attr( 'data-caption' ) && img.attr( 'data-caption' ).length ) ? img.attr( 'data-caption' ) : img.attr( 'title' )
					};
				items.push( item );
			} );
		}

		let options = {
			index: 0,
			shareEl: false,
			closeOnScroll: false,
			history: false,
			hideAnimationDuration: 0,
			showAnimationDuration: 0
		};

		// Initializes and opens PhotoSwipe
		let photoswipe = new PhotoSwipe( pswpElement, PhotoSwipeUI_Default, items, options );
		photoswipe.init();

		return false;
	}

	function onPage( event ) {
		// Animate back to top of table on next/previous page event
		event.data.table.scrollToTop();
	}

	function onProcessing( event, settings, processing ) {
		if ( processing ) {
			event.data.table.$table.block( blockConfig );
		} else {
			event.data.table.$table.unblock();
		}
	}

	function onReset( event ) {
		event.preventDefault();

		// Reload page without query params if we have them (e.g. layered nav filters)
		if ( window.location.search ) {
			window.location = getCurrentUrlWithoutFilters();
			return true;
		}

		let table = event.data.table,
			dataTable = table.getDataTable();

		// Reset responsive child rows
		table.$table.find( 'tr.child' ).remove();
		table.$table.find( 'tr.parent' ).removeClass( 'parent' );

		// Reset cart stuff
		table
			.resetQuantities()
			.resetProductAddons()
			.resetMultiCartCheckboxes();

		// Disable onSearch to prevent this running during reset.
		table.$table.off( 'search.dt', onSearch );

		// Remove add to cart notifications
		table.$tableWrapper.find( '.multi-cart-message' ).remove();
		table.$table.find( 'p.cart-error' ).remove();
		table.$table
			.find( '.cart .single_add_to_cart_button' )
			.removeClass( 'added' )
			.siblings( 'a.added_to_cart' ).remove();

		// Clear search for any filtered columns
		dataTable.columns( 'th[data-searchable="true"]' ).search( '' );

		// Reset ordering
		let initialOrder = table.$table.attr( 'data-order' );

		if ( initialOrder.length ) {
			let orderArray = initialOrder.replace( /[\[\]" ]+/g, '' ).split( ',' );

			if ( 2 === orderArray.length ) {
				dataTable.order( orderArray );
			}
		}

		// Reset initial search term
		let searchTerm = ( 'search' in table.config && 'search' in table.config.search ) ? table.config.search.search : '';

		// Set search, reset page length, then re-draw
		dataTable
			.search( searchTerm )
			.page.len( table.config.pageLength )
			.draw( true );

		if ( selectWooEnabled() ) {
			// If using selectWoo, setting the page length above won't update the select control, so we need to trigger change.
			table.$tableControls.find( '.dataTables_length select' ).trigger( 'change' );
		}

		// Reset filters
		if ( table.$filters.length ) {
			table.$filters.val( '' ).trigger( 'change', [ true ] );
			table.updateFilterOptions( table.$filters );
		}

		// Re-enable onSearch.
		table.$table.on( 'search.dt', { table: table }, onSearch );
	}

	function onResponsiveDisplay( event, datatable, row, showHide ) {
		if ( showHide && ( typeof row.child() !== 'undefined' ) ) {

			// Initialise media and other content in child row
			initContent( row.child() );

			let table = event.data.table;

			table.$table.trigger( 'responsiveDisplay.wcpt', [ table, datatable, row, showHide ] );
		}
	}

	function onSearch( event, settings ) {
		let table = event.data.table;

		// Handler to update the filter dropdown options during search operations (e.g. text box search, click to search, etc.).
		// In DataTables, this event is triggered for all searches including filter dropdown searches, so we disable this
		// handler in onFilterChange to prevent conflicts.

		// We only update filters that have no current selection (i.e. where the default value is selected).
		let $filtersWithNoSelection = table.$filters.filter( function() {
			return '' === $( this ).val();
		} );

		table.updateFilterOptions( $filtersWithNoSelection );
	}

	function onStateLoadParams( event, settings, data ) {
		let table = event.data.table;

		// Always reset to first page.
		data.start = 0;

		// If we have no active filter widgets, clear previous table search and reset ordering.
		if ( window.location.href === getCurrentUrlWithoutFilters() ) {

			// Reset page length
			if ( 'pageLength' in table.config ) {
				data.length = table.config.pageLength;
			}

			// Reset search
			if ( 'search' in table.config && 'search' in table.config.search ) {
				data.search.search = table.config.search.search;
			}

			// Clear any column searches
			for ( let i = 0; i < data.columns.length; i++ ) {
				data.columns[i].search.search = '';
			}

			// Reset ordering - use order from shortcode if specified, otherwise remove ordering
			if ( 'order' in table.config ) {
				data.order = table.config.order;
			}
		}

		// Store initial state
		table.initialState = data;
	}

	function onWindowLoad( event ) {
		let table = event.data.table;

		// Recalc column sizes on window load (e.g. to correctly contain media playlists)
		table.getDataTable()
			.columns.adjust()
			.responsive.recalc();

		table.$table.trigger( 'load.wcpt', [ table ] );
	}

	/******************************************
	 * JQUERY PLUGIN
	 ******************************************/

	/**
	 * jQuery plugin to create a product table for the current set of matched elements.
	 *
	 * @returns jQuery object - the set of matched elements the function was called with (for chaining)
	 */
	$.fn.productTable = function() {
		return this.each( function() {
			let table = new ProductTable( $( this ) );
			table.init();
		} );
	};

	$( function() {
		// Add support for hyphens and non-Roman characters in input names/keys in jquery-serialize-object.js
		if ( typeof FormSerializer !== 'undefined' ) {
			$.extend( FormSerializer.patterns, {
				validate: /^[a-z][a-z0-9_\-\%]*(?:\[(?:\d*|[a-z0-9_\-\%]+)\])*$/i,
				key: /[a-z0-9_\-\%]+|(?=\[\])/gi,
				named: /^[a-z0-9_\-\%]+$/i
			} );
		}

		if ( 'DataTable' in $.fn && $.fn.DataTable.ext ) {
			// Change DataTables error reporting to throw rather than alert
			$.fn.DataTable.ext.errMode = 'throw';
		}

		// Set fallback for WC add to cart params.
		if ( typeof wc_add_to_cart_params === 'undefined' ) {
			window.wc_add_to_cart_params = {
				cart_redirect_after_add: 'no',
				cart_url: '',
				i18n_view_cart: 'View cart'
			};
		}

		// Initialise all product tables
		$( '.wc-product-table' ).productTable();
	} );

} )( jQuery, window, document, window.product_table_params );