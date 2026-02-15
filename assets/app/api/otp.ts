import api from "@/lib/axiosApi";

export async function verifyOtp(data: VerifyOtpData) {
    return await api.post('otp/verify', data);
}
