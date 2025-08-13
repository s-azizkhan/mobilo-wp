export enum EMinRequirementsTypes {
    WWPP_REQUIRED_VERSIONS = "WWPP_REQUIRED_VERSIONS",
    REMOVE_WWPP_MIN_FAIL_MESSAGE = "REMOVE_WWPP_MIN_FAIL_MESSAGE"
}

export const minRequirements = {
    wwppRequiredVersions: (payload: any) => ({
        type: EMinRequirementsTypes.WWPP_REQUIRED_VERSIONS,
        payload
    }),
    removeMinimumWWPPFailMessage: (payload: any) => ({
        type: EMinRequirementsTypes.REMOVE_WWPP_MIN_FAIL_MESSAGE,
        payload
    })
};