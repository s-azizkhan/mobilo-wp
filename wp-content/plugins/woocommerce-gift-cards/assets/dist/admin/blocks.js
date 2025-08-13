/******/ (function() { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ 184:
/***/ (function(module, exports) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/*!
  Copyright (c) 2018 Jed Watson.
  Licensed under the MIT License (MIT), see
  http://jedwatson.github.io/classnames
*/
/* global define */

(function () {
	'use strict';

	var hasOwn = {}.hasOwnProperty;

	function classNames() {
		var classes = [];

		for (var i = 0; i < arguments.length; i++) {
			var arg = arguments[i];
			if (!arg) continue;

			var argType = typeof arg;

			if (argType === 'string' || argType === 'number') {
				classes.push(arg);
			} else if (Array.isArray(arg)) {
				if (arg.length) {
					var inner = classNames.apply(null, arg);
					if (inner) {
						classes.push(inner);
					}
				}
			} else if (argType === 'object') {
				if (arg.toString === Object.prototype.toString) {
					for (var key in arg) {
						if (hasOwn.call(arg, key) && arg[key]) {
							classes.push(key);
						}
					}
				} else {
					classes.push(arg.toString());
				}
			}
		}

		return classes.join(' ');
	}

	if ( true && module.exports) {
		classNames.default = classNames;
		module.exports = classNames;
	} else if (true) {
		// register as 'classnames', consistent with npm package name
		!(__WEBPACK_AMD_DEFINE_ARRAY__ = [], __WEBPACK_AMD_DEFINE_RESULT__ = (function () {
			return classNames;
		}).apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
		__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));
	} else {}
}());


/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	!function() {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = function(module) {
/******/ 			var getter = module && module.__esModule ?
/******/ 				function() { return module['default']; } :
/******/ 				function() { return module; };
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	!function() {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = function(exports, definition) {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	!function() {
/******/ 		__webpack_require__.o = function(obj, prop) { return Object.prototype.hasOwnProperty.call(obj, prop); }
/******/ 	}();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be in strict mode.
!function() {
"use strict";

;// CONCATENATED MODULE: external ["wc","wcSettings"]
var external_wc_wcSettings_namespaceObject = window["wc"]["wcSettings"];
;// CONCATENATED MODULE: ./resources/js/frontend/blocks/utils/get-plugin-settings.js
/**
 * External dependencies
 */


/**
 * Helper for fetching plugin settings.
 */
const getPluginSettings = () => {
  const {
    show_balance_checkbox: showBalanceCheckbox,
    show_remaining_balance_per_gift_card: showRemainingBalance,
    is_ui_disabled: isUiDisabled,
    is_cart_disabled: isCartDisabled,
    is_redeeming_enabled: isRedeemingEnabled,
    is_cart: isCart,
    is_checkout: isCheckout,
    account_orders_link: accountOrdersLink
  } = (0,external_wc_wcSettings_namespaceObject.getSetting)('wc-gift-cards-blocks_data');
  return {
    isUiDisabled,
    isCartDisabled,
    isRedeemingEnabled,
    isCart,
    isCheckout,
    showBalanceCheckbox,
    showRemainingBalance,
    accountOrdersLink
  };
};
/* harmony default export */ var get_plugin_settings = (getPluginSettings);
;// CONCATENATED MODULE: external "React"
var external_React_namespaceObject = window["React"];
;// CONCATENATED MODULE: external ["wp","blockEditor"]
var external_wp_blockEditor_namespaceObject = window["wp"]["blockEditor"];
;// CONCATENATED MODULE: external ["wp","components"]
var external_wp_components_namespaceObject = window["wp"]["components"];
;// CONCATENATED MODULE: external ["wp","element"]
var external_wp_element_namespaceObject = window["wp"]["element"];
;// CONCATENATED MODULE: ./node_modules/@wordpress/icons/build-module/icon/index.js
/**
 * WordPress dependencies
 */

/** @typedef {{icon: JSX.Element, size?: number} & import('@wordpress/primitives').SVGProps} IconProps */

/**
 * Return an SVG icon.
 *
 * @param {IconProps} props icon is the SVG component to render
 *                          size is a number specifiying the icon size in pixels
 *                          Other props will be passed to wrapped SVG component
 *
 * @return {JSX.Element}  Icon component
 */

function Icon(_ref) {
  let {
    icon,
    size = 24,
    ...props
  } = _ref;
  return (0,external_wp_element_namespaceObject.cloneElement)(icon, {
    width: size,
    height: size,
    ...props
  });
}

/* harmony default export */ var icon = (Icon);
//# sourceMappingURL=index.js.map
;// CONCATENATED MODULE: external ["wp","primitives"]
var external_wp_primitives_namespaceObject = window["wp"]["primitives"];
;// CONCATENATED MODULE: ./node_modules/@wordpress/icons/build-module/library/tag.js


/**
 * WordPress dependencies
 */

const tag = (0,external_wp_element_namespaceObject.createElement)(external_wp_primitives_namespaceObject.SVG, {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24"
}, (0,external_wp_element_namespaceObject.createElement)(external_wp_primitives_namespaceObject.Path, {
  d: "M20.1 11.2l-6.7-6.7c-.1-.1-.3-.2-.5-.2H5c-.4-.1-.8.3-.8.7v7.8c0 .2.1.4.2.5l6.7 6.7c.2.2.5.4.7.5s.6.2.9.2c.3 0 .6-.1.9-.2.3-.1.5-.3.8-.5l5.6-5.6c.4-.4.7-1 .7-1.6.1-.6-.2-1.2-.6-1.6zM19 13.4L13.4 19c-.1.1-.2.1-.3.2-.2.1-.4.1-.6 0-.1 0-.2-.1-.3-.2l-6.5-6.5V5.8h6.8l6.5 6.5c.2.2.2.4.2.6 0 .1 0 .3-.2.5zM9 8c-.6 0-1 .4-1 1s.4 1 1 1 1-.4 1-1-.4-1-1-1z"
}));
/* harmony default export */ var library_tag = (tag);
//# sourceMappingURL=tag.js.map
;// CONCATENATED MODULE: external ["wp","blocks"]
var external_wp_blocks_namespaceObject = window["wp"]["blocks"];
;// CONCATENATED MODULE: external ["wc","blocksCheckout"]
var external_wc_blocksCheckout_namespaceObject = window["wc"]["blocksCheckout"];
;// CONCATENATED MODULE: external ["wp","i18n"]
var external_wp_i18n_namespaceObject = window["wp"]["i18n"];
;// CONCATENATED MODULE: external ["wp","data"]
var external_wp_data_namespaceObject = window["wp"]["data"];
;// CONCATENATED MODULE: external ["wp","compose"]
var external_wp_compose_namespaceObject = window["wp"]["compose"];
// EXTERNAL MODULE: ./node_modules/classnames/index.js
var classnames = __webpack_require__(184);
var classnames_default = /*#__PURE__*/__webpack_require__.n(classnames);
;// CONCATENATED MODULE: ./resources/js/frontend/blocks/components/loading-mask/index.js

/**
 * External dependencies
 */



// @todo Find a way to block buttons/form components when LoadingMask isLoading
const LoadingMask = ({
  children,
  className,
  screenReaderLabel,
  isLoading = true
}) => {
  return (0,external_React_namespaceObject.createElement)("div", {
    className: classnames_default()(className, {
      'wc-block-components-loading-mask': isLoading
    })
  }, (0,external_React_namespaceObject.createElement)("div", {
    className: classnames_default()({
      'wc-block-components-loading-mask__children': isLoading
    }),
    "aria-hidden": isLoading
  }, children), isLoading && (0,external_React_namespaceObject.createElement)("span", {
    className: "screen-reader-text"
  }, screenReaderLabel || (0,external_wp_i18n_namespaceObject.__)('Loading…', 'woocommerce-gift-cards')));
};
/* harmony default export */ var loading_mask = (LoadingMask);
;// CONCATENATED MODULE: ./resources/js/frontend/blocks/components/text-input/index.js

/**
 * External dependencies
 */




/**
 * Internal dependencies
 */


/**
 * Displays a text input.
 *
 * @param {Object}   props
 * @param {string}   props.className
 * @param {number}   props.id
 * @param {string}   props.type
 * @param {string}   props.ariaLabel
 * @param {string}   props.ariaDescribedBy
 * @param {string}   props.label
 * @param {string}   props.screenReaderLabel
 * @param {boolean}  props.disabled
 * @param {boolean}  props.autoCapitalize
 * @param {boolean}  props.autoComplete
 * @param {string}   props.value
 * @param {Function} props.onChange
 * @param {number}   props.min
 * @param {number}   props.max
 * @param {number}   props.step
 * @param {boolean}  props.hasError
 * @param {boolean}  props.required
 * @param {boolean}  props.focusOnMount
 * @param {boolean}  props.onBlur
 * @return {JSX.Element} TextInput.
 */
const TextInput = ({
  className,
  id,
  type = 'text',
  ariaLabel,
  ariaDescribedBy,
  label,
  screenReaderLabel,
  disabled,
  autoCapitalize = 'off',
  autoComplete = 'off',
  value = '',
  onChange,
  min,
  max,
  step,
  hasError = false,
  required = false,
  focusOnMount = false,
  onBlur = () => {
    /* Do nothing */
  }
}) => {
  const [isPristine, setIsPristine] = (0,external_wp_element_namespaceObject.useState)(true);
  const [isActive, setIsActive] = (0,external_wp_element_namespaceObject.useState)(false);
  const inputRef = (0,external_wp_element_namespaceObject.useRef)(null);

  /**
   * Focus on mount
   *
   * If the input is in pristine state, focus the element.
   */
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    if (isPristine && focusOnMount) {
      inputRef.current?.focus();
    }
    setIsPristine(false);
  }, [focusOnMount, isPristine]);
  const numberAttributesFromProps = type === 'number' ? {
    step,
    min,
    max
  } : {};
  const numberProps = {};
  Object.keys(numberAttributesFromProps).forEach(key => {
    if (typeof numberAttributesFromProps[key] === 'undefined') {
      return;
    }
    numberProps[key] = numberAttributesFromProps[key];
  });
  return (0,external_React_namespaceObject.createElement)("div", {
    className: classnames_default()('wc-gift-cards-text-input', className, {
      'is-active': isActive || value,
      'has-error': hasError
    })
  }, (0,external_React_namespaceObject.createElement)("input", {
    type: type,
    id: id,
    value: value,
    ref: inputRef,
    autoCapitalize: autoCapitalize,
    autoComplete: autoComplete,
    onChange: event => {
      onChange(event.target.value);
    },
    onFocus: () => setIsActive(true),
    onBlur: event => {
      onBlur(event.target.value);
      setIsActive(false);
    },
    "aria-label": ariaLabel || label,
    "aria-invalid": hasError === true,
    disabled: disabled,
    "aria-describedby": hasError ? ariaDescribedBy : '',
    required: required,
    ...numberProps
  }), (0,external_React_namespaceObject.createElement)(external_wc_blocksCheckout_namespaceObject.Label, {
    label: label,
    screenReaderLabel: screenReaderLabel || label,
    wrapperElement: "label",
    wrapperProps: {
      htmlFor: id
    },
    htmlFor: id
  }));
};
/* harmony default export */ var text_input = (TextInput);
;// CONCATENATED MODULE: ./resources/js/frontend/blocks/components/gift-cards-form/index.js

/**
 * External dependencies
 */






/**
 * Internal dependencies
 */





/**
 * Inline form for applying gift card codes in the cart.
 *
 * @param {Object}   props
 * @param {Object}   props.extensions   The extension-added data on the WC Cart.
 * @param {Function} props.createNotice
 * @return {JSX.Element} A panel used to display the inline gift card form.
 */
const GiftCardsForm = ({
  extensions,
  createNotice
}) => {
  // Form state.
  const [giftCardCode, setGiftCardCode] = (0,external_wp_element_namespaceObject.useState)('');
  const [isProcessing, setIsProcessing] = (0,external_wp_element_namespaceObject.useState)(false);

  // Error handling state.
  const [showError, setShowError] = (0,external_wp_element_namespaceObject.useState)(false);
  const [errorMessage, setErrorMessage] = (0,external_wp_element_namespaceObject.useState)('');
  const notApplicable = !extensions || !extensions.hasOwnProperty('woocommerce-gift-cards') || !extensions['woocommerce-gift-cards'].hasOwnProperty('account_giftcards');
  if (notApplicable === true) {
    return null;
  }
  const {
    isCheckout
  } = get_plugin_settings();

  /**
   * Function applyGiftCardCode.
   *
   * Applies a given code to the current session using the extensionCartUpdate.
   *
   * @param {string} codeToApply
   */
  const applyGiftCardCode = codeToApply => {
    codeToApply = codeToApply.trim();
    const codeRegex = /^([a-zA-Z0-9]{4}[\-]){3}[a-zA-Z0-9]{4}$/;
    if (!codeToApply.match(codeRegex)) {
      const message = (0,external_wp_i18n_namespaceObject.__)('Please enter a gift card code that follows the format XXXX-XXXX-XXXX-XXXX, where X can be any letter or number.', 'woocommerce-gift-cards');
      setGiftCardCode('');
      setErrorMessage(message);
      setShowError(true);
      return;
    }
    setIsProcessing(true);
    setShowError(false);
    setErrorMessage('');
    (0,external_wc_blocksCheckout_namespaceObject.extensionCartUpdate)({
      namespace: 'woocommerce-gift-cards',
      data: {
        action: 'apply_gift_card_to_session',
        wc_gc_cart_code: codeToApply
      }
    }).then(() => {
      const message = (0,external_wp_i18n_namespaceObject.__)('Gift card code has been applied.', 'woocommerce-gift-cards');
      createNotice('default', message, {
        id: 'wc-gift-cards-form-notice',
        type: 'snackbar',
        context: !isCheckout ? 'wc/cart' : 'wc/checkout'
      });
    }).catch(err => {
      setShowError(true);
      if (err?.message && typeof err.message === 'string') {
        setErrorMessage(err.message);
        return;
      }
      setErrorMessage((0,external_wp_i18n_namespaceObject.__)('An unknown error occurred.', 'woocommerce-gift-cards'));
    }).finally(() => {
      setIsProcessing(false);
      setGiftCardCode('');
    });
  };

  // Render Inline Gift Card Form Panel.
  return (0,external_React_namespaceObject.createElement)(external_wc_blocksCheckout_namespaceObject.Panel, {
    className: "wc-gift-cards-apply-panel",
    hasBorder: false,
    initialOpen: false,
    title: (0,external_wp_i18n_namespaceObject.__)('Have a gift card?', 'woocommerce-gift-cards')
  }, (0,external_React_namespaceObject.createElement)(loading_mask, {
    isLoading: isProcessing
  }, (0,external_React_namespaceObject.createElement)("form", {
    className: "wc-gift-cards-form"
  }, (0,external_React_namespaceObject.createElement)(text_input, {
    id: 'wc-gift-cards-form-input',
    label: (0,external_wp_i18n_namespaceObject.__)('Enter code', 'woocommerce-gift-cards'),
    type: 'string',
    value: giftCardCode,
    disabled: isProcessing,
    ariaDescribedBy: "validate-error-gift-card",
    onChange: newGiftCardCode => {
      setGiftCardCode(newGiftCardCode);
    },
    focusOnMount: true,
    hasError: showError && giftCardCode === '',
    errorMessage: errorMessage
  }), (0,external_React_namespaceObject.createElement)(external_wc_blocksCheckout_namespaceObject.Button, {
    className: 'wc-gift-cards-form__button',
    disabled: giftCardCode === null || giftCardCode === '' || isProcessing,
    showSpinner: isProcessing,
    onClick: e => {
      e.preventDefault();
      applyGiftCardCode(giftCardCode);
    },
    type: "submit"
  }, (0,external_wp_i18n_namespaceObject.__)('Apply', 'woocommerce-gift-cards')), showError && (0,external_React_namespaceObject.createElement)("div", {
    className: "wc-gift-cards-form__error",
    role: "alert"
  }, (0,external_React_namespaceObject.createElement)(external_wp_element_namespaceObject.RawHTML, {
    id: "validate-error-gift-card"
  }, errorMessage)))));
};
/* harmony default export */ var gift_cards_form = ((0,external_wp_compose_namespaceObject.compose)((0,external_wp_data_namespaceObject.withDispatch)(dispatch => {
  const {
    createNotice
  } = dispatch('core/notices');
  return {
    createNotice
  };
}))(GiftCardsForm));
;// CONCATENATED MODULE: ./resources/js/frontend/blocks/inner-blocks/checkout-order-summary-gift-card-form/block.js

/**
 * External dependencies
 */


/**
 * Internal dependencies
 */

const Block = props => {
  const {
    className,
    extensions
  } = props;
  return (0,external_React_namespaceObject.createElement)(external_wc_blocksCheckout_namespaceObject.TotalsWrapper, {
    className: className
  }, (0,external_React_namespaceObject.createElement)(gift_cards_form, {
    extensions: extensions
  }));
};
/* harmony default export */ var block = (Block);
;// CONCATENATED MODULE: ./resources/js/frontend/blocks/inner-blocks/checkout-order-summary-gift-card-form/block.json
var checkout_order_summary_gift_card_form_block_namespaceObject = JSON.parse('{"name":"woocommerce/checkout-order-summary-gift-card-form-block","version":"1.0.0","title":"Gift Card Form","description":"Shows the apply gift card form.","category":"woocommerce","supports":{"align":false,"html":false,"multiple":false,"reusable":false},"attributes":{"className":{"type":"string","default":""},"lock":{"type":"object","default":{"remove":true,"move":false}}},"parent":["woocommerce/checkout-order-summary-block"],"textdomain":"woocommerce-gift-cards","apiVersion":2}');
;// CONCATENATED MODULE: ./resources/js/frontend/blocks/inner-blocks/checkout-order-summary-gift-card-form/edit.js

/**
 * External dependencies
 */





/**
 * Internal dependencies
 */


const Edit = ({
  attributes
}) => {
  const {
    className
  } = attributes;
  const blockProps = (0,external_wp_blockEditor_namespaceObject.useBlockProps)();

  // Make sure that the Block will be applicable to render.
  const props = {
    extensions: {
      'woocommerce-gift-cards': {
        account_giftcards: []
      }
    }
  };
  return (0,external_React_namespaceObject.createElement)("div", {
    ...blockProps
  }, (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.Disabled, null, (0,external_React_namespaceObject.createElement)(block, {
    className: className,
    ...props
  })));
};
const Save = () => {
  return (0,external_React_namespaceObject.createElement)("div", {
    ...external_wp_blockEditor_namespaceObject.useBlockProps.save()
  });
};
const registerInnerBlock = () => {
  // Register Block in the Editor.
  (0,external_wp_blocks_namespaceObject.registerBlockType)(checkout_order_summary_gift_card_form_block_namespaceObject.name, {
    ...checkout_order_summary_gift_card_form_block_namespaceObject,
    icon: {
      src: (0,external_React_namespaceObject.createElement)(icon, {
        icon: library_tag,
        className: "wc-block-editor-components-block-icon"
      })
    },
    edit: Edit,
    save: Save,
    apiVersion: 3
  });
};
;// CONCATENATED MODULE: ./node_modules/@wordpress/icons/build-module/library/percent.js


/**
 * WordPress dependencies
 */

const percent = (0,external_wp_element_namespaceObject.createElement)(external_wp_primitives_namespaceObject.SVG, {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24"
}, (0,external_wp_element_namespaceObject.createElement)(external_wp_primitives_namespaceObject.Path, {
  fillRule: "evenodd",
  d: "M6.5 8a1.5 1.5 0 103 0 1.5 1.5 0 00-3 0zM8 5a3 3 0 100 6 3 3 0 000-6zm6.5 11a1.5 1.5 0 103 0 1.5 1.5 0 00-3 0zm1.5-3a3 3 0 100 6 3 3 0 000-6zM5.47 17.41a.75.75 0 001.06 1.06L18.47 6.53a.75.75 0 10-1.06-1.06L5.47 17.41z",
  clipRule: "evenodd"
}));
/* harmony default export */ var library_percent = (percent);
//# sourceMappingURL=percent.js.map
;// CONCATENATED MODULE: external ["wc","priceFormat"]
var external_wc_priceFormat_namespaceObject = window["wc"]["priceFormat"];
;// CONCATENATED MODULE: ./resources/js/frontend/blocks/components/balance-checkbox/index.js

/**
 * External dependencies
 */






/**
 * Internal dependencies
 */



/**
 * Displays the account balance checkbox in the cart/checkout totals table.
 *
 * Moreover, it will display account related pending balances, if applicable.
 *
 * @param {Object} props
 * @param {Object} props.extensions The extension-added data on the WC Cart.
 * @return {JSX.Element} The account balance checkbox.
 */
const BalanceCheckbox = ({
  extensions
}) => {
  const {
    is_using_balance: isUsingBalance,
    available_total: availableTotal,
    pending_total: pendingTotal
  } = extensions['woocommerce-gift-cards'];
  const {
    accountOrdersLink
  } = get_plugin_settings();
  const [isChecked, setIsChecked] = (0,external_wp_element_namespaceObject.useState)(isUsingBalance);
  const [isLoading, setIsLoading] = (0,external_wp_element_namespaceObject.useState)(false);
  const isDisabled = isLoading;

  /**
   * Function that handles checkbox changes.
   *
   * It sets the UI into loading mode, and asks for the server to setup the session.
   *
   * @param {boolean} value The checked state of the checkbox.
   */
  const onChange = value => {
    setIsLoading(true);
    (0,external_wc_blocksCheckout_namespaceObject.extensionCartUpdate)({
      namespace: 'woocommerce-gift-cards',
      data: {
        action: 'set_balance_usage',
        value: value ? 'yes' : 'no'
      }
    }).then(() => {
      setIsChecked(v => !v);
    }).finally(() => {
      setIsLoading(false);
    });
  };

  // Cache sprintf.
  const checkboxLabel = (0,external_wp_element_namespaceObject.useMemo)(() => {
    const label = (0,external_wp_i18n_namespaceObject.sprintf)( /* translators: %S gift cards balance amount */
    (0,external_wp_i18n_namespaceObject.__)('Use %s from your gift cards balance.', 'woocommerce-gift-cards'), '<strong>' + (0,external_wc_priceFormat_namespaceObject.formatPrice)(availableTotal) + '</strong>');
    const pendingOrdersLink = '<a href="' + accountOrdersLink + '">' +
    // TODO: Add link.
    (0,external_wp_i18n_namespaceObject.__)('pending orders', 'woocommerce-gift-cards') + '</a>';
    const pendingText = (0,external_wp_i18n_namespaceObject.sprintf)( /* translators: %1$s pending balance, %2$s pending orders link html */
    (0,external_wp_i18n_namespaceObject.__)('%1$s on hold in %2$s', 'woocommerce-gift-cards'), (0,external_wc_priceFormat_namespaceObject.formatPrice)(pendingTotal), pendingOrdersLink);
    return (0,external_React_namespaceObject.createElement)(external_wp_element_namespaceObject.RawHTML, {
      className: 'wc-gift-cards-checkbox-message'
    }, label, pendingTotal > 0 && '<div class="wc-block-components-totals-item__description">' + pendingText + '</div>');
  }, [availableTotal, pendingTotal, accountOrdersLink]);

  // Render Gift Cards Balance Checkbox.
  return (0,external_React_namespaceObject.createElement)(external_wc_blocksCheckout_namespaceObject.TotalsWrapper, null, (0,external_React_namespaceObject.createElement)("div", {
    className: classnames_default()('wc-gc-balance-checkbox-container', {
      'wc-gc-balance-checkbox-container--disabled': isDisabled
    })
  }, (0,external_React_namespaceObject.createElement)(external_wc_blocksCheckout_namespaceObject.CheckboxControl, {
    className: "wc-gc-balance-checkbox",
    id: "wc-gc-balance-checkbox",
    checked: isChecked,
    onChange: value => onChange(value),
    disabled: isDisabled
  }, checkboxLabel)));
};
/* harmony default export */ var balance_checkbox = (BalanceCheckbox);
;// CONCATENATED MODULE: ./resources/js/frontend/blocks/components/chip/chip.js

/**
 * External dependencies
 */


/**
 * Component used to render a "chip" -- a list item containing some text.
 *
 * Each chip defaults to a list element but this can be customized by providing
 * a wrapperElement.
 *
 * @param {Object} props
 * @param {string} props.text
 * @param {string} props.screenReaderText
 * @param {string} props.element
 * @param {string} props.className
 * @param {string} props.radius
 * @param {Array}  props.children
 */
const Chip = ({
  text,
  screenReaderText = '',
  element = 'li',
  className = '',
  radius = 'small',
  children = null,
  ...props
}) => {
  const Wrapper = element;
  const wrapperClassName = classnames_default()(className, 'wc-block-components-chip', 'wc-block-components-chip--radius-' + radius);
  const showScreenReaderText = Boolean(screenReaderText && screenReaderText !== text);
  return (0,external_React_namespaceObject.createElement)(Wrapper, {
    className: wrapperClassName,
    ...props
  }, (0,external_React_namespaceObject.createElement)("span", {
    "aria-hidden": showScreenReaderText,
    className: "wc-block-components-chip__text"
  }, text), showScreenReaderText && (0,external_React_namespaceObject.createElement)("span", {
    className: "screen-reader-text"
  }, screenReaderText), children);
};
/* harmony default export */ var chip = (Chip);
;// CONCATENATED MODULE: ./node_modules/@wordpress/icons/build-module/library/close-small.js


/**
 * WordPress dependencies
 */

const closeSmall = (0,external_wp_element_namespaceObject.createElement)(external_wp_primitives_namespaceObject.SVG, {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24"
}, (0,external_wp_element_namespaceObject.createElement)(external_wp_primitives_namespaceObject.Path, {
  d: "M12 13.06l3.712 3.713 1.061-1.06L13.061 12l3.712-3.712-1.06-1.06L12 10.938 8.288 7.227l-1.061 1.06L10.939 12l-3.712 3.712 1.06 1.061L12 13.061z"
}));
/* harmony default export */ var close_small = (closeSmall);
//# sourceMappingURL=close-small.js.map
;// CONCATENATED MODULE: ./resources/js/frontend/blocks/components/chip/removable-chip.js

/**
 * External dependencies
 */




/**
 * Internal dependencies
 */



/**
 * Component used to render a "chip" -- an item containing some text with
 * an X button to remove/dismiss each chip.
 *
 * @param {Object}         props                  Incoming props for the component.
 * @param {string}         props.ariaLabel        Aria label content.
 * @param {string}         props.className        CSS class used.
 * @param {boolean}        props.disabled         Whether action is disabled or not.
 * @param {function():any} props.onRemove         Function to call when remove event is fired.
 * @param {boolean}        props.removeOnAnyClick Whether to expand click area for remove event.
 * @param {string}         props.text             The text for the chip.
 * @param {string}         props.screenReaderText The screen reader text for the chip.
 * @param {Object}         props.props            Rest of props passed into component.
 */
const RemovableChip = ({
  ariaLabel = '',
  className = '',
  disabled = false,
  onRemove = () => void 0,
  removeOnAnyClick = false,
  text,
  screenReaderText = '',
  ...props
}) => {
  const RemoveElement = removeOnAnyClick ? 'span' : 'button';
  if (!ariaLabel) {
    const ariaLabelText = screenReaderText && typeof screenReaderText === 'string' ? screenReaderText : text;
    ariaLabel = typeof ariaLabelText !== 'string' ? /* translators: Remove chip. */
    (0,external_wp_i18n_namespaceObject.__)('Remove', 'woocommerce-gift-cards') : (0,external_wp_i18n_namespaceObject.sprintf)( /* translators: %s text of the chip to remove. */
    (0,external_wp_i18n_namespaceObject.__)('Remove gift card "%s"', 'woocommerce-gift-cards'), ariaLabelText);
  }
  const clickableElementProps = {
    'aria-label': ariaLabel,
    disabled,
    onClick: onRemove,
    onKeyDown: e => {
      if (e.key === 'Backspace' || e.key === 'Delete') {
        onRemove();
      }
    }
  };
  const chipProps = removeOnAnyClick ? clickableElementProps : {};
  const removeProps = removeOnAnyClick ? {
    'aria-hidden': true
  } : clickableElementProps;
  return (0,external_React_namespaceObject.createElement)(chip, {
    ...props,
    ...chipProps,
    className: classnames_default()(className, 'is-removable', {
      'is-removing': disabled
    }),
    element: removeOnAnyClick ? 'button' : props.element,
    screenReaderText: screenReaderText,
    text: text
  }, (0,external_React_namespaceObject.createElement)(RemoveElement, {
    className: "wc-block-components-chip__remove",
    ...removeProps
  }, (0,external_React_namespaceObject.createElement)(icon, {
    className: "wc-block-components-chip__remove-icon",
    icon: close_small,
    size: 16
  })));
};
/* harmony default export */ var removable_chip = (RemovableChip);
;// CONCATENATED MODULE: ./resources/js/frontend/blocks/components/chip/index.js


;// CONCATENATED MODULE: ./resources/js/frontend/blocks/components/applied-gift-cards-totals/applied-gift-card.js

/**
 * External dependencies
 */





/**
 * Internal dependencies
 */



/**
 * Displays an applied gift card in the card.
 *
 * @param {Object}   props
 * @param {Object}   props.giftcard     The JS representation of the applied gift card.
 * @param {Function} props.handleRemove The function to handle the removal.
 * @return {JSX.Element} A panel used to display an applied gift card.
 */
const AppliedGiftCard = ({
  giftcard,
  handleRemove
}) => {
  const currency = (0,external_wc_priceFormat_namespaceObject.getCurrency)();
  const [isDisabled, setIsDisabled] = (0,external_wp_element_namespaceObject.useState)(false);
  const {
    id,
    code,
    amount,
    balance,
    pending_message: pendingMessage
  } = giftcard;
  const {
    showRemainingBalance
  } = get_plugin_settings();
  return (0,external_React_namespaceObject.createElement)(external_wc_blocksCheckout_namespaceObject.TotalsWrapper, null, (0,external_React_namespaceObject.createElement)(external_wc_blocksCheckout_namespaceObject.TotalsItem, {
    className: "wc-gift-cards-totals wc-block-components-totals-discount",
    currency: currency,
    label: (0,external_wp_i18n_namespaceObject.__)('Gift Card:', 'woocommerce-gift-cards'),
    value: -1 * parseInt(amount, 10),
    description: (0,external_React_namespaceObject.createElement)(external_React_namespaceObject.Fragment, null, (0,external_React_namespaceObject.createElement)(removable_chip, {
      key: 'gifcard-' + code,
      className: "wc-gift-cards-totals-giftcard__code-list-item",
      text: code,
      element: "div",
      radius: "large",
      screenReaderText: (0,external_wp_i18n_namespaceObject.sprintf)( /* translators: %s Gift card code. */
      (0,external_wp_i18n_namespaceObject.__)('Gift Card: %s', 'woocommerce-gift-cards'), code),
      disabled: isDisabled,
      onRemove: () => {
        setIsDisabled(true);
        handleRemove(id);
      },
      ariaLabel: (0,external_wp_i18n_namespaceObject.sprintf)( /* translators: %s gift card code. */
      (0,external_wp_i18n_namespaceObject.__)('Remove gift card "%s"', 'woocommerce-gift-cards'), code)
    }), pendingMessage && (0,external_React_namespaceObject.createElement)(external_wp_element_namespaceObject.RawHTML, null, pendingMessage), showRemainingBalance && (0,external_React_namespaceObject.createElement)("div", null, (0,external_React_namespaceObject.createElement)("strong", null, (0,external_wp_i18n_namespaceObject.__)('Remaining Balance:', 'woocommerce-gift-cards')), (0,external_React_namespaceObject.createElement)("div", null, (0,external_wc_priceFormat_namespaceObject.formatPrice)(balance - amount))))
  }));
};
/* harmony default export */ var applied_gift_card = (AppliedGiftCard);
;// CONCATENATED MODULE: ./resources/js/frontend/blocks/components/applied-gift-cards-totals/index.js

/**
 * External dependencies
 */





/**
 * Internal dependencies
 */



/**
 * Inline Gift Cards.
 *
 * @param {Object}   props
 * @param {Object}   props.extensions   The extension-added data on the WC Cart.
 * @param {Function} props.createNotice
 * @return {JSX.Element} A panel used to display the inline gift card form.
 */
const AppliedGiftCardTotals = ({
  extensions,
  createNotice
}) => {
  const {
    applied_giftcards: appliedGiftCards
  } = extensions['woocommerce-gift-cards'];
  if (!appliedGiftCards.length) {
    return null;
  }
  const {
    isCheckout
  } = get_plugin_settings();

  /**
   * Function to handle the code removal.
   *
   * @param {number} giftcardId The giftcard ID to remove from session.
   */
  const handleRemove = giftcardId => {
    (0,external_wc_blocksCheckout_namespaceObject.extensionCartUpdate)({
      namespace: 'woocommerce-gift-cards',
      data: {
        action: 'remove_gift_card_from_session',
        wc_gc_remove_id: giftcardId
      }
    }).then(() => {
      const message = (0,external_wp_i18n_namespaceObject.__)('Gift card code has been removed.', 'woocommerce-gift-cards');
      createNotice('default', message, {
        id: 'wc-gift-cards-removed-gift-card',
        type: 'snackbar',
        context: !isCheckout ? 'wc/cart' : 'wc/checkout'
      });
    }).catch(() => {
      // ...
    });
  };
  return appliedGiftCards.map(giftcard => {
    return (0,external_React_namespaceObject.createElement)(applied_gift_card, {
      key: 'giftcard-' + giftcard.id,
      giftcard: giftcard,
      handleRemove: handleRemove
    });
  });
};
/* harmony default export */ var applied_gift_cards_totals = ((0,external_wp_compose_namespaceObject.compose)((0,external_wp_data_namespaceObject.withDispatch)(dispatch => {
  const {
    createNotice
  } = dispatch('core/notices');
  return {
    createNotice
  };
}))(AppliedGiftCardTotals));
;// CONCATENATED MODULE: ./resources/js/frontend/blocks/components/account-gift-cards-totals/index.js

/**
 * External dependencies
 */




/**
 * Internal dependencies
 */


/**
 * Component AccountGiftCardTotals
 *
 * Displays account-related gift cards in the cart/checkout totals table.
 *
 * @param {Object} props
 * @param {Object} props.extensions The extension-added data on the WC Cart.
 * @return {JSX.Element} A panel used to display the inline gift card form.
 */
const AccountGiftCardTotals = ({
  extensions
}) => {
  const {
    account_giftcards: accountGiftCards,
    balance: accountBalance,
    available_total: availableTotal
  } = extensions['woocommerce-gift-cards'];
  if (accountGiftCards.length === 0) {
    return null;
  }
  const currency = (0,external_wc_priceFormat_namespaceObject.getCurrency)();
  const totalBalanceUsed = accountGiftCards.reduce((total, giftcard) => {
    total = total + parseInt(giftcard.amount, 10);
    return total;
  }, 0);
  return (0,external_React_namespaceObject.createElement)(external_React_namespaceObject.Fragment, null, (0,external_React_namespaceObject.createElement)(external_wc_blocksCheckout_namespaceObject.TotalsWrapper, null, (0,external_React_namespaceObject.createElement)(external_wc_blocksCheckout_namespaceObject.TotalsItem, {
    className: "wc-gift-cards-totals wc-block-components-totals-discount",
    currency: currency,
    label: (0,external_wp_i18n_namespaceObject.__)('Gift Cards Balance', 'woocommerce-gift-cards'),
    value: -1 * totalBalanceUsed,
    description: (0,external_React_namespaceObject.createElement)(external_React_namespaceObject.Fragment, null, (0,external_React_namespaceObject.createElement)("strong", null, (0,external_wp_i18n_namespaceObject._n)('Code:', 'Codes:', accountGiftCards.length, 'woocommerce-gift-cards')), (0,external_React_namespaceObject.createElement)("ul", {
      className: "wc-block-components-totals-discount__coupon-list"
    }, accountGiftCards.map(giftcard => {
      return (0,external_React_namespaceObject.createElement)(chip, {
        key: 'account-giftcard-' + giftcard.code,
        className: "wc-block-components-totals-discount__coupon-list-item",
        text: giftcard.code,
        radius: "large",
        screenReaderText: (0,external_wp_i18n_namespaceObject.sprintf)( /* translators: %s Gift card code. */
        (0,external_wp_i18n_namespaceObject.__)('Gift Card: %s', 'woocommerce-gift-cards'), giftcard.code)
      });
    })), (0,external_React_namespaceObject.createElement)("strong", null, (0,external_wp_i18n_namespaceObject.__)('Remaining Balance:', 'woocommerce-gift-cards')), (0,external_React_namespaceObject.createElement)("div", null, (0,external_wc_priceFormat_namespaceObject.formatPrice)(accountBalance - availableTotal)))
  })));
};
/* harmony default export */ var account_gift_cards_totals = (AccountGiftCardTotals);
;// CONCATENATED MODULE: ./resources/js/frontend/blocks/components/totals-item/index.js

/**
 * External dependencies
 */




/**
 * Displays the total before Gift Cards in the cart/checkout totals table.
 *
 * @param {Object} props
 * @param {Object} props.extensions The extension-added data on the WC Cart.
 * @return {JSX.Element} The account balance checkbox.
 */
const TotalBeforeGiftCardsItem = ({
  extensions
}) => {
  const notApplicable = !extensions || !extensions.hasOwnProperty('woocommerce-gift-cards') || !extensions['woocommerce-gift-cards'].hasOwnProperty('account_giftcards');
  if (notApplicable === true) {
    return null;
  }
  const {
    cart_total: cartTotal,
    applied_giftcards: appliedGiftCards,
    account_giftcards: accountGiftCards
  } = extensions['woocommerce-gift-cards'];
  if (!appliedGiftCards.length && !accountGiftCards.length) {
    return null;
  }
  const currency = (0,external_wc_priceFormat_namespaceObject.getCurrency)();
  return (0,external_React_namespaceObject.createElement)(external_wc_blocksCheckout_namespaceObject.TotalsWrapper, null, (0,external_React_namespaceObject.createElement)(external_wc_blocksCheckout_namespaceObject.TotalsItem, {
    className: "wc-gift-cards-totals wc-block-components-totals-footer-item",
    currency: currency,
    label: (0,external_wp_i18n_namespaceObject.__)('Total', 'woocommerce-gift-cards'),
    value: parseInt(cartTotal, 10),
    description: (0,external_wp_i18n_namespaceObject.__)('(before gift cards)', 'woocommerce-gift-cards')
  }));
};
/* harmony default export */ var totals_item = (TotalBeforeGiftCardsItem);
;// CONCATENATED MODULE: ./resources/js/frontend/blocks/components/gift-cards-totals/index.js

/**
 * Internal dependencies
 */






/**
 * Displays gift card related totals table.
 *
 * @param {Object} props
 * @param {Object} props.cart       The JS representation of the user's shopping cart.
 * @param {Object} props.extensions The extension-added data on the WC Cart.
 * @return {JSX.Element} The gift card totals.
 */
const GiftCardsTotals = props => {
  const notApplicable = !props.extensions || !props.extensions.hasOwnProperty('woocommerce-gift-cards') || !props?.extensions['woocommerce-gift-cards'].hasOwnProperty('account_giftcards');
  if (notApplicable === true) {
    return null;
  }
  const {
    balance: accountBalance,
    available_total: availableTotal
  } = props.extensions['woocommerce-gift-cards'];
  const {
    isRedeemingEnabled,
    showBalanceCheckbox
  } = get_plugin_settings();
  const showAccountRelatedUI = accountBalance > 0 && isRedeemingEnabled && showBalanceCheckbox;
  return (0,external_React_namespaceObject.createElement)(external_React_namespaceObject.Fragment, null, (0,external_React_namespaceObject.createElement)(totals_item, {
    ...props
  }), (0,external_React_namespaceObject.createElement)(applied_gift_cards_totals, {
    ...props
  }), showAccountRelatedUI && availableTotal > 0 && (0,external_React_namespaceObject.createElement)(balance_checkbox, {
    ...props
  }), showAccountRelatedUI && (0,external_React_namespaceObject.createElement)(account_gift_cards_totals, {
    ...props
  }));
};
/* harmony default export */ var gift_cards_totals = (GiftCardsTotals);
;// CONCATENATED MODULE: ./resources/js/frontend/blocks/inner-blocks/checkout-order-summary-gift-card-totals/block.js

/**
 * Internal dependencies
 */

const block_Block = props => {
  const {
    className
  } = props;
  return (0,external_React_namespaceObject.createElement)("div", {
    className: className
  }, (0,external_React_namespaceObject.createElement)(gift_cards_totals, {
    ...props
  }));
};
/* harmony default export */ var checkout_order_summary_gift_card_totals_block = (block_Block);
;// CONCATENATED MODULE: ./resources/js/frontend/blocks/inner-blocks/checkout-order-summary-gift-card-totals/block.json
var inner_blocks_checkout_order_summary_gift_card_totals_block_namespaceObject = JSON.parse('{"name":"woocommerce/checkout-order-summary-gift-card-totals-block","version":"1.0.0","title":"Gift Card Totals","description":"Shows the gift card totals.","category":"woocommerce","supports":{"align":false,"html":false,"multiple":false,"reusable":false},"attributes":{"className":{"type":"string","default":""},"lock":{"type":"object","default":{"remove":true,"move":false}}},"parent":["woocommerce/checkout-order-summary-block"],"textdomain":"woocommerce-gift-cards","apiVersion":2}');
;// CONCATENATED MODULE: ./resources/js/frontend/blocks/inner-blocks/checkout-order-summary-gift-card-totals/edit.js

/**
 * External dependencies
 */





/**
 * Internal dependencies
 */


const edit_Edit = ({
  attributes
}) => {
  const {
    className
  } = attributes;
  const blockProps = (0,external_wp_blockEditor_namespaceObject.useBlockProps)();

  // TODO: Use preview data.
  const props = {
    extensions: {
      'woocommerce-gift-cards': {
        applied_giftcards: [],
        account_giftcards: [],
        available_total: '3080',
        balance: '3960',
        cart_total: '3080'
      }
    }
  };
  return (0,external_React_namespaceObject.createElement)("div", {
    ...blockProps
  }, (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.Disabled, null, (0,external_React_namespaceObject.createElement)(checkout_order_summary_gift_card_totals_block, {
    className: className,
    ...props
  })));
};
const edit_Save = () => {
  return (0,external_React_namespaceObject.createElement)("div", {
    ...external_wp_blockEditor_namespaceObject.useBlockProps.save()
  });
};
const edit_registerInnerBlock = () => {
  // Register Block in the Editor.
  (0,external_wp_blocks_namespaceObject.registerBlockType)(inner_blocks_checkout_order_summary_gift_card_totals_block_namespaceObject.name, {
    ...inner_blocks_checkout_order_summary_gift_card_totals_block_namespaceObject,
    icon: {
      src: (0,external_React_namespaceObject.createElement)(icon, {
        icon: library_percent,
        className: "wc-block-editor-components-block-icon"
      })
    },
    edit: edit_Edit,
    save: edit_Save,
    apiVersion: 3
  });
};
;// CONCATENATED MODULE: ./resources/js/frontend/blocks/inner-blocks/cart-order-summary-gift-card-form/block.js

/**
 * External dependencies
 */


/**
 * Internal dependencies
 */

const cart_order_summary_gift_card_form_block_Block = props => {
  const {
    className,
    extensions
  } = props;
  return (0,external_React_namespaceObject.createElement)(external_wc_blocksCheckout_namespaceObject.TotalsWrapper, {
    className: className
  }, (0,external_React_namespaceObject.createElement)(gift_cards_form, {
    extensions: extensions
  }));
};
/* harmony default export */ var cart_order_summary_gift_card_form_block = (cart_order_summary_gift_card_form_block_Block);
;// CONCATENATED MODULE: ./resources/js/frontend/blocks/inner-blocks/cart-order-summary-gift-card-form/block.json
var inner_blocks_cart_order_summary_gift_card_form_block_namespaceObject = JSON.parse('{"name":"woocommerce/cart-order-summary-gift-card-form-block","version":"1.0.0","title":"Gift Card Form","description":"Shows the apply gift card form.","category":"woocommerce","supports":{"align":false,"html":false,"multiple":false,"reusable":false},"attributes":{"className":{"type":"string","default":""},"lock":{"type":"object","default":{"remove":true,"move":false}}},"parent":["woocommerce/cart-order-summary-block"],"textdomain":"woocommerce-gift-cards","apiVersion":2}');
;// CONCATENATED MODULE: ./resources/js/frontend/blocks/inner-blocks/cart-order-summary-gift-card-form/edit.js

/**
 * External dependencies
 */





/**
 * Internal dependencies
 */


const cart_order_summary_gift_card_form_edit_Edit = ({
  attributes
}) => {
  const {
    className
  } = attributes;
  const blockProps = (0,external_wp_blockEditor_namespaceObject.useBlockProps)();

  // Make sure that the Block will be applicable to render.
  const props = {
    extensions: {
      'woocommerce-gift-cards': {
        account_giftcards: []
      }
    }
  };
  return (0,external_React_namespaceObject.createElement)("div", {
    ...blockProps
  }, (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.Disabled, null, (0,external_React_namespaceObject.createElement)(cart_order_summary_gift_card_form_block, {
    className: className,
    ...props
  })));
};
const cart_order_summary_gift_card_form_edit_Save = () => {
  return (0,external_React_namespaceObject.createElement)("div", {
    ...external_wp_blockEditor_namespaceObject.useBlockProps.save()
  });
};
const cart_order_summary_gift_card_form_edit_registerInnerBlock = () => {
  // Register Block in the Editor.
  (0,external_wp_blocks_namespaceObject.registerBlockType)(inner_blocks_cart_order_summary_gift_card_form_block_namespaceObject.name, {
    ...inner_blocks_cart_order_summary_gift_card_form_block_namespaceObject,
    icon: {
      src: (0,external_React_namespaceObject.createElement)(icon, {
        icon: library_tag,
        className: "wc-block-editor-components-block-icon"
      })
    },
    edit: cart_order_summary_gift_card_form_edit_Edit,
    save: cart_order_summary_gift_card_form_edit_Save,
    apiVersion: 3
  });
};
;// CONCATENATED MODULE: ./resources/js/frontend/blocks/inner-blocks/cart-order-summary-gift-card-totals/block.js

/**
 * Internal dependencies
 */

const cart_order_summary_gift_card_totals_block_Block = props => {
  const {
    className
  } = props;
  return (0,external_React_namespaceObject.createElement)("div", {
    className: className
  }, (0,external_React_namespaceObject.createElement)(gift_cards_totals, {
    ...props
  }));
};
/* harmony default export */ var cart_order_summary_gift_card_totals_block = (cart_order_summary_gift_card_totals_block_Block);
;// CONCATENATED MODULE: ./resources/js/frontend/blocks/inner-blocks/cart-order-summary-gift-card-totals/block.json
var inner_blocks_cart_order_summary_gift_card_totals_block_namespaceObject = JSON.parse('{"name":"woocommerce/cart-order-summary-gift-card-totals-block","version":"1.0.0","title":"Gift Card Totals","description":"Shows the gift card totals.","category":"woocommerce","supports":{"align":false,"html":false,"multiple":false,"reusable":false},"attributes":{"className":{"type":"string","default":""},"lock":{"type":"object","default":{"remove":true,"move":false}}},"parent":["woocommerce/cart-order-summary-block"],"textdomain":"woocommerce-gift-cards","apiVersion":2}');
;// CONCATENATED MODULE: ./resources/js/frontend/blocks/inner-blocks/cart-order-summary-gift-card-totals/edit.js

/**
 * External dependencies
 */





/**
 * Internal dependencies
 */


const cart_order_summary_gift_card_totals_edit_Edit = ({
  attributes
}) => {
  const {
    className
  } = attributes;
  const blockProps = (0,external_wp_blockEditor_namespaceObject.useBlockProps)();
  const props = {
    extensions: {
      'woocommerce-gift-cards': {
        applied_giftcards: [],
        account_giftcards: [],
        available_total: '3080',
        balance: '3960',
        cart_total: '3080'
      }
    }
  };
  return (0,external_React_namespaceObject.createElement)("div", {
    ...blockProps
  }, (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.Disabled, null, (0,external_React_namespaceObject.createElement)(cart_order_summary_gift_card_totals_block, {
    className: className,
    ...props
  })));
};
const cart_order_summary_gift_card_totals_edit_Save = () => {
  return (0,external_React_namespaceObject.createElement)("div", {
    ...external_wp_blockEditor_namespaceObject.useBlockProps.save()
  });
};
const cart_order_summary_gift_card_totals_edit_registerInnerBlock = () => {
  // Register Block in the Editor.
  (0,external_wp_blocks_namespaceObject.registerBlockType)(inner_blocks_cart_order_summary_gift_card_totals_block_namespaceObject.name, {
    ...inner_blocks_cart_order_summary_gift_card_totals_block_namespaceObject,
    icon: {
      src: (0,external_React_namespaceObject.createElement)(icon, {
        icon: library_percent,
        className: "wc-block-editor-components-block-icon"
      })
    },
    edit: cart_order_summary_gift_card_totals_edit_Edit,
    save: cart_order_summary_gift_card_totals_edit_Save,
    apiVersion: 3
  });
};
;// CONCATENATED MODULE: ./resources/js/admin/blocks/index.js
/**
 * Internal dependencies
 */







/**
 * Gift Card Inner blocks registry controller.
 */
const registerInnerBlocks = () => {
  const {
    isCartDisabled,
    isUiDisabled
  } = get_plugin_settings();
  if (isUiDisabled) {
    return;
  }
  registerInnerBlock();
  edit_registerInnerBlock();
  if (!isCartDisabled) {
    cart_order_summary_gift_card_form_edit_registerInnerBlock();
    cart_order_summary_gift_card_totals_edit_registerInnerBlock();
  }
};

/**
 * Register.
 */
registerInnerBlocks();
}();
/******/ })()
;