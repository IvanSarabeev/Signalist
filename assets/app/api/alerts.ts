import api from "@/lib/axiosApi";

export async function getAlerts(): Promise<AlertsResponse> {
    return api.get('/alerts');
}
