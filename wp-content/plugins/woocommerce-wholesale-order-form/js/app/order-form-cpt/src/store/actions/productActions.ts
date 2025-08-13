export enum EProductActionTypes {
    FETCH_PRODUCTS = "FETCH_PRODUCTS",
    SET_PRODUCTS = "SET_PRODUCTS",
    SET_CATEGORIES = "SET_CATEGORIES",
    FETCH_CATEGORIES = "FETCH_CATEGORIES",
    SET_VARIATIONS = "SET_VARIATIONS"
}

export const productActions = {
    fetchProducts: (payload: any) => ({
        type: EProductActionTypes.FETCH_PRODUCTS,
        payload
    }),
    setProducts: (payload: any) => ({
        type: EProductActionTypes.SET_PRODUCTS,
        payload
    }),
    setVariations: (payload: any) => ({
        type: EProductActionTypes.SET_VARIATIONS,
        payload
    }),
    fetchCategories: (payload: any) => ({
        type: EProductActionTypes.FETCH_CATEGORIES,
        payload
    }),
    setCategories: (payload: any) => ({
        type: EProductActionTypes.SET_CATEGORIES,
        payload
    })
};