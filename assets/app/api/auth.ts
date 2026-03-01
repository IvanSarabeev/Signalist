import api from "@/lib/axiosApi";
import type {AuthLoginResponse, AuthRegisterResponse} from "@/app/types/security";

export async function authRegister(data: SignUpFormData): Promise<AuthRegisterResponse> {
    return await api.post('authentication/register', data);
}

export async function authLogin(data: SignInFormData): Promise<AuthLoginResponse> {
    return await api.post('authentication/login', data);
}

export async function authLogout(): Promise<void> {
    return await api.post('authentication/logout');
}
