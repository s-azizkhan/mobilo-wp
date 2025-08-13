export enum EPaginationActionTypes {
    SET_PAGINATION_STATE = "SET_PAGINATION_STATE"
}

export const paginationActions = {
    setPaginationState: (payload: any) => ({
        type: EPaginationActionTypes.SET_PAGINATION_STATE,
        payload
    })
}