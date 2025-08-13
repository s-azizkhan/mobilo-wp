/******/ (function() { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ 703:
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

"use strict";
/**
 * Copyright (c) 2013-present, Facebook, Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */



var ReactPropTypesSecret = __webpack_require__(414);

function emptyFunction() {}
function emptyFunctionWithReset() {}
emptyFunctionWithReset.resetWarningCache = emptyFunction;

module.exports = function() {
  function shim(props, propName, componentName, location, propFullName, secret) {
    if (secret === ReactPropTypesSecret) {
      // It is still safe when called from React.
      return;
    }
    var err = new Error(
      'Calling PropTypes validators directly is not supported by the `prop-types` package. ' +
      'Use PropTypes.checkPropTypes() to call them. ' +
      'Read more at http://fb.me/use-check-prop-types'
    );
    err.name = 'Invariant Violation';
    throw err;
  };
  shim.isRequired = shim;
  function getShim() {
    return shim;
  };
  // Important!
  // Keep this list in sync with production version in `./factoryWithTypeCheckers.js`.
  var ReactPropTypes = {
    array: shim,
    bigint: shim,
    bool: shim,
    func: shim,
    number: shim,
    object: shim,
    string: shim,
    symbol: shim,

    any: shim,
    arrayOf: getShim,
    element: shim,
    elementType: shim,
    instanceOf: getShim,
    node: shim,
    objectOf: getShim,
    oneOf: getShim,
    oneOfType: getShim,
    shape: getShim,
    exact: getShim,

    checkPropTypes: emptyFunctionWithReset,
    resetWarningCache: emptyFunction
  };

  ReactPropTypes.PropTypes = ReactPropTypes;

  return ReactPropTypes;
};


/***/ }),

/***/ 697:
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

/**
 * Copyright (c) 2013-present, Facebook, Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */

if (false) { var throwOnDirectAccess, ReactIs; } else {
  // By explicitly using `prop-types` you are opting into new production behavior.
  // http://fb.me/prop-types-in-prod
  module.exports = __webpack_require__(703)();
}


/***/ }),

