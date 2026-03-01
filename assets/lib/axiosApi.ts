import axios from "axios";
import {tokenRefresh} from "@/app/api/token";
import {hideWaveLoader, showWaveLoader} from "@/components/waveLoader";

const api = axios.create({
    baseURL: '/api/v1',
    headers: {
        'Content-Type': 'application/json',
        // TODO: First Enable Symfony firewall and setup the correct security.yaml rules
    }
});

let isRefreshing = false;
let refreshSubscribers: ((token: string) => void)[] = [];

const subscribeTokenRefresh = (cb: (token: string) => void) => {
    refreshSubscribers.push(cb);
};

const onRefreshed = (token: string) => {
    refreshSubscribers.forEach((cb) => cb(token));
    refreshSubscribers = [];
};

export const setupInterceptors = (
    getAccessToken: () => string | null,
    getRefreshToken: () => string | null,
    setTokens: (access: string, refresh: string) => void,
    logout: () => void
) => {
    api.interceptors.request.use((config) => {
        showWaveLoader();
        const token = getAccessToken();
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    });

    api.interceptors.response.use(
        (response) => {
            hideWaveLoader();
            return response.data ?? response;
        },
        async (error) => {
            hideWaveLoader();
            const currentMainRequest = error.config;

            if (error.response?.status !== 401) {
                throw error;
            }

            if (currentMainRequest._retry) {
                logout();
                throw error;
            }

            if (isRefreshing) {
                return new Promise((resolve) => {
                    subscribeTokenRefresh((token) => {
                        currentMainRequest.headers.Authorization = `Bearer ${token}`;
                        resolve(api(currentMainRequest));
                    });
                });
            }

            currentMainRequest._retry = true;
            isRefreshing = true;

            try {
                const refreshToken = getRefreshToken();

                if (!refreshToken) throw new Error('Missing refresh token');

                const {message, access_token, refresh_token} = await tokenRefresh(refreshToken);

                if (!access_token || !refresh_token) throw new Error(message);

                setTokens(access_token, refresh_token);
                onRefreshed(access_token);
                return api(currentMainRequest);
            } catch (refreshError: unknown) {
                // Any refresh failure invalidates authentication state.
                logout();
                return Promise.reject(refreshError);
            } finally {
                isRefreshing = false;
            }

        }
    )
}

export default api;
