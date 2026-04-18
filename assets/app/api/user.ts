import api from "@/lib/axiosApi";

export async function getCurrentUser(): Promise<UserResponse> {
    return api.get('/user');
}
