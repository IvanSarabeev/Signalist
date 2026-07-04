import api from "@/lib/axiosApi";

export async function getAlerts(): Promise<AlertsResponse> {
    return api.get('/alerts');
}

export async function createAlert(data: CreateAlertForm): Promise<CreateAlertResponse> {
    return api.post('/alerts', data);
}

export async function deleteAlert(id: number): Promise<void> {
    return api.delete(`/alerts/${id}`);
}
