import axios, {AxiosError} from "axios";

const api = axios.create({
    baseURL: '/api/v1',
    headers: {
        'Content-Type': 'application/json',
        // TODO: First Enable Symfony firewall and setup the correct security.yaml rules
    }
});

api.interceptors.response.use((response) => {
    return response?.data;
}, (error: AxiosError<any>) => {
    return Promise.reject({
        status: error.response?.status,
        response: error.response?.data ?? {},
        message: error.response?.data?.message ?? "Communication Error"
    });
});

export default api;
