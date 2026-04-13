import api from "@/lib/axiosApi";

export async function getCurrentUser() {
    return api.get('/user');
}
