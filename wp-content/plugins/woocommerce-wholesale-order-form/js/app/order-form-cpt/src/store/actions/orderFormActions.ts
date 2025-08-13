export enum EOrderFormActionTypes {
    SET_LOADING = "SET_LOADING",
    SET_ORDER_FORM_DATA = "SET_ORDER_FORM_DATA",
    SET_CART_SUBTOTAL = "SET_CART_SUBTOTAL",
    FETCH_ORDER_FORMS = "FETCH_ORDER_FORMS",
    FETCH_ORDER_FORM = "FETCH_ORDER_FORM",
    ADD_NEW_ORDER_FORM = "ADD_NEW_ORDER_FORM",
    EDIT_ORDER_FORM = "EDIT_ORDER_FORM",
    DELETE_ORDER_FORM = "DELETE_ORDER_FORM",
    SET_PAGE = "SET_PAGE",
    GET_ORDER_FORM_SETTINGS = "GET_ORDER_FORM_SETTINGS",
    SET_ORDER_FORM_SETTINGS = "SET_ORDER_FORM_SETTINGS",
    GET_ORDER_FORM_SETTINGS_DATA = "GET_ORDER_FORM_SETTING_DATA",
    SET_ORDER_FORM_SETTINGS_DATA = "SET_ORDER_FORM_SETTING_DATA"
}

export const orderFormActions = {
    setLoading: (payload: any) => ({
        type: EOrderFormActionTypes.SET_LOADING,
        payload
    }),
    setOrderFormData: (payload: any) => ({
        type: EOrderFormActionTypes.SET_ORDER_FORM_DATA,
        payload
    }),
    setCartSubtotal: (payload: any) => ({
        type: EOrderFormActionTypes.SET_CART_SUBTOTAL,
        payload
    }),
    fetchOrderForms: (payload: any) => ({
        type: EOrderFormActionTypes.FETCH_ORDER_FORMS,
        payload
    }),
    fetchOrderForm: (payload: any) => ({
        type: EOrderFormActionTypes.FETCH_ORDER_FORM,
        payload
    }),
    addNewOrderForm: (payload: any) => ({
        type: EOrderFormActionTypes.ADD_NEW_ORDER_FORM,
        payload
    }),
    editOrderForm: (payload: any) => ({
        type: EOrderFormActionTypes.EDIT_ORDER_FORM,
        payload
    }),
    deleteOrderForm: (payload: any) => ({
        type: EOrderFormActionTypes.DELETE_ORDER_FORM,
        payload
    }),
    setPage: (payload: any) => ({
        type: EOrderFormActionTypes.SET_PAGE,
        payload
    }),
    getOrderFormSettings: (payload: any) => ({
        type: EOrderFormActionTypes.GET_ORDER_FORM_SETTINGS,
        payload
    }),
    setOrderFormSettings: (payload: any) => ({
        type: EOrderFormActionTypes.SET_ORDER_FORM_SETTINGS,
        payload
    }),
    setOrderFormSettingsData: (payload: any) => ({
        type: EOrderFormActionTypes.SET_ORDER_FORM_SETTINGS_DATA,
        payload
    })
};