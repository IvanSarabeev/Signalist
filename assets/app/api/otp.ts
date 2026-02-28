import api from "@/lib/axiosApi";

export async function verifyOtp(code: string): Promise<void> {
    return await api.post('otp/verify', {otp: code});
}
