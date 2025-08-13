import axios from "axios";

declare var wpApiSettings: any;

export default axios.create({
    baseURL: wpApiSettings.root,
    // timeout: 1000,
    headers: {
        // "X-WP-Nonce": wpApiSettings.nonce
    }
});
