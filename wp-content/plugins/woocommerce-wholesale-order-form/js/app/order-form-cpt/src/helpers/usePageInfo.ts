import { useLocation } from 'react-router-dom';

export const usePageInfo = (orderFormID?: any) => {

    const pathName = useLocation().pathname;
    const params = useLocation().search;
    const urlParams = new URLSearchParams(params);
    const editPath = `${pathName}?page=order-forms&sub-page=edit&post=${orderFormID}`;

    const pageType = urlParams.get("sub-page");
    const postID = urlParams.get("post") || 0;

    return {
        pathName,
        params,
        urlParams,
        editPath,
        pageType,
        postID
    };

}