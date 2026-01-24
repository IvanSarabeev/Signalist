import axios from "axios";

const api = axios.create({
    baseURL: '/api/v1',
    headers: {
        'Content-Type': 'application/json',
        // TODO: First Enable Symfony firewall and setup the correct security.yaml rules
    }
});

api.interceptors.response.use((response) => {
    return response;
}, (error) => {
    if (error.response) {
        return Promise.reject({
            response: error.response.data,
            message: error.response.data?.message || 'Communication Error',
        });
    }

    return Promise.reject({
        message: error.message || 'Unexpected Error',
    });
});

export default api;
