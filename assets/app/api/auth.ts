import api from "@/lib/axiosApi";

export async function authRegister(data: SignUpFormData) {
    return await api.post('authentication/register', data);
}

type ApiAuthLogin = {
    status: boolean;
    is_otp_required?: boolean;
    user_id?: number;
    message?: string;
    errors?: string[];
    invalid_fields?: string[];
}

export async function authLogin(data: SignInFormData): Promise<ApiAuthLogin> {
    return await api.post('authentication/login', data);
}

export async function authLogout() {
    return await api.post('authentication/logout');
}
