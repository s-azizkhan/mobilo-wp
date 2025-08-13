export enum EDragAndDropActionTypes {
    RESET_DND_DATA = "RESET_DND_DATA",
    SET_DND_DATA = "SET_DND_DATA",
    ARRANGE_TABLE_COLUMNS = "ARRANGE_TABLE_COLUMNS",
    ARRANGE_HEADER_FOOTER_ROWS = "ARRANGE_HEADER_FOOTER_ROWS",
    ARRANGE_HEADER_FOOTER_COLUMNS = "ARRANGE_HEADER_FOOTER_COLUMNS",
    TRANSFER_ITEMS = "TRANSFER_ITEMS"
}

export const dragAndDropActions = {
    setDndData: (payload: any) => ({
        type: EDragAndDropActionTypes.SET_DND_DATA,
        payload
    }),
    resetDndData: (payload: any) => ({
        type: EDragAndDropActionTypes.RESET_DND_DATA,
        payload
    }),
    transferItems: (payload: any) => ({
        type: EDragAndDropActionTypes.TRANSFER_ITEMS,
        payload
    }),
    arrangeTableColumns: (payload: any) => ({
        type: EDragAndDropActionTypes.ARRANGE_TABLE_COLUMNS,
        payload
    }),
    arrangeHeaderFooterRows: (payload: any) => ({
        type: EDragAndDropActionTypes.ARRANGE_HEADER_FOOTER_ROWS,
        payload
    }),
    arrangeHeaderFooterColumns: (payload: any) => ({
        type: EDragAndDropActionTypes.ARRANGE_HEADER_FOOTER_COLUMNS,
        payload
    })
};