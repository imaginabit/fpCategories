
( function( $ ) {
	"use strict";

  var primaryTermInputTemplate, primaryTermUITemplate, primaryTermScreenReaderTemplate;
  var metaboxTaxonomy;

  function makePrimarySub(termId){
    console.log('termId', termId);
    var primaryTermInput;

  	primaryTermInput = $( "#yoast-wpseo-primary-category" );
  	primaryTermInput.val( termId ).trigger( "change" );
  }

  function termCheckboxHandler( taxonomyName ) {
		return function() {
      console.log('termCheckboxHandler');
      console.log('primary term', getPrimaryTerm( taxonomyName ));
      console.log( 'this', $(this) );
      console.log( 'this val', $(this).val() );
      console.log( '$( this ).prop( "checked" )', $( this ).prop( "checked" ));
			// If the user unchecks the primary category we have to select any new primary term
			if ( false === $( this ).prop( "checked" ) && $( this ).val() === getPrimaryTerm( taxonomyName ) ) {
				makeFirstTermPrimary( taxonomyName );
			}

			ensurePrimaryTerm( taxonomyName );

			updatePrimaryTermSelectors( taxonomyName );
		};
	}

  function makeFirstTermPrimary( taxonomyName ) {
    console.log('make firstTerm great again');
    var firstTerm = metaboxTaxonomy.find( "#" + taxonomyName + 'checklist input[type="checkbox"]:checked:first' );

    setPrimaryTerm( taxonomyName, firstTerm.val() );
    updatePrimaryTermSelectors( taxonomyName );
  }

  function setPrimaryTerm( taxonomyName, termId ) {
    var primaryTermInput;

    primaryTermInput = $( "#yoast-wpseo-primary-" + taxonomyName );
    primaryTermInput.val( termId ).trigger( "change" );
  }

  function ensurePrimaryTerm( taxonomyName ) {
		if ( "" === getPrimaryTerm( taxonomyName ) ) {
			makeFirstTermPrimary( taxonomyName );
		}
	}

  function updatePrimaryTermSelectors( taxonomyName ) {
		var checkedTerms;
		var listItem, label;

		checkedTerms = metaboxTaxonomy.find( "#" + taxonomyName + 'checklist input[type="checkbox"]:checked' );

		var taxonomyListItem = metaboxTaxonomy.find( "#" + taxonomyName + "checklist li" );
		taxonomyListItem.removeClass( "wpseo-term-unchecked wpseo-primary-term wpseo-non-primary-term" );

		$( ".wpseo-primary-category-label" ).remove();
		taxonomyListItem.addClass( "wpseo-term-unchecked" );

		// If there is only one term selected we don't want to show our interface.
		if ( checkedTerms.length <= 1 ) {
			return;
		}

		checkedTerms.each( function( i, term ) {
			term = $( term );
			listItem = term.closest( "li" );
			listItem.removeClass( "wpseo-term-unchecked" );

			// Create our interface elements if they don't exist.
			if ( ! hasPrimaryTermElements( term ) ) {
				createPrimaryTermElements( taxonomyName, term );
			}

			if ( term.val() === getPrimaryTerm( taxonomyName ) ) {
				listItem.addClass( "wpseo-primary-term" );

				label = term.closest( "label" );
				label.find( ".wpseo-primary-category-label" ).remove();
				label.append( primaryTermScreenReaderTemplate( {
					taxonomy: taxonomyName,
				} ) );
			}
			else {
				listItem.addClass( "wpseo-non-primary-term" );
			}
		} );
	}
  function termListAddHandler( taxonomyName ) {
		return function() {
			ensurePrimaryTerm( taxonomyName );
			updatePrimaryTermSelectors( taxonomyName );
		};
	}
  function getPrimaryTerm( taxonomyName ) {
    var primaryTermInput;

    primaryTermInput = $( "#yoast-wpseo-primary-" + taxonomyName );
    return primaryTermInput.val();
  }

  function makePrimaryHandler( taxonomyName ) {
		return function( e ) {
			var term, checkbox;

			term = $( e.currentTarget );
			checkbox = term.siblings( "label" ).find( "input" );

			setPrimaryTerm( taxonomyName, checkbox.val() );

			updatePrimaryTermSelectors( taxonomyName );

			// The clicked link will be hidden so we need to focus something different.
			checkbox.focus();
		};
	}
  function hasPrimaryTermElements( checkbox ) {
		return 1 === $( checkbox ).closest( "li" ).children( ".wpseo-make-primary-term" ).length;
	}
  function createPrimaryTermElements( taxonomyName, checkbox ) {
		var label, html;

		label = $( checkbox ).closest( "label" );

		html = primaryTermUITemplate( {
			taxonomy: taxonomyName,
			term: label.text(),
		} );

		label.after( html );
	}

  $( function() {
			var html;
      console.log("fpCategories js");

      var taxonomyname = 'category';

      metaboxTaxonomy = $( "#fpCategorias-box" );

      // Initialize our templates
		  primaryTermInputTemplate = wp.template( "primary-term-input" );
		  primaryTermUITemplate = wp.template( "primary-term-ui" );
		  primaryTermScreenReaderTemplate = wp.template( "primary-term-screen-reader" );

      html = primaryTermInputTemplate( {
				taxonomy: taxonomyname,
			} );


			metaboxTaxonomy.append( html );
			updatePrimaryTermSelectors( taxonomyname );

      metaboxTaxonomy.on( "click", 'input[type="checkbox"]', termCheckboxHandler( taxonomyname ) );

			// When the AJAX Request is done, this event will be fired.
			metaboxTaxonomy.on( "wpListAddEnd", "#" + taxonomyname + "checklist", termListAddHandler( taxonomyname ) );

			metaboxTaxonomy.on( "click", ".wpseo-make-primary-term", makePrimaryHandler( taxonomyname ) );
      // metaboxTaxonomy.on("click",".wpseo-make-primary-term",
      //   function(){
      //       makePrimarySub( $(this).siblings( "label" ).find( "input" ).val() );
      //   }
      // );

	} );
}( jQuery ) );
