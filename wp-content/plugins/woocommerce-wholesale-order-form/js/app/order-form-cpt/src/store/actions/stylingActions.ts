export enum EStylingActionTypes {
    SET_SHOW_STYLING = "SET_SHOW_STYLING",
    SET_STYLES = "SET_STYLES"
}

export const stylingActions = {
    setShowStyling: (payload: any) => ({
        type: EStylingActionTypes.SET_SHOW_STYLING,
        payload
    }),
    setStyles: (payload: any) => ({
        type: EStylingActionTypes.SET_STYLES,
        payload
    }),
};