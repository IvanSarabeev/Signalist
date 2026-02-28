import api from "@/lib/axiosApi";
import {type TokenRefreshResponse} from "@/app/types/security";

export const tokenRefresh = async (token: string): Promise<TokenRefreshResponse> => {
    return api.post('/token/refresh', {refresh_token: token});
};