/***/ 414:
/***/ (function(module) {

"use strict";
/**
 * Copyright (c) 2013-present, Facebook, Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */



var ReactPropTypesSecret = 'SECRET_DO_NOT_PASS_THIS_OR_YOU_WILL_BE_FIRED';

module.exports = ReactPropTypesSecret;


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

;// CONCATENATED MODULE: external ["wp","hooks"]
var external_wp_hooks_namespaceObject = window["wp"]["hooks"];
;// CONCATENATED MODULE: external ["wp","i18n"]
var external_wp_i18n_namespaceObject = window["wp"]["i18n"];
;// CONCATENATED MODULE: external "React"
var external_React_namespaceObject = window["React"];
;// CONCATENATED MODULE: external ["wp","element"]
var external_wp_element_namespaceObject = window["wp"]["element"];
// EXTERNAL MODULE: ./node_modules/prop-types/index.js
var prop_types = __webpack_require__(697);
var prop_types_default = /*#__PURE__*/__webpack_require__.n(prop_types);
;// CONCATENATED MODULE: external ["wc","components"]
var external_wc_components_namespaceObject = window["wc"]["components"];
;// CONCATENATED MODULE: ./resources/js/admin/analytics/report/config.js
/**
 * External dependencies.
 */


/**
 * Exports.
 */
const filters = [{
  label: (0,external_wp_i18n_namespaceObject.__)('Show Gift Cards', 'woocommerce-gift-cards'),
  staticParams: ['filter'],
  param: 'section',
  showFilters: () => true,
  filters: [{
    label: (0,external_wp_i18n_namespaceObject._x)('Used', 'analytics filter label', 'woocommerce-gift-cards'),
    value: 'used'
  }, {
    label: (0,external_wp_i18n_namespaceObject._x)('Issued', 'analytics filter label', 'woocommerce-gift-cards'),
    value: 'issued'
  }, {
    label: (0,external_wp_i18n_namespaceObject._x)('Expired', 'analytics filter label', 'woocommerce-gift-cards'),
    value: 'expired'
  }]
}];
;// CONCATENATED MODULE: ./resources/js/admin/analytics/report/issued/config.js
/**
 * External dependencies.
 */


/**
 * Exports.
 */
const ENDPOINT = 'giftcards-issued';
// export const ENDPOINT = 'gift-cards-issued';
const charts = [{
  key: 'giftcards_count',
  label: (0,external_wp_i18n_namespaceObject.__)('Issued Gift Cards', 'woocommerce-gift-cards'),
  order: 'desc',
  orderby: 'date',
  type: 'number'
}, {
  key: 'issued_balance',
  label: (0,external_wp_i18n_namespaceObject.__)('Issued Amount', 'woocommerce-gift-cards'),
  order: 'desc',
  orderby: 'issued_balance',
  type: 'currency'
}];
;// CONCATENATED MODULE: external ["wp","compose"]
var external_wp_compose_namespaceObject = window["wp"]["compose"];
;// CONCATENATED MODULE: external ["wp","htmlEntities"]
var external_wp_htmlEntities_namespaceObject = window["wp"]["htmlEntities"];
;// CONCATENATED MODULE: external ["wp","data"]
var external_wp_data_namespaceObject = window["wp"]["data"];
;// CONCATENATED MODULE: external "lodash"
var external_lodash_namespaceObject = window["lodash"];
;// CONCATENATED MODULE: external ["wc","navigation"]
var external_wc_navigation_namespaceObject = window["wc"]["navigation"];
;// CONCATENATED MODULE: external ["wc","number"]
var external_wc_number_namespaceObject = window["wc"]["number"];
;// CONCATENATED MODULE: external ["wc","wcSettings"]
var external_wc_wcSettings_namespaceObject = window["wc"]["wcSettings"];
;// CONCATENATED MODULE: external ["wc","date"]
var external_wc_date_namespaceObject = window["wc"]["date"];
;// CONCATENATED MODULE: external ["wc","data"]
var external_wc_data_namespaceObject = window["wc"]["data"];
;// CONCATENATED MODULE: external ["wp","components"]
var external_wp_components_namespaceObject = window["wp"]["components"];
;// CONCATENATED MODULE: external ["wp","dom"]
var external_wp_dom_namespaceObject = window["wp"]["dom"];
;// CONCATENATED MODULE: external ["wc","csvExport"]
var external_wc_csvExport_namespaceObject = window["wc"]["csvExport"];
;// CONCATENATED MODULE: ./node_modules/@somewherewarm/woocommerce/packages/components/report-table/download-icon.js

/* harmony default export */ var download_icon = (() => (0,external_React_namespaceObject.createElement)("svg", {
  role: "img",
  "aria-hidden": "true",
  focusable: "false",
  version: "1.1",
  xmlns: "http://www.w3.org/2000/svg",
  x: "0px",
  y: "0px",
  viewBox: "0 0 24 24"
}, (0,external_React_namespaceObject.createElement)("path", {
  d: "M18,9c-0.009,0-0.017,0.002-0.025,0.003C17.72,5.646,14.922,3,11.5,3C7.91,3,5,5.91,5,9.5c0,0.524,0.069,1.031,0.186,1.519 C5.123,11.016,5.064,11,5,11c-2.209,0-4,1.791-4,4c0,1.202,0.541,2.267,1.38,3h18.593C22.196,17.089,23,15.643,23,14 C23,11.239,20.761,9,18,9z M12,16l-4-5h3V8h2v3h3L12,16z"
})));
;// CONCATENATED MODULE: ./node_modules/@somewherewarm/woocommerce/packages/components/report-error/index.js

/**
 * External dependencies
 */






/**
 * Component to render when there is an error in a report component due to data
 * not being loaded or being invalid.
 */
class report_error_ReportError extends external_wp_element_namespaceObject.Component {
  render() {
    const {
      className,
      isError,
      isEmpty
    } = this.props;
    let title, actionLabel, actionURL, actionCallback;
    if (isError) {
      title = (0,external_wp_i18n_namespaceObject.__)('There was an error getting your stats. Please try again.', 'woocommerce-product-bundles');
      actionLabel = (0,external_wp_i18n_namespaceObject.__)('Reload', 'woocommerce-product-bundles');
      actionCallback = () => {
        window.location.reload();
      };
    } else if (isEmpty) {
      title = (0,external_wp_i18n_namespaceObject.__)('No results could be found for this date range.', 'woocommerce-product-bundles');
      actionLabel = (0,external_wp_i18n_namespaceObject.__)('View Orders', 'woocommerce-product-bundles');
      actionURL = (0,external_wc_wcSettings_namespaceObject.getAdminLink)('edit.php?post_type=shop_order');
    }
    return (0,external_React_namespaceObject.createElement)(external_wc_components_namespaceObject.EmptyContent, {
      className: className,
      title: title,
      actionLabel: actionLabel,
      actionURL: actionURL,
      actionCallback: actionCallback
    });
  }
}
report_error_ReportError.propTypes = {
  /**
   * Additional class name to style the component.
   */
  className: (prop_types_default()).string,
  /**
   * Boolean representing whether there was an error.
   */
  isError: (prop_types_default()).bool,
  /**
   * Boolean representing whether the issue is that there is no data.
   */
  isEmpty: (prop_types_default()).bool
};
report_error_ReportError.defaultProps = {
  className: ''
};
/* harmony default export */ var report_error = (report_error_ReportError);
;// CONCATENATED MODULE: ./node_modules/@somewherewarm/woocommerce/packages/components/report-table/utils.js
/**
 * External dependencies
 */

function extendTableData(select, props, queriedTableData) {
  const {
    extendItemsMethodNames,
    extendedItemsStoreName,
    itemIdField
  } = props;
  const itemsData = queriedTableData.items.data;
  if (!Array.isArray(itemsData) || !itemsData.length || !extendItemsMethodNames || !itemIdField) {
    return queriedTableData;
  }
  const {
    [extendItemsMethodNames.getError]: getErrorMethod,
    [extendItemsMethodNames.isRequesting]: isRequestingMethod,
    [extendItemsMethodNames.load]: loadMethod
  } = select(extendedItemsStoreName);
  const extendQuery = {
    include: itemsData.map(item => item[itemIdField]).join(','),
    per_page: itemsData.length
  };
  const extendedItems = loadMethod(extendQuery);
  const isExtendedItemsRequesting = isRequestingMethod ? isRequestingMethod(extendQuery) : false;
  const isExtendedItemsError = getErrorMethod ? getErrorMethod(extendQuery) : false;
  const extendedItemsData = itemsData.map(item => {
    const extendedItemData = first(extendedItems.filter(extendedItem => item.id === extendedItem.id));
    return {
      ...item,
      ...extendedItemData
    };
  });
  const isRequesting = queriedTableData.isRequesting || isExtendedItemsRequesting;
  const isError = queriedTableData.isError || isExtendedItemsError;
  return {
    ...queriedTableData,
    isRequesting,
    isError,
    items: {
      ...queriedTableData.items,
      data: extendedItemsData
    }
  };
}
;// CONCATENATED MODULE: ./node_modules/@somewherewarm/woocommerce/packages/components/report-table/index.js

/**
 * External dependencies
 */














/**
 * Internal dependencies
 */



const TABLE_FILTER = 'woocommerce_admin_report_table';
const ReportTable = props => {
  const {
    getHeadersContent,
    getRowsContent,
    getSummary,
    isRequesting,
    primaryData,
    tableData,
    endpoint,
    // These props are not used in the render function, but are destructured
    // so they are not included in the `tableProps` variable.
    // eslint-disable-next-line no-unused-vars
    itemIdField,
    // eslint-disable-next-line no-unused-vars
    tableQuery,
    compareBy,
    compareParam,
    searchBy,
    labels = {},
    ...tableProps
  } = props;

  // Pull these props out separately because they need to be included in tableProps.
  const {
    query,
    columnPrefsKey
  } = props;
  const {
    items,
    query: reportQuery
  } = tableData;
  const initialSelectedRows = query[compareParam] ? (0,external_wc_navigation_namespaceObject.getIdsFromQuery)(query[compareBy]) : [];
  const [selectedRows, setSelectedRows] = (0,external_wp_element_namespaceObject.useState)(initialSelectedRows);
  const scrollPointRef = (0,external_wp_element_namespaceObject.useRef)(null);
  const {
    updateUserPreferences,
    ...userData
  } = (0,external_wc_data_namespaceObject.useUserPreferences)();

  // Bail early if we've encountered an error.
  const isError = tableData.isError || primaryData.isError;
  if (isError) {
    return (0,external_React_namespaceObject.createElement)(report_error, {
      isError: true
    });
  }
  let userPrefColumns = [];
  if (columnPrefsKey) {
    userPrefColumns = userData && userData[columnPrefsKey] ? userData[columnPrefsKey] : userPrefColumns;
  }
  const onPageChange = (newPage, source) => {
    scrollPointRef.current.scrollIntoView();
    const tableElement = scrollPointRef.current.nextSibling.querySelector('.woocommerce-table__table');
    const focusableElements = external_wp_dom_namespaceObject.focus.focusable.find(tableElement);
    if (focusableElements.length) {
      focusableElements[0].focus();
    }
  };
  const onSort = (key, direction) => {
    (0,external_wc_navigation_namespaceObject.onQueryChange)('sort')(key, direction);
    const eventProps = {
      report: endpoint,
      column: key,
      direction
    };
  };
  const filterShownHeaders = (headers, hiddenKeys) => {
    // If no user preferences, set visibilty based on column default.
    if (!hiddenKeys) {
      return headers.map(header => ({
        ...header,
        visible: header.required || !header.hiddenByDefault
      }));
    }

    // Set visibilty based on user preferences.
    return headers.map(header => ({
      ...header,
      visible: header.required || !hiddenKeys.includes(header.key)
    }));
  };
  const applyTableFilters = (data, totals, totalResults) => {
    const summary = getSummary ? getSummary(totals, totalResults) : null;

    /**
     * Filter report table for the CSV download.
     *
     * Enables manipulation of data used to create the report CSV.
     *
     * @param {Object} reportTableData - data used to create the table.
     * @param {string} reportTableData.endpoint - table api endpoint.
     * @param {Array} reportTableData.headers - table headers data.
     * @param {Array} reportTableData.rows - table rows data.
     * @param {Object} reportTableData.totals - total aggregates for request.
     * @param {Array} reportTableData.summary - summary numbers data.
     * @param {Object} reportTableData.items - response from api requerst.
     */
    return (0,external_wp_hooks_namespaceObject.applyFilters)(TABLE_FILTER, {
      endpoint,
      headers: getHeadersContent(),
      rows: getRowsContent(data),
      totals,
      summary,
      items
    });
  };
  const onClickDownload = () => {
    const {
      createNotice,
      startExport,
      title
    } = props;
    const params = Object.assign({}, query);
    const {
      data,
      totalResults
    } = items;
    let downloadType = 'browser';

    // Delete unnecessary items from filename.
    delete params.extended_info;
    if (params.search) {
      delete params[searchBy];
    }
    if (data && data.length === totalResults) {
      const {
        headers,
        rows
      } = applyTableFilters(data, totalResults);
      (0,external_wc_csvExport_namespaceObject.downloadCSVFile)((0,external_wc_csvExport_namespaceObject.generateCSVFileName)(title, params), (0,external_wc_csvExport_namespaceObject.generateCSVDataFromTable)(headers, rows));
    } else {
      downloadType = 'email';
      startExport(endpoint, reportQuery).then(() => createNotice('success', (0,external_wp_i18n_namespaceObject.sprintf)( /* translators: %s = type of report */
      (0,external_wp_i18n_namespaceObject.__)('Your %s Report will be emailed to you.', 'woocommerce-admin'), title))).catch(error => createNotice('error', error.message || (0,external_wp_i18n_namespaceObject.sprintf)( /* translators: %s = type of report */
      (0,external_wp_i18n_namespaceObject.__)('There was a problem exporting your %s Report. Please try again.', 'woocommerce-admin'), title)));
    }
  };
  const onCompare = () => {
    if (compareBy) {
      (0,external_wc_navigation_namespaceObject.onQueryChange)('compare')(compareBy, compareParam, selectedRows.join(','));
    }
  };
  const onSearchChange = values => {
    const {
      baseSearchQuery
    } = props;
    // A comma is used as a separator between search terms, so we want to escape
    // any comma they contain.
    const searchTerms = values.map(v => v.label.replace(',', '%2C'));
    if (searchTerms.length) {
      (0,external_wc_navigation_namespaceObject.updateQueryString)({
        filter: undefined,
        [compareParam]: undefined,
        [searchBy]: undefined,
        ...baseSearchQuery,
        search: (0,external_lodash_namespaceObject.uniq)(searchTerms).join(',')
      });
    } else {
      (0,external_wc_navigation_namespaceObject.updateQueryString)({
        search: undefined
      });
    }
  };
  const selectAllRows = checked => {
    const {
      ids
    } = props;
    setSelectedRows(checked ? ids : []);
  };
  const selectRow = (i, checked) => {
    const {
      ids
    } = props;
    if (checked) {
      setSelectedRows((0,external_lodash_namespaceObject.uniq)([ids[i], ...selectedRows]));
    } else {
      const index = selectedRows.indexOf(ids[i]);
      setSelectedRows([...selectedRows.slice(0, index), ...selectedRows.slice(index + 1)]);
    }
  };
  const getCheckbox = i => {
    const {
      ids = []
    } = props;
    const isChecked = selectedRows.indexOf(ids[i]) !== -1;
    return {
      display: (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.CheckboxControl, {
        onChange: (0,external_lodash_namespaceObject.partial)(selectRow, i),
        checked: isChecked
      }),
      value: false
    };
  };
  const getAllCheckbox = () => {
    const {
      ids = []
    } = props;
    const hasData = ids.length > 0;
    const isAllChecked = hasData && ids.length === selectedRows.length;
    return {
      cellClassName: 'is-checkbox-column',
      key: 'compare',
      label: (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.CheckboxControl, {
        onChange: selectAllRows,
        "aria-label": (0,external_wp_i18n_namespaceObject.__)('Select All'),
        checked: isAllChecked,
        disabled: !hasData
      }),
      required: true
    };
  };
  const isLoading = isRequesting || tableData.isRequesting || primaryData.isRequesting;
  const totals = (0,external_lodash_namespaceObject.get)(primaryData, ['data', 'totals'], {});
  const totalResults = items.totalResults || 0;
  const downloadable = totalResults > 0;
  // Search words are in the query string, not the table query.
  const searchWords = (0,external_wc_navigation_namespaceObject.getSearchWords)(query);
  const searchedLabels = searchWords.map(v => ({
    key: v,
    label: v
  }));
  const {
    data
  } = items;
  const applyTableFiltersResult = applyTableFilters(data, totals, totalResults);
  let {
    headers,
    rows
  } = applyTableFiltersResult;
  const {
    summary
  } = applyTableFiltersResult;
  const onColumnsChange = (shownColumns, toggledColumn) => {
    const columns = headers.map(header => header.key);
    const hiddenColumns = columns.filter(column => !shownColumns.includes(column));
    if (columnPrefsKey) {
      const userDataFields = {
        [columnPrefsKey]: hiddenColumns
      };
      updateUserPreferences(userDataFields);
    }
  };

  // Add in selection for comparisons.
  if (compareBy) {
    rows = rows.map((row, i) => {
      return [getCheckbox(i), ...row];
    });
    headers = [getAllCheckbox(), ...headers];
  }

  // Hide any headers based on user prefs, if loaded.
  const filteredHeaders = filterShownHeaders(headers, userPrefColumns);
  return (0,external_React_namespaceObject.createElement)(external_wp_element_namespaceObject.Fragment, null, (0,external_React_namespaceObject.createElement)("div", {
    className: "woocommerce-report-table__scroll-point",
    ref: scrollPointRef,
    "aria-hidden": true
  }), (0,external_React_namespaceObject.createElement)(external_wc_components_namespaceObject.TableCard, {
    className: ('woocommerce-report-table', 'woocommerce-report-table-' + endpoint.replace('/', '-')),
    hasSearch: !!searchBy,
    actions: [compareBy && (0,external_React_namespaceObject.createElement)(external_wc_components_namespaceObject.CompareButton, {
      key: "compare",
      className: "woocommerce-table__compare",
      count: selectedRows.length,
      helpText: labels.helpText || (0,external_wp_i18n_namespaceObject.__)('Check at least two items below to compare', 'woocommerce-admin'),
      onClick: onCompare,
      disabled: !downloadable
    }, labels.compareButton || (0,external_wp_i18n_namespaceObject.__)('Compare', 'woocommerce-admin')), searchBy && (0,external_React_namespaceObject.createElement)(external_wc_components_namespaceObject.Search, {
      allowFreeTextSearch: true,
      inlineTags: true,
      key: "search",
      onChange: onSearchChange,
      placeholder: labels.placeholder || (0,external_wp_i18n_namespaceObject.__)('Search by item name', 'woocommerce-admin'),
      selected: searchedLabels,
      showClearButton: true,
      type: searchBy,
      disabled: !downloadable
    }), downloadable && (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.Button, {
      key: "download",
      className: "woocommerce-table__download-button",
      disabled: isLoading,
      onClick: onClickDownload
    }, (0,external_React_namespaceObject.createElement)(download_icon, null), (0,external_React_namespaceObject.createElement)("span", {
      className: "woocommerce-table__download-button__label"
    }, labels.downloadButton || (0,external_wp_i18n_namespaceObject.__)('Download', 'woocommerce-admin')))],
    headers: filteredHeaders,
    isLoading: isLoading,
    onQueryChange: external_wc_navigation_namespaceObject.onQueryChange,
    onColumnsChange: onColumnsChange,
    onSort: onSort,
    onPageChange: onPageChange,
    rows: rows,
    rowsPerPage: parseInt(reportQuery.per_page, 10) || external_wc_data_namespaceObject.QUERY_DEFAULTS.pageSize,
    summary: summary,
    totalRows: totalResults,
    ...tableProps
  }));
};
ReportTable.propTypes = {
  /**
   * Pass in query parameters to be included in the path when onSearch creates a new url.
   */
  baseSearchQuery: (prop_types_default()).object,
  /**
   * The string to use as a query parameter when comparing row items.
   */
  compareBy: (prop_types_default()).string,
  /**
   * Url query parameter compare function operates on
   */
  compareParam: (prop_types_default()).string,
  /**
   * The key for user preferences settings for column visibility.
   */
  columnPrefsKey: (prop_types_default()).string,
  /**
   * The endpoint to use in API calls to populate the table rows and summary.
   * For example, if `taxes` is provided, data will be fetched from the report
   * `taxes` endpoint (ie: `/wc-analytics/reports/taxes` and `/wc/v4/reports/taxes/stats`).
   * If the provided endpoint doesn't exist, an error will be shown to the user
   * with `ReportError`.
   */
  endpoint: (prop_types_default()).string,
  /**
   * A function that returns the headers object to build the table.
   */
  getHeadersContent: (prop_types_default()).func.isRequired,
  /**
   * A function that returns the rows array to build the table.
   */
  getRowsContent: (prop_types_default()).func.isRequired,
  /**
   * A function that returns the summary object to build the table.
   */
  getSummary: (prop_types_default()).func,
  /**
   * The name of the property in the item object which contains the id.
   */
  itemIdField: (prop_types_default()).string,
  /**
   * Custom labels for table header actions.
   */
  labels: prop_types_default().shape({
    compareButton: (prop_types_default()).string,
    downloadButton: (prop_types_default()).string,
    helpText: (prop_types_default()).string,
    placeholder: (prop_types_default()).string
  }),
  /**
   * Primary data of that report. If it's not provided, it will be automatically
   * loaded via the provided `endpoint`.
   */
  primaryData: (prop_types_default()).object,
  /**
   * The string to use as a query parameter when searching row items.
   */
  searchBy: (prop_types_default()).string,
  /**
   * List of fields used for summary numbers. (Reduces queries)
   */
  summaryFields: prop_types_default().arrayOf((prop_types_default()).string),
  /**
   * Table data of that report. If it's not provided, it will be automatically
   * loaded via the provided `endpoint`.
   */
  tableData: (prop_types_default()).object.isRequired,
  /**
   * Properties to be added to the query sent to the report table endpoint.
   */
  tableQuery: (prop_types_default()).object,
  /**
   * String to display as the title of the table.
   */
  title: (prop_types_default()).string.isRequired
};
ReportTable.defaultProps = {
  primaryData: {},
  tableData: {
    items: {
      data: [],
      totalResults: 0
    },
    query: {}
  },
  tableQuery: {},
  compareParam: 'filter',
  downloadable: false,
  onSearch: external_lodash_namespaceObject.noop,
  baseSearchQuery: {}
};
const EMPTY_ARRAY = (/* unused pure expression or super */ null && ([]));
const EMPTY_OBJECT = {};
/* harmony default export */ var report_table = ((0,external_wp_compose_namespaceObject.compose)((0,external_wp_data_namespaceObject.withSelect)((select, props) => {
  const {
    endpoint,
    getSummary,
    isRequesting,
    itemIdField,
    query,
    tableData,
    tableQuery,
    filters,
    advancedFilters,
    summaryFields,
    extendedItemsStoreName
  } = props;
  const {
    woocommerce_default_date_range: defaultDateRange
  } = select(external_wc_data_namespaceObject.SETTINGS_STORE_NAME).getSetting('wc_admin', 'wcAdminSettings');
  if (isRequesting) {
    return EMPTY_OBJECT;
  }

  /* eslint @wordpress/no-unused-vars-before-return: "off" */
  const reportStoreSelector = select(external_wc_data_namespaceObject.REPORTS_STORE_NAME);
  const extendedStoreSelector = extendedItemsStoreName ? select(extendedItemsStoreName) : null;
  const primaryData = getSummary ? (0,external_wc_data_namespaceObject.getReportChartData)({
    endpoint: endpoint,
    dataType: 'primary',
    query,
    // Hint: Leave this param for backwards compatibility WC-Admin lt 2.6.
    select,
    selector: reportStoreSelector,
    filters,
    advancedFilters,
    defaultDateRange,
    fields: summaryFields
  }) : EMPTY_OBJECT;
  const queriedTableData = tableData || (0,external_wc_data_namespaceObject.getReportTableData)({
    endpoint,
    query,
    // Hint: Leave this param for backwards compatibility WC-Admin lt 2.6.
    select,
    selector: reportStoreSelector,
    tableQuery,
    filters,
    advancedFilters,
    defaultDateRange
  });
  return {
    primaryData,
    tableData: queriedTableData,
    query
  };
}), (0,external_wp_data_namespaceObject.withDispatch)(dispatch => {
  const {
    startExport
  } = dispatch(external_wc_data_namespaceObject.EXPORT_STORE_NAME);
  const {
    createNotice
  } = dispatch('core/notices');
  return {
    createNotice,
    startExport
  };
}))(ReportTable));
;// CONCATENATED MODULE: external ["wc","currency"]
var external_wc_currency_namespaceObject = window["wc"]["currency"];
var external_wc_currency_default = /*#__PURE__*/__webpack_require__.n(external_wc_currency_namespaceObject);
;// CONCATENATED MODULE: ./node_modules/@somewherewarm/woocommerce/packages/lib/currency-context.js
/**
 * External dependencies
 */




/**
 * Internal dependencies
 */

const appCurrency = external_wc_currency_default()(external_wc_wcSettings_namespaceObject.CURRENCY);
const getFilteredCurrencyInstance = query => {
  const config = appCurrency.getCurrencyConfig();
  const filteredConfig = applyFilters('woocommerce_admin_report_currency', config, query);
  return CurrencyFactory(filteredConfig);
};
const CurrencyContext = (0,external_wp_element_namespaceObject.createContext)(appCurrency // default value
);
;// CONCATENATED MODULE: ./resources/js/admin/analytics/report/issued/table.js

/**
 * External dependencies
 */













/**
 * SomewhereWarm dependencies
 */


class IssuedBalanceReportTable extends external_wp_element_namespaceObject.Component {
  constructor() {
    super();
    this.getHeadersContent = this.getHeadersContent.bind(this);
    this.getRowsContent = this.getRowsContent.bind(this);
    this.getSummary = this.getSummary.bind(this);
  }
  getHeadersContent() {
    return [{
      label: (0,external_wp_i18n_namespaceObject.__)('Code', 'woocommerce-gift-cards'),
      key: 'giftcard_code',
      required: true,
      isLeftAligned: true,
      isSortable: false
    }, {
      label: (0,external_wp_i18n_namespaceObject.__)('Issued Amount', 'woocommerce-gift-cards'),
      key: 'issued_balance',
      required: true,
      isSortable: true,
      isNumeric: true
    }, {
      label: (0,external_wp_i18n_namespaceObject.__)('Issued Date', 'woocommerce-gift-cards'),
      key: 'date',
      defaultSort: true,
      isSortable: true,
      isNumeric: true
    }, {
      label: (0,external_wp_i18n_namespaceObject.__)('Expiration Date', 'woocommerce-gift-cards'),
      key: 'expire_date',
      isSortable: true,
      isNumeric: true
    }].filter(Boolean);
  }
  getRowsContent(data = []) {
    const {
      query
    } = this.props;
    const persistedQuery = (0,external_wc_navigation_namespaceObject.getPersistedQuery)(query);
    const dateFormat = (0,external_wc_wcSettings_namespaceObject.getSetting)('dateFormat', external_wc_date_namespaceObject.defaultTableDateFormat);
    const {
      render: renderCurrency,
      formatDecimal: getCurrencyFormatDecimal,
      getCurrencyConfig
    } = this.context;
    const currency = getCurrencyConfig();
    return (0,external_lodash_namespaceObject.map)(data, row => {
      const {
        create_date: createDate,
        giftcard_id: giftcardId,
        giftcard_code: giftcardCode,
        issued_balance: issuedBalance,
        expire_date: expireDate
      } = row;
      const giftcardDetailLink = (0,external_wc_wcSettings_namespaceObject.getAdminLink)('admin.php?page=gc_giftcards&section=edit&giftcard=' + giftcardId);
      return [{
        display: (0,external_React_namespaceObject.createElement)(external_wc_components_namespaceObject.Link, {
          href: giftcardDetailLink,
          type: "wp-admin"
        }, giftcardCode),
        value: giftcardCode
      }, {
        display: renderCurrency(issuedBalance),
        value: getCurrencyFormatDecimal(issuedBalance)
      }, {
        display: createDate ? (0,external_React_namespaceObject.createElement)(external_wc_components_namespaceObject.Date, {
          date: createDate,
          visibleFormat: dateFormat
        }) : (0,external_wp_i18n_namespaceObject.__)('N/A', 'woocommerce-gift-cards'),
        value: createDate
      }, {
        display: expireDate ? (0,external_React_namespaceObject.createElement)(external_wc_components_namespaceObject.Date, {
          date: expireDate,
          visibleFormat: dateFormat
        }) : (0,external_wp_i18n_namespaceObject.__)('N/A', 'woocommerce-gift-cards'),
        value: expireDate
      }].filter(Boolean);
    });
  }
  getSummary(totals) {
    const {
      giftcards_count: giftcardsCount = 0,
      issued_balance: issuedBalance = 0
    } = totals;
    const {
      formatAmount,
      getCurrencyConfig
    } = this.context;
    const currency = getCurrencyConfig();
    return [{
      label: (0,external_wp_i18n_namespaceObject._n)('gift card issued', 'gift cards issued', giftcardsCount, 'woocommerce-gift-cards'),
      value: (0,external_wc_number_namespaceObject.formatValue)(currency, 'number', giftcardsCount)
    }, {
      label: (0,external_wp_i18n_namespaceObject.__)('issued', 'woocommerce-gift-cards'),
      value: formatAmount(issuedBalance)
    }];
  }
  render() {
    const {
      filters,
      isRequesting,
      hideCompare,
      query,
      endpoint
    } = this.props;
    return (0,external_React_namespaceObject.createElement)(report_table, {
      endpoint: endpoint,
      getHeadersContent: this.getHeadersContent,
      getRowsContent: this.getRowsContent,
      getSummary: this.getSummary,
      summaryFields: ['giftcards_count', 'issued_balance'],
      itemIdField: "giftcard_id",
      isRequesting: isRequesting,
      query: query,
      compareBy: hideCompare ? undefined : 'giftcards',
      tableQuery: {
        orderby: query.orderby || 'issued_balance',
        order: query.order || 'desc',
        segmentby: query.segmentby
      },
      title: (0,external_wp_i18n_namespaceObject.__)('Gift Cards', 'woocommerce-gift-cards'),
      columnPrefsKey: "giftcards_issued_report_columns",
      filters: filters
    });
  }
}
IssuedBalanceReportTable.contextType = CurrencyContext;
/* harmony default export */ var table = (IssuedBalanceReportTable);
;// CONCATENATED MODULE: ./node_modules/@somewherewarm/woocommerce/packages/components/report-summary/index.js

/**
 * External dependencies
 */






/**
 * WooCommerce dependencies
 */






/**
 * Internal dependencies
 */
// import ReportError from '../report-error';


/**
 * Component to render summary numbers in reports.
 */
class ReportSummary extends external_wp_element_namespaceObject.Component {
  formatVal(val, type) {
    const {
      formatAmount,
      getCurrencyConfig
    } = this.context;
    return type === 'currency' ? formatAmount(val) : (0,external_wc_number_namespaceObject.formatValue)(getCurrencyConfig(), type, val);
  }
  getValues(key, type) {
    const {
      emptySearchResults,
      summaryData
    } = this.props;
    const {
      totals
    } = summaryData;
    const primaryTotal = totals.primary ? totals.primary[key] : 0;
    const secondaryTotal = totals.secondary ? totals.secondary[key] : 0;
    const primaryValue = emptySearchResults ? 0 : primaryTotal;
    const secondaryValue = emptySearchResults ? 0 : secondaryTotal;
    return {
      delta: (0,external_wc_number_namespaceObject.calculateDelta)(primaryValue, secondaryValue),
      prevValue: this.formatVal(secondaryValue, type),
      value: this.formatVal(primaryValue, type)
    };
  }
  render() {
    const {
      charts,
      query,
      selectedChart,
      summaryData,
      endpoint,
      report,
      defaultDateRange
    } = this.props;
    const {
      isError,
      isRequesting
    } = summaryData;
    if (isError) {
      // return <ReportError isError />;
      return;
    }
    if (isRequesting) {
      return (0,external_React_namespaceObject.createElement)(external_wc_components_namespaceObject.SummaryListPlaceholder, {
        numberOfItems: charts.length
      });
    }
    const {
      compare
    } = (0,external_wc_date_namespaceObject.getDateParamsFromQuery)(query, defaultDateRange);
    const renderSummaryNumbers = ({
      onToggle
    }) => charts.map(chart => {
      const {
        key,
        order,
        orderby,
        label,
        type
      } = chart;
      const newPath = {
        chart: key
      };
      if (orderby) {
        newPath.orderby = orderby;
      }
      if (order) {
        newPath.order = order;
      }
      const href = (0,external_wc_navigation_namespaceObject.getNewPath)(newPath);
      const isSelected = selectedChart.key === key;
      const {
        delta,
        prevValue,
        value
      } = this.getValues(key, type);
      return (0,external_React_namespaceObject.createElement)(external_wc_components_namespaceObject.SummaryNumber, {
        key: key,
        delta: delta,
        href: href,
        label: label,
        prevLabel: compare === 'previous_period' ? (0,external_wp_i18n_namespaceObject.__)('Previous Period:', 'woocommerce-product-bundles') : (0,external_wp_i18n_namespaceObject.__)('Previous Year:', 'woocommerce-product-bundles'),
        prevValue: prevValue,
        selected: isSelected,
        value: value,
        onLinkClickCallback: () => {
          // Wider than a certain breakpoint, there is no dropdown so avoid calling onToggle.
          if (onToggle) {
            onToggle();
          }
        }
      });
    });
    return (0,external_React_namespaceObject.createElement)(external_wc_components_namespaceObject.SummaryList, null, renderSummaryNumbers);
  }
}
ReportSummary.propTypes = {
  /**
   * Properties of all the charts available for that report.
   */
  charts: (prop_types_default()).array.isRequired,
  /**
   * The endpoint to use in API calls to populate the Summary Numbers.
   * For example, if `taxes` is provided, data will be fetched from the report
   * `taxes` endpoint (ie: `/wc-analytics/reports/taxes/stats`). If the provided endpoint
   * doesn't exist, an error will be shown to the user with `ReportError`.
   */
  endpoint: (prop_types_default()).string.isRequired,
  /**
   * The query string represented in object form.
   */
  query: (prop_types_default()).object.isRequired,
  /**
   * Properties of the selected chart.
   */
  selectedChart: prop_types_default().shape({
    /**
     * Key of the selected chart.
     */
    key: (prop_types_default()).string.isRequired,
    /**
     * Chart label.
     */
    label: (prop_types_default()).string.isRequired,
    /**
     * Order query argument.
     */
    order: prop_types_default().oneOf(['asc', 'desc']),
    /**
     * Order by query argument.
     */
    orderby: (prop_types_default()).string,
    /**
     * Number type for formatting.
     */
    type: prop_types_default().oneOf(['average', 'number', 'currency']).isRequired
  }).isRequired,
  /**
   * Data to display in the SummaryNumbers.
   */
  summaryData: (prop_types_default()).object,
  /**
   * Report name, if different than the endpoint.
   */
  report: (prop_types_default()).string
};
ReportSummary.defaultProps = {
  summaryData: {
    totals: {
      primary: {},
      secondary: {}
    },
    isError: false
  }
};
ReportSummary.contextType = CurrencyContext;
/* harmony default export */ var report_summary = ((0,external_wp_compose_namespaceObject.compose)((0,external_wp_data_namespaceObject.withSelect)((select, props) => {
  const {
    charts,
    endpoint,
    limitProperties,
    query,
    filters,
    advancedFilters
  } = props;
  const limitBy = limitProperties || [endpoint];
  const hasLimitByParam = limitBy.some(item => query[item] && query[item].length);
  if (query.search && !hasLimitByParam) {
    return {
      emptySearchResults: true
    };
  }
  const fields = charts && charts.map(chart => chart.key);
  const {
    woocommerce_default_date_range: defaultDateRange
  } = select(external_wc_data_namespaceObject.SETTINGS_STORE_NAME).getSetting('wc_admin', 'wcAdminSettings');
  const summaryData = (0,external_wc_data_namespaceObject.getSummaryNumbers)({
    endpoint,
    query,
    select,
    limitBy,
    filters,
    advancedFilters,
    defaultDateRange,
    fields
  });
  return {
    summaryData,
    defaultDateRange
  };
}))(ReportSummary));
;// CONCATENATED MODULE: external ["wp","date"]
var external_wp_date_namespaceObject = window["wp"]["date"];
;// CONCATENATED MODULE: ./node_modules/@somewherewarm/woocommerce/packages/components/report-chart/utils.js
/**
 * External dependencies
 */


const DEFAULT_FILTER = 'all';
function getSelectedFilter(filters, query, selectedFilterArgs = {}) {
  if (!filters || filters.length === 0) {
    return null;
  }
  const clonedFilters = filters.slice(0);
  const filterConfig = clonedFilters.pop();
  if (filterConfig.showFilters(query, selectedFilterArgs)) {
    const allFilters = (0,external_wc_navigation_namespaceObject.flattenFilters)(filterConfig.filters);
    const value = query[filterConfig.param] || filterConfig.defaultValue || DEFAULT_FILTER;
    return (0,external_lodash_namespaceObject.find)(allFilters, {
      value
    });
  }
  return getSelectedFilter(clonedFilters, query, selectedFilterArgs);
}
function getChartMode(selectedFilter, query) {
  if (selectedFilter && query) {
    const selectedFilterParam = (0,external_lodash_namespaceObject.get)(selectedFilter, ['settings', 'param']);
    if (!selectedFilterParam || Object.keys(query).includes(selectedFilterParam)) {
      return (0,external_lodash_namespaceObject.get)(selectedFilter, ['chartMode']);
    }
  }
  return null;
}
;// CONCATENATED MODULE: ./node_modules/@somewherewarm/woocommerce/packages/components/report-chart/index.js

/**
 * External dependencies
 */








/**
 * WooCommerce dependencies
 */




/**
 * Internal dependencies
 */




/**
 * Component that renders the chart in reports.
 */
class ReportChart extends external_wp_element_namespaceObject.Component {
  shouldComponentUpdate(nextProps) {
    if (nextProps.isRequesting !== this.props.isRequesting || nextProps.primaryData.isRequesting !== this.props.primaryData.isRequesting || nextProps.secondaryData.isRequesting !== this.props.secondaryData.isRequesting || !(0,external_lodash_namespaceObject.isEqual)(nextProps.query, this.props.query)) {
      return true;
    }
    return false;
  }
  getItemChartData() {
    const {
      primaryData,
      selectedChart
    } = this.props;
    const chartData = primaryData.data.intervals.map(function (interval) {
      const intervalData = {};
      interval.subtotals.segments.forEach(function (segment) {
        if (segment.segment_label) {
          const label = intervalData[segment.segment_label] ? segment.segment_label + ' (#' + segment.segment_id + ')' : segment.segment_label;
          intervalData[segment.segment_id] = {
            label,
            value: segment.subtotals[selectedChart.key] || 0
          };
        }
      });
      return {
        date: (0,external_wp_date_namespaceObject.format)('Y-m-d\\TH:i:s', interval.date_start),
        ...intervalData
      };
    });
    return chartData;
  }
  getTimeChartData() {
    const {
      query,
      primaryData,
      secondaryData,
      selectedChart,
      defaultDateRange
    } = this.props;
    const currentInterval = (0,external_wc_date_namespaceObject.getIntervalForQuery)(query);
    const {
      primary,
      secondary
    } = (0,external_wc_date_namespaceObject.getCurrentDates)(query, defaultDateRange);
    const chartData = primaryData.data.intervals.map(function (interval, index) {
      const secondaryDate = (0,external_wc_date_namespaceObject.getPreviousDate)(interval.date_start, primary.after, secondary.after, query.compare, currentInterval);
      const secondaryInterval = secondaryData.data.intervals[index];
      return {
        date: (0,external_wp_date_namespaceObject.format)('Y-m-d\\TH:i:s', interval.date_start),
        primary: {
          label: `${primary.label} (${primary.range})`,
          labelDate: interval.date_start,
          value: interval.subtotals[selectedChart.key] || 0
        },
        secondary: {
          label: `${secondary.label} (${secondary.range})`,
          labelDate: secondaryDate.format('YYYY-MM-DD HH:mm:ss'),
          value: secondaryInterval && secondaryInterval.subtotals[selectedChart.key] || 0
        }
      };
    });
    return chartData;
  }
  getTimeChartTotals() {
    const {
      primaryData,
      secondaryData,
      selectedChart
    } = this.props;
    return {
      primary: (0,external_lodash_namespaceObject.get)(primaryData, ['data', 'totals', selectedChart.key], null),
      secondary: (0,external_lodash_namespaceObject.get)(secondaryData, ['data', 'totals', selectedChart.key], null)
    };
  }
  renderChart(mode, isRequesting, chartData, legendTotals) {
    const {
      emptySearchResults,
      filterParam,
      interactiveLegend,
      itemsLabel,
      legendPosition,
      path,
      query,
      selectedChart,
      showHeaderControls,
      primaryData
    } = this.props;
    const currentInterval = (0,external_wc_date_namespaceObject.getIntervalForQuery)(query);
    const allowedIntervals = (0,external_wc_date_namespaceObject.getAllowedIntervalsForQuery)(query);
    const formats = (0,external_wc_date_namespaceObject.getDateFormatsForInterval)(currentInterval, primaryData.data.intervals.length);
    const emptyMessage = emptySearchResults ? (0,external_wp_i18n_namespaceObject.__)('No data for the current search', 'woocommerce-admin') : (0,external_wp_i18n_namespaceObject.__)('No data for the selected date range', 'woocommerce-admin');
    const {
      formatAmount,
      getCurrencyConfig
    } = this.context;
    return (0,external_React_namespaceObject.createElement)(external_wc_components_namespaceObject.Chart, {
      allowedIntervals: allowedIntervals,
      data: chartData,
      dateParser: '%Y-%m-%dT%H:%M:%S',
      emptyMessage: emptyMessage,
      filterParam: filterParam,
      interactiveLegend: interactiveLegend,
      interval: currentInterval,
      isRequesting: isRequesting,
      itemsLabel: itemsLabel,
      legendPosition: legendPosition,
      legendTotals: legendTotals,
      mode: mode,
      path: path,
      query: query,
      screenReaderFormat: formats.screenReaderFormat,
      showHeaderControls: showHeaderControls,
      title: selectedChart.label,
      tooltipLabelFormat: formats.tooltipLabelFormat,
      tooltipTitle: mode === 'time-comparison' && selectedChart.label || null,
      tooltipValueFormat: (0,external_wc_data_namespaceObject.getTooltipValueFormat)(selectedChart.type, formatAmount),
      chartType: (0,external_wc_date_namespaceObject.getChartTypeForQuery)(query),
      valueType: selectedChart.type,
      xFormat: formats.xFormat,
      x2Format: formats.x2Format,
      currency: getCurrencyConfig()
    });
  }
  renderItemComparison() {
    const {
      isRequesting,
      primaryData
    } = this.props;
    if (primaryData.isError) {
      return (0,external_React_namespaceObject.createElement)(report_error, {
        isError: true
      });
    }
    const isChartRequesting = isRequesting || primaryData.isRequesting;
    const chartData = this.getItemChartData();
    return this.renderChart('item-comparison', isChartRequesting, chartData);
  }
  renderTimeComparison() {
    const {
      isRequesting,
      primaryData,
      secondaryData
    } = this.props;
    if (!primaryData || primaryData.isError || secondaryData.isError) {
      return (0,external_React_namespaceObject.createElement)(report_error, {
        isError: true
      });
    }
    const isChartRequesting = isRequesting || primaryData.isRequesting || secondaryData.isRequesting;
    const chartData = this.getTimeChartData();
    const legendTotals = this.getTimeChartTotals();
    return this.renderChart('time-comparison', isChartRequesting, chartData, legendTotals);
  }
  render() {
    const {
      mode
    } = this.props;
    if (mode === 'item-comparison') {
      return this.renderItemComparison();
    }
    return this.renderTimeComparison();
  }
}
ReportChart.contextType = CurrencyContext;
ReportChart.propTypes = {
  /**
   * Filters available for that report.
   */
  filters: (prop_types_default()).array,
  /**
   * Whether there is an API call running.
   */
  isRequesting: (prop_types_default()).bool,
  /**
   * Label describing the legend items.
   */
  itemsLabel: (prop_types_default()).string,
  /**
   * Allows specifying properties different from the `endpoint` that will be used
   * to limit the items when there is an active search.
   */
  limitProperties: (prop_types_default()).array,
  /**
   * `items-comparison` (default) or `time-comparison`, this is used to generate correct
   * ARIA properties.
   */
  mode: (prop_types_default()).string,
  /**
   * Current path
   */
  path: (prop_types_default()).string.isRequired,
  /**
   * Primary data to display in the chart.
   */
  primaryData: (prop_types_default()).object,
  /**
   * The query string represented in object form.
   */
  query: (prop_types_default()).object.isRequired,
  /**
   * Secondary data to display in the chart.
   */
  secondaryData: (prop_types_default()).object,
  /**
   * Properties of the selected chart.
   */
  selectedChart: prop_types_default().shape({
    /**
     * Key of the selected chart.
     */
    key: (prop_types_default()).string.isRequired,
    /**
     * Chart label.
     */
    label: (prop_types_default()).string.isRequired,
    /**
     * Order query argument.
     */
    order: prop_types_default().oneOf(['asc', 'desc']),
    /**
     * Order by query argument.
     */
    orderby: (prop_types_default()).string,
    /**
     * Number type for formatting.
     */
    type: prop_types_default().oneOf(['average', 'number', 'currency']).isRequired
  }).isRequired
};
ReportChart.defaultProps = {
  isRequesting: false,
  primaryData: {
    data: {
      intervals: []
    },
    isError: false,
    isRequesting: false
  },
  secondaryData: {
    data: {
      intervals: []
    },
    isError: false,
    isRequesting: false
  }
};
/* harmony default export */ var report_chart = ((0,external_wp_compose_namespaceObject.compose)((0,external_wp_data_namespaceObject.withSelect)((select, props) => {
  const {
    charts,
    endpoint,
    filters,
    isRequesting,
    limitProperties,
    query,
    advancedFilters
  } = props;
  const limitBy = limitProperties || [endpoint];
  const selectedFilter = getSelectedFilter(filters, query);
  const filterParam = (0,external_lodash_namespaceObject.get)(selectedFilter, ['settings', 'param']);
  const chartMode = props.mode || getChartMode(selectedFilter, query) || 'time-comparison';
  const {
    woocommerce_default_date_range: defaultDateRange
  } = select(external_wc_data_namespaceObject.SETTINGS_STORE_NAME).getSetting('wc_admin', 'wcAdminSettings');

  /* eslint @wordpress/no-unused-vars-before-return: "off" */
  const reportStoreSelector = select(external_wc_data_namespaceObject.REPORTS_STORE_NAME);
  const newProps = {
    mode: chartMode,
    filterParam,
    defaultDateRange
  };
  if (isRequesting) {
    return newProps;
  }
  const hasLimitByParam = limitBy.some(item => query[item] && query[item].length);
  if (query.search && !hasLimitByParam) {
    return {
      ...newProps,
      emptySearchResults: true
    };
  }
  const fields = charts && charts.map(chart => chart.key);
  const primaryData = (0,external_wc_data_namespaceObject.getReportChartData)({
    endpoint,
    dataType: 'primary',
    query,
    // Hint: Leave this param for backwards compatibility WC-Admin lt 2.6.
    select,
    selector: reportStoreSelector,
    limitBy,
    filters,
    advancedFilters,
    defaultDateRange,
    fields
  });
  if (chartMode === 'item-comparison') {
    return {
      ...newProps,
      primaryData
    };
  }
  const secondaryData = (0,external_wc_data_namespaceObject.getReportChartData)({
    endpoint,
    dataType: 'secondary',
    query,
    // Hint: Leave this param for backwards compatibility WC-Admin lt 2.6.
    select,
    selector: reportStoreSelector,
    limitBy,
    filters,
    advancedFilters,
    defaultDateRange,
    fields
  });
  return {
    ...newProps,
    primaryData,
    secondaryData
  };
}))(ReportChart));
;// CONCATENATED MODULE: external ["wp","apiFetch"]
var external_wp_apiFetch_namespaceObject = window["wp"]["apiFetch"];
;// CONCATENATED MODULE: external ["wp","url"]
var external_wp_url_namespaceObject = window["wp"]["url"];
;// CONCATENATED MODULE: ./node_modules/@somewherewarm/woocommerce/packages/lib/index.js
/**
 * External dependencies.
 */





/**
 * Exports.
 */
function getRequestByIdString(path, handleData = identity) {
  return function (queryString = '') {
    const pathString = path;
    const idList = getIdsFromQuery(queryString);
    if (idList.length < 1) {
      return Promise.resolve([]);
    }
    const payload = {
      include: idList.join(','),
      per_page: idList.length
    };
    return apiFetch({
      path: addQueryArgs(pathString, payload)
    }).then(data => data.map(handleData));
  };
}

/**
 * Takes a chart name returns the configuration for that chart from and array
 * of charts. If the chart is not found it will return the first chart.
 *
 * @param {string} chartName - the name of the chart to get configuration for
 * @param {Array} charts - list of charts for a particular report
 * @return {Object} - chart configuration object
 */
function getSelectedChart(chartName, charts = []) {
  const chart = (0,external_lodash_namespaceObject.find)(charts, {
    key: chartName
  });
  if (chart) {
    return chart;
  }
  return charts[0];
}
;// CONCATENATED MODULE: ./resources/js/admin/analytics/report/issued/index.js

/**
 * External dependencies
 */




/**
 * WooCommerce dependencies
 */


/**
 * Internal dependencies.
 */





/**
 * SomewhereWarm dependencies.
 */



class IssuedReport extends external_wp_element_namespaceObject.Component {
  getChartMeta() {
    const {
      query,
      isSingleProductView
    } = this.props;
    const isCompareView = false;
    const mode = 'time-comparison';
    const compareObject = 'giftcards';
    /* translators: Number of Bundles */
    const label = (0,external_wp_i18n_namespaceObject.__)('%d gift cards', 'woocommerce-gift-cards');
    return {
      itemsLabel: label,
      mode
    };
  }
  render() {
    const {
      itemsLabel,
      mode
    } = this.getChartMeta();
    const {
      path,
      query,
      isError,
      isRequesting
    } = this.props;
    if (isError) {
      return (0,external_React_namespaceObject.createElement)(ReportError, {
        isError: true
      });
    }
    const chartQuery = {
      ...query
    };
    return (0,external_React_namespaceObject.createElement)(external_wp_element_namespaceObject.Fragment, null, (0,external_React_namespaceObject.createElement)(external_wc_components_namespaceObject.ReportFilters, {
      query: query,
      path: path,
      showDatePicker: true,
      filters: filters
    }), (0,external_React_namespaceObject.createElement)(report_summary, {
      mode: mode,
      charts: charts,
      endpoint: ENDPOINT,
      isRequesting: isRequesting,
      query: chartQuery,
      selectedChart: getSelectedChart(query.chart, charts),
      filters: filters
    }), (0,external_React_namespaceObject.createElement)(report_chart, {
      charts: charts,
      mode: mode,
      endpoint: ENDPOINT,
      isRequesting: isRequesting,
      itemsLabel: itemsLabel,
      path: path,
      query: chartQuery,
      selectedChart: getSelectedChart(chartQuery.chart, charts),
      filters: filters
    }), (0,external_React_namespaceObject.createElement)(table, {
      isRequesting: isRequesting,
      endpoint: ENDPOINT,
      hideCompare: true,
      query: query,
      filters: filters
    }));
  }
}
IssuedReport.propTypes = {
  path: (prop_types_default()).string.isRequired,
  query: (prop_types_default()).object.isRequired
};
/* harmony default export */ var issued = (IssuedReport);
;// CONCATENATED MODULE: ./resources/js/admin/analytics/report/used/config.js
/**
 * External dependencies.
 */


/**
 * Exports.
 */
const config_charts = [{
  key: 'giftcards_count',
  label: (0,external_wp_i18n_namespaceObject.__)('Used Gift Cards', 'woocommerce-gift-cards'),
  order: 'desc',
  orderby: 'net_amount',
  type: 'number'
}, {
  key: 'used_amount',
  label: (0,external_wp_i18n_namespaceObject.__)('Debited Amount', 'woocommerce-gift-cards'),
  order: 'desc',
  orderby: 'used_amount',
  type: 'currency'
}, {
  key: 'refunded_amount',
  label: (0,external_wp_i18n_namespaceObject.__)('Credited Amount', 'woocommerce-gift-cards'),
  order: 'desc',
  orderby: 'refunded_amount',
  type: 'currency'
}, {
  key: 'net_amount',
  label: (0,external_wp_i18n_namespaceObject.__)('Net Amount', 'woocommerce-gift-cards'),
  order: 'desc',
  orderby: 'net_amount',
  type: 'currency'
}];
;// CONCATENATED MODULE: ./resources/js/admin/analytics/report/used/table.js

/**
 * External dependencies
 */












/**
 * SomewhereWarm dependencies
 */


class UsedBalanceReportTable extends external_wp_element_namespaceObject.Component {
  constructor() {
    super();
    this.getHeadersContent = this.getHeadersContent.bind(this);
    this.getRowsContent = this.getRowsContent.bind(this);
    this.getSummary = this.getSummary.bind(this);
  }
  getHeadersContent() {
    return [{
      label: (0,external_wp_i18n_namespaceObject.__)('Code', 'woocommerce-gift-cards'),
      key: 'giftcard_code',
      required: true,
      isLeftAligned: true,
      isSortable: false
    }, {
      label: (0,external_wp_i18n_namespaceObject.__)('Debited Amount', 'woocommerce-gift-cards'),
      key: 'used_amount',
      required: true,
      isSortable: true,
      isNumeric: true
    }, {
      label: (0,external_wp_i18n_namespaceObject.__)('Credited Amount', 'woocommerce-gift-cards'),
      key: 'refunded_amount',
      isSortable: true,
      isNumeric: true
    }, {
      label: (0,external_wp_i18n_namespaceObject.__)('Net Amount', 'woocommerce-gift-cards'),
      key: 'net_amount',
      defaultSort: true,
      isSortable: true,
      isNumeric: true
    }].filter(Boolean);
  }
  getRowsContent(data = []) {
    const {
      query
    } = this.props;
    const {
      render: renderCurrency,
      formatDecimal: getCurrencyFormatDecimal,
      getCurrencyConfig
    } = this.context;
    return (0,external_lodash_namespaceObject.map)(data, row => {
      const {
        giftcard_id: giftcardId,
        giftcard_code: giftcardCode,
        net_amount: netAmount,
        refunded_amount: refundedAmount,
        used_amount: usedAmount
      } = row;
      const giftcardDetailLink = (0,external_wc_wcSettings_namespaceObject.getAdminLink)('admin.php?page=gc_giftcards&section=edit&giftcard=' + giftcardId);
      return [{
        display: (0,external_React_namespaceObject.createElement)(external_wc_components_namespaceObject.Link, {
          href: giftcardDetailLink,
          type: "wp-admin"
        }, giftcardCode),
        value: giftcardCode
      }, {
        display: renderCurrency(usedAmount),
        value: getCurrencyFormatDecimal(usedAmount)
      }, {
        display: renderCurrency(refundedAmount),
        value: getCurrencyFormatDecimal(refundedAmount)
      }, {
        display: renderCurrency(netAmount),
        value: getCurrencyFormatDecimal(netAmount)
      }].filter(Boolean);
    });
  }
  getSummary(totals) {
    const {
      giftcards_count: giftcardsCount = 0,
      net_amount: netAmount = 0,
      refunded_amount: refundedAmount = 0,
      used_amount: usedAmount = 0
    } = totals;
    const {
      formatAmount,
      getCurrencyConfig
    } = this.context;
    const currency = getCurrencyConfig();
    return [{
      label: (0,external_wp_i18n_namespaceObject._n)('gift card used', 'gift cards used', giftcardsCount, 'woocommerce-gift-cards'),
      value: (0,external_wc_number_namespaceObject.formatValue)(currency, 'number', giftcardsCount)
    }, {
      label: (0,external_wp_i18n_namespaceObject.__)('debited', 'woocommerce-gift-cards'),
      value: formatAmount(usedAmount)
    }, {
      label: (0,external_wp_i18n_namespaceObject.__)('credited', 'woocommerce-gift-cards'),
      value: formatAmount(refundedAmount)
    }, {
      label: (0,external_wp_i18n_namespaceObject.__)('net', 'woocommerce-gift-cards'),
      value: formatAmount(netAmount)
    }];
  }
  render() {
    const {
      filters,
      isRequesting,
      hideCompare,
      query
    } = this.props;
    return (0,external_React_namespaceObject.createElement)(report_table, {
      endpoint: "giftcards/used",
      getHeadersContent: this.getHeadersContent,
      getRowsContent: this.getRowsContent,
      getSummary: this.getSummary,
      summaryFields: ['giftcards_count', 'used_amount', 'refunded_amount', 'net_amount'],
      itemIdField: "giftcard_id",
      isRequesting: isRequesting,
      query: query,
      compareBy: hideCompare ? undefined : 'giftcards',
      tableQuery: {
        orderby: query.orderby || 'used_amount',
        order: query.order || 'desc',
        segmentby: query.segmentby
      },
      title: (0,external_wp_i18n_namespaceObject.__)('Gift Cards', 'woocommerce-gift-cards'),
      columnPrefsKey: "giftcards_used_report_columns",
      filters: filters
    });
  }
}
UsedBalanceReportTable.contextType = CurrencyContext;
/* harmony default export */ var used_table = (UsedBalanceReportTable);
;// CONCATENATED MODULE: ./resources/js/admin/analytics/report/used/index.js

/**
 * External dependencies
 */




/**
 * WooCommerce dependencies
 */



/**
 * Internal dependencies.
 */




/**
 * SomewhereWarm dependencies.
 */



class UsedReport extends external_wp_element_namespaceObject.Component {
  getChartMeta() {
    const {
      query,
      isSingleProductView
    } = this.props;
    const isCompareView = false;
    const mode = 'time-comparison';
    const compareObject = 'giftcards';
    /* translators: Number of Bundles */
    const label = (0,external_wp_i18n_namespaceObject.__)('%d gift cards', 'woocommerce-gift-cards');
    return {
      itemsLabel: label,
      mode
    };
  }
  render() {
    const {
      itemsLabel,
      mode
    } = this.getChartMeta();
    const {
      path,
      query,
      isError,
      isRequesting
    } = this.props;
    if (isError) {
      return (0,external_React_namespaceObject.createElement)(ReportError, {
        isError: true
      });
    }
    const chartQuery = {
      ...query
    };
    return (0,external_React_namespaceObject.createElement)(external_wp_element_namespaceObject.Fragment, null, (0,external_React_namespaceObject.createElement)(external_wc_components_namespaceObject.ReportFilters, {
      query: query,
      path: path,
      showDatePicker: true,
      filters: filters
    }), (0,external_React_namespaceObject.createElement)(report_summary, {
      mode: mode,
      charts: config_charts,
      endpoint: "giftcards/used",
      isRequesting: isRequesting,
      query: chartQuery,
      selectedChart: getSelectedChart(query.chart, config_charts),
      filters: filters
    }), (0,external_React_namespaceObject.createElement)(report_chart, {
      charts: config_charts,
      mode: mode,
      endpoint: "giftcards/used",
      isRequesting: isRequesting,
      itemsLabel: itemsLabel,
      path: path,
      query: chartQuery,
      selectedChart: getSelectedChart(chartQuery.chart, config_charts),
      filters: filters
    }), (0,external_React_namespaceObject.createElement)(used_table, {
      isRequesting: isRequesting,
      hideCompare: true,
      query: query,
      filters: filters
    }));
  }
}
UsedReport.propTypes = {
  path: (prop_types_default()).string.isRequired,
  query: (prop_types_default()).object.isRequired
};
/* harmony default export */ var used = (UsedReport);
;// CONCATENATED MODULE: ./resources/js/admin/analytics/report/expired/config.js
/**
 * External dependencies.
 */


/**
 * Exports.
 */
const expired_config_charts = [{
  key: 'giftcards_count',
  label: (0,external_wp_i18n_namespaceObject.__)('Expired Gift Cards', 'woocommerce-gift-cards'),
  order: 'desc',
  orderby: 'expire_date',
  type: 'number'
}, {
  key: 'expired_balance',
  label: (0,external_wp_i18n_namespaceObject.__)('Expired Amount', 'woocommerce-gift-cards'),
  order: 'desc',
  orderby: 'expired_balance',
  type: 'currency'
}];
;// CONCATENATED MODULE: ./resources/js/admin/analytics/report/expired/table.js

/**
 * External dependencies
 */













/**
 * SomewhereWarm dependencies.
 */


class ExpiredBalanceReportTable extends external_wp_element_namespaceObject.Component {
  constructor() {
    super();
    this.getHeadersContent = this.getHeadersContent.bind(this);
    this.getRowsContent = this.getRowsContent.bind(this);
    this.getSummary = this.getSummary.bind(this);
  }
  getHeadersContent() {
    return [{
      label: (0,external_wp_i18n_namespaceObject.__)('Code', 'woocommerce-gift-cards'),
      key: 'giftcard_code',
      required: true,
      isLeftAligned: true,
      isSortable: false
    }, {
      label: (0,external_wp_i18n_namespaceObject.__)('Expired Amount', 'woocommerce-gift-cards'),
      key: 'expired_balance',
      required: true,
      isSortable: true,
      isNumeric: true
    }, {
      label: (0,external_wp_i18n_namespaceObject.__)('Expired', 'woocommerce-gift-cards'),
      key: 'expire_date',
      defaultSort: true,
      isSortable: true,
      isNumeric: true
    }, {
      label: (0,external_wp_i18n_namespaceObject.__)('Issued', 'woocommerce-gift-cards'),
      key: 'date',
      isSortable: true,
      isNumeric: true
    }].filter(Boolean);
  }
  getRowsContent(data = []) {
    const {
      query
    } = this.props;
    const persistedQuery = (0,external_wc_navigation_namespaceObject.getPersistedQuery)(query);
    const dateFormat = (0,external_wc_wcSettings_namespaceObject.getSetting)('dateFormat', external_wc_date_namespaceObject.defaultTableDateFormat);
    const {
      render: renderCurrency,
      formatDecimal: getCurrencyFormatDecimal,
      getCurrencyConfig
    } = this.context;
    const currency = getCurrencyConfig();
    return (0,external_lodash_namespaceObject.map)(data, row => {
      const {
        create_date: createDate,
        giftcard_id: giftcardId,
        giftcard_code: giftcardCode,
        expired_balance: expiredBalance,
        expire_date: expireDate
      } = row;
      const giftcardDetailLink = (0,external_wc_wcSettings_namespaceObject.getAdminLink)('admin.php?page=gc_giftcards&section=edit&giftcard=' + giftcardId);
      return [{
        display: (0,external_React_namespaceObject.createElement)(external_wc_components_namespaceObject.Link, {
          href: giftcardDetailLink,
          type: "wp-admin"
        }, giftcardCode),
        value: giftcardCode
      }, {
        display: renderCurrency(expiredBalance),
        value: getCurrencyFormatDecimal(expiredBalance)
      }, {
        display: expireDate ? (0,external_React_namespaceObject.createElement)(external_wc_components_namespaceObject.Date, {
          date: expireDate,
          visibleFormat: dateFormat
        }) : (0,external_wp_i18n_namespaceObject.__)('N/A', 'woocommerce-gift-cards'),
        value: expireDate
      }, {
        display: createDate ? (0,external_React_namespaceObject.createElement)(external_wc_components_namespaceObject.Date, {
          date: createDate,
          visibleFormat: dateFormat
        }) : (0,external_wp_i18n_namespaceObject.__)('N/A', 'woocommerce-gift-cards'),
        value: createDate
      }].filter(Boolean);
    });
  }
  getSummary(totals) {
    const {
      giftcards_count: giftcardsCount = 0,
      expired_balance: expiredBalance = 0
    } = totals;
    const {
      formatAmount,
      getCurrencyConfig
    } = this.context;
    const currency = getCurrencyConfig();
    return [{
      label: (0,external_wp_i18n_namespaceObject._n)('gift card expired', 'gift cards expired', giftcardsCount, 'woocommerce-gift-cards'),
      value: (0,external_wc_number_namespaceObject.formatValue)(currency, 'number', giftcardsCount)
    }, {
      label: (0,external_wp_i18n_namespaceObject.__)('expired', 'woocommerce-gift-cards'),
      value: formatAmount(expiredBalance)
    }];
  }
  render() {
    const {
      filters,
      isRequesting,
      hideCompare,
      query
    } = this.props;
    return (0,external_React_namespaceObject.createElement)(report_table, {
      endpoint: "giftcards/expired",
      getHeadersContent: this.getHeadersContent,
      getRowsContent: this.getRowsContent,
      getSummary: this.getSummary,
      summaryFields: ['giftcards_count', 'expired_balance'],
      itemIdField: "giftcard_id",
      isRequesting: isRequesting,
      query: query,
      compareBy: hideCompare ? undefined : 'giftcards',
      tableQuery: {
        orderby: query.orderby || 'expire_date',
        order: query.order || 'desc',
        segmentby: query.segmentby
      },
      title: (0,external_wp_i18n_namespaceObject.__)('Gift Cards', 'woocommerce-gift-cards'),
      columnPrefsKey: "giftcards_expired_report_columns",
      filters: filters
    });
  }
}
ExpiredBalanceReportTable.contextType = CurrencyContext;
/* harmony default export */ var expired_table = (ExpiredBalanceReportTable);
;// CONCATENATED MODULE: ./resources/js/admin/analytics/report/expired/index.js

/**
 * External dependencies
 */




/**
 * WooCommerce dependencies
 */



/**
 * Internal dependencies.
 */




/**
 * SomewhereWarm dependencies.
 */



class ExpiredReport extends external_wp_element_namespaceObject.Component {
  getChartMeta() {
    const {
      query,
      isSingleProductView
    } = this.props;
    const isCompareView = false;
    const mode = 'time-comparison';
    const compareObject = 'giftcards';
    /* translators: Number of Bundles */
    const label = (0,external_wp_i18n_namespaceObject.__)('%d gift cards', 'woocommerce-gift-cards');
    return {
      itemsLabel: label,
      mode
    };
  }
  render() {
    const {
      itemsLabel,
      mode
    } = this.getChartMeta();
    const {
      path,
      query,
      isError,
      isRequesting
    } = this.props;
    if (isError) {
      return (0,external_React_namespaceObject.createElement)(ReportError, {
        isError: true
      });
    }
    const chartQuery = {
      ...query
    };
    return (0,external_React_namespaceObject.createElement)(external_wp_element_namespaceObject.Fragment, null, (0,external_React_namespaceObject.createElement)(external_wc_components_namespaceObject.ReportFilters, {
      query: query,
      path: path,
      showDatePicker: true,
      filters: filters
    }), (0,external_React_namespaceObject.createElement)(report_summary, {
      mode: mode,
      charts: expired_config_charts,
      endpoint: "giftcards/expired",
      isRequesting: isRequesting,
      query: chartQuery,
      selectedChart: getSelectedChart(query.chart, expired_config_charts),
      filters: filters
    }), (0,external_React_namespaceObject.createElement)(report_chart, {
      charts: expired_config_charts,
      mode: mode,
      endpoint: "giftcards/expired",
      isRequesting: isRequesting,
      itemsLabel: itemsLabel,
      path: path,
      query: chartQuery,
      selectedChart: getSelectedChart(chartQuery.chart, expired_config_charts),
      filters: filters
    }), (0,external_React_namespaceObject.createElement)(expired_table, {
      isRequesting: isRequesting,
      hideCompare: true,
      query: query,
      filters: filters
    }));
  }
}
ExpiredReport.propTypes = {
  path: (prop_types_default()).string.isRequired,
  query: (prop_types_default()).object.isRequired
};
/* harmony default export */ var expired = (ExpiredReport);
;// CONCATENATED MODULE: ./resources/js/admin/analytics/report/index.js

/**
 * External dependencies
 */


/**
 * Internal dependencies.
 */




/**
 * SomewhereWarm dependencies.
 */

const Report = props => {
  const {
    query
  } = props;
  props.query.section = query.section ? query.section : 'used';
  let main_content;
  switch (query.section) {
    case 'issued':
      main_content = (0,external_React_namespaceObject.createElement)(issued, {
        ...props
      });
      break;
    case 'used':
      main_content = (0,external_React_namespaceObject.createElement)(used, {
        ...props
      });
      break;
    case 'expired':
      main_content = (0,external_React_namespaceObject.createElement)(expired, {
        ...props
      });
      break;
    default:
      main_content = (0,external_React_namespaceObject.createElement)(report_error, null);
  }
  return (0,external_React_namespaceObject.createElement)(external_wp_element_namespaceObject.Fragment, null, main_content);
};
/* harmony default export */ var report = (Report);
;// CONCATENATED MODULE: ./resources/js/admin/analytics/index.js
/**
 * External dependencies
 */




/**
 * Local imports
 */


/**
 * Use the 'woocommerce_admin_reports_list' filter to add a report page.
 */
(0,external_wp_hooks_namespaceObject.addFilter)('woocommerce_admin_reports_list', 'woocommerce-gift-cards', reports => {
  return [...reports, {
    report: 'gift-cards',
    title: (0,external_wp_i18n_namespaceObject._x)('Gift Cards', 'analytics report menu item', 'woocommerce-gift-cards'),
    component: report,
    navArgs: {
      id: 'wc-gc-gift-cards-analytics-report'
    }
  }];
});
}();
/******/ })()
;