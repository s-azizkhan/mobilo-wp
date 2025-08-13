export enum EOrderFormDataActionTypes {
    FETCH_ORDER_FORM_DATA = "FETCH_ORDER_FORM_DATA",
    SET_ORDER_FORM_DATA = "SET_ORDER_FORM_DATA",
    SET_ORDER_FORM_PRODUCTS = "SET_ORDER_FORM_PRODUCTS",
    SET_ORDER_FORM_PAGINATION = "SET_ORDER_FORM_PAGINATION",
    SET_PAGE_SELECTED_ALL = "SET_PAGE_SELECTED_ALL",
    SET_ORDER_FORM_FILTERS = "SET_ORDER_FORM_FILTERS",
    SET_ORDER_FORM_SELECTED_PRODUCTS_TO_ADD_CART = "SET_ORDER_FORM_SELECTED_PRODUCTS_TO_ADD_CART",
    UPDATE_ORDER_FORM_SELECTED_PRODUCTS = "UPDATE_ORDER_FORM_SELECTED_PRODUCTS",
    SET_ORDER_FORM_CART_SUBTOTAL = "SET_ORDER_FORM_CART_SUBTOTAL"
}

export const orderFormDataActions = {
    fetchOrderFormData: (payload: any) => ({
        type: EOrderFormDataActionTypes.FETCH_ORDER_FORM_DATA,
        payload
    }),
    setOrderFormData: (payload: any) => ({
        type: EOrderFormDataActionTypes.SET_ORDER_FORM_DATA,
        payload
    }),
    setOrderFormProducts: (payload: any) => ({
        type: EOrderFormDataActionTypes.SET_ORDER_FORM_PRODUCTS,
        payload
    }),
    setOrderFormPagination: (payload: any) => ({
        type: EOrderFormDataActionTypes.SET_ORDER_FORM_PAGINATION,
        payload
    }),
    setPageSelectedAll: (payload: any) => ({
        type: EOrderFormDataActionTypes.SET_PAGE_SELECTED_ALL,
        payload
    }),
    setFormFilters: (payload: any) => ({
        type: EOrderFormDataActionTypes.SET_ORDER_FORM_FILTERS,
        payload
    }),
    setSelectedProductsToAddToCart: (payload: any) => ({
        type: EOrderFormDataActionTypes.SET_ORDER_FORM_SELECTED_PRODUCTS_TO_ADD_CART,
        payload
    }),
    updateSelectedProducts: (payload: any) => ({
        type: EOrderFormDataActionTypes.UPDATE_ORDER_FORM_SELECTED_PRODUCTS,
        payload
    }),
    setCartSubtotal: (payload: any) => ({
        type: EOrderFormDataActionTypes.SET_ORDER_FORM_CART_SUBTOTAL,
        payload
    })
};