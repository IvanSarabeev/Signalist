export interface AuthRegisterResponse {
    status: boolean;
    message?: string;
    errors?: string[];
    invalid_fields?: string[];
}

export interface AuthLoginResponse {
    status: boolean;
    token?: string;
    message?: string;
    errors?: string[];
    invalid_fields?: string[];
}

export interface TokenRefreshResponse {
    message: string;
    access_token?: string;
    refresh_token?: string;
}
