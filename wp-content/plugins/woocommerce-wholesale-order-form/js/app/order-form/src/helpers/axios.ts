import axios from "axios";

interface IWpApi {
    root: string,
    timeout: number,
    headers: Object
}

const wpApiSettings: IWpApi = {
    root: '',
    timeout: 0,
    headers: {}
};

export default axios.create({
    baseURL: wpApiSettings.root,
    timeout: 8000,
    headers: {
        // "X-WP-Nonce": wpApiSettings.nonce
    }
});
