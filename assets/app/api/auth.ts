import api from "@/lib/axiosApi";

export async function authRegister(data: SignUpFormData) {
    return await api.post('authentication/register', data);
}

export async function authLogin(data: SignInFormData) {
    return await api.post('authentication/login', data);
}

export async function authLogout() {
    return await api.post('authentication/logout');
}
