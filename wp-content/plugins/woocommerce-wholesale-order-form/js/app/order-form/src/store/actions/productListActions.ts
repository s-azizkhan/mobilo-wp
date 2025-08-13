export enum EProductListActionTypes {
    SET_PRODUCT_LIST_COLUMNS = "SET_PRODUCT_LIST_COLUMNS",
    SET_PRODUCT_QUANTITY = "SET_PRODUCT_QUANTITY",
    SET_SELECTED_PRODUCTS = "SET_SELECTED_PRODUCTS",
    SET_SELECTED_VARIATIONS = "SET_SELECTED_VARIATIONS",
    ADD_PRODUCT_TO_CART = "ADD_PRODUCT_TO_CART",
    ADD_PRODUCTS_TO_CART = "ADD_PRODUCTS_TO_CART",
}

export const productListActions = {
    setProductListColumns: (payload: any) => ({
        type: EProductListActionTypes.SET_PRODUCT_LIST_COLUMNS,
        payload
    }),
    setProductQuantity: (payload: any) => ({
        type: EProductListActionTypes.SET_PRODUCT_QUANTITY,
        payload
    }),
    setSelectedProducts: (payload: any) => ({
        type: EProductListActionTypes.SET_SELECTED_PRODUCTS,
        payload
    }),
    setSelectedVariations: (payload: any) => ({
        type: EProductListActionTypes.SET_SELECTED_VARIATIONS,
        payload
    }),
    addProductToCartAction: (payload: any) => ({
        type: EProductListActionTypes.ADD_PRODUCT_TO_CART,
        payload
    }),
    addProductsToCartAction: (payload: any) => ({
        type: EProductListActionTypes.ADD_PRODUCTS_TO_CART,
        payload
    })
}