export enum EOrderFormActionTypes {
    SET_FETCHING = "SET_FETCHING",
    FETCH_PRODUCTS = "FETCH_PRODUCTS",
    FETCH_VARIATIONS = "FETCH_VARIATIONS",
    SET_APP_STATE = "SET_APP_STATE",
    FETCH_CATEGORIES = "FETCH_CATEGORIES",
    SET_CATEGORIES = "SET_CATEGORIES",
    SET_CART_SUB_TOTAL = "SET_CART_SUB_TOTAL",
    SET_VARIATIONS = "SET_VARIATIONS",
    SET_CART_SUBTOTAL = "SET_CART_SUBTOTAL"
}

export const orderFormActions = {
    setFetching: (payload: any) => ({
        type: EOrderFormActionTypes.SET_FETCHING,
        payload
    }),
    fetchProducts: (payload: any) => ({
        type: EOrderFormActionTypes.FETCH_PRODUCTS,
        payload
    }),
    fetchVariations: (payload: any) => ({
        type: EOrderFormActionTypes.FETCH_VARIATIONS,
        payload
    }),
    setAppState: (payload: any) => ({
        type: EOrderFormActionTypes.SET_APP_STATE,
        payload
    }),
    fetchCategories: (payload: any) => ({
        type: EOrderFormActionTypes.FETCH_CATEGORIES,
        payload
    }),
    setCategories: (payload: any) => ({
        type: EOrderFormActionTypes.SET_CATEGORIES,
        payload
    }),
    setCartSubtotal: (payload: any) => ({
        type: EOrderFormActionTypes.SET_CART_SUB_TOTAL,
        payload
    }),
    setVariations: (payload: any) => ({
        type: EOrderFormActionTypes.SET_VARIATIONS,
        payload
    })
};