import api from "@/lib/axiosApi";
import type {OtpVerifyResponse} from "@/app/types/security";

export async function verifyOtp(code: string): Promise<OtpVerifyResponse> {
    return await api.post('otp/verify', {otp: code});
}
