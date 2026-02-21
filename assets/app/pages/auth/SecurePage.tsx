import React, {FC, useEffect} from 'react'
import {Button} from "@/components/ui/button";
import {verifyOtp} from "@/app/api/otp";
import InputField from "@/components/forms/InputField";
import {useLocation, useNavigate} from "react-router";
import {SubmitHandler, useForm} from "react-hook-form";
import FooterLink from "@/components/forms/FooterLink";
import {addNotification} from "@/lib/utils";

type SecureRouteState = {userId: number}

const SecurePage: FC = () => {
    const location = useLocation() as { state: SecureRouteState | null };
    const navigate = useNavigate();

    const userId = location.state?.userId ?? null;

    useEffect(() => {
        if (!userId) {
            navigate("/");
            addNotification({
                type: "warning",
                message: 'Unauthorized access!',
                duration: 2500
            });
        }
    }, [userId, navigate]);

    const {
        register,
        handleSubmit,
        formState: {errors, isSubmitting, isLoading},
    } = useForm<VerifyOtpData>({
        defaultValues: {
            user_id: userId ?? 0,
            otp: '',
        },
        mode: 'onBlur'
    });

    if (userId === null) return null;

    const onSubmit: SubmitHandler<VerifyOtpData> = async (data) => {
        if (data.otp.length < 4) {
            addNotification({
                type: 'error',
                message: 'Incorrect OTP!',
                description: 'Includes invalid length'
            });
            return;
        }

        try {
            const otpResponse = await verifyOtp(data);
            console.log('OTP Response: ', otpResponse);
            if (otpResponse?.status) {
                addNotification({
                    type: "success",
                    message: "Successfully Authenticated!",
                    description: "Welcome back",
                    duration: 4000
                });
                navigate("/account");
                return;
            }
        } catch (error: unknown) {
            const apiError = error as ApiError;

            addNotification({
                type: 'error',
                message: 'Authentication Error!',
                description: apiError.message || 'Invalid OTP (One Time Password)'
            });
        }
    };

    const isBtnDisabled = isSubmitting || isLoading;

    console.log('Btn Is Disabled:', isBtnDisabled);

    return (
        <div className='h-full flex flex-col justify-center'>
            <h1 className="form-title">
                Verify your login
            </h1>

            <form onSubmit={handleSubmit(onSubmit)} method='POST' className='space-y-5'>
                <InputField
                    name='otp'
                    label='OTP / (One Time Password)'
                    placeholder='Enter the 4-digit code'
                    register={register}
                    error={errors.otp}
                />

                <Button type='submit' disabled={isBtnDisabled} className='yellow-btn w-full mt-5'>
                    {isSubmitting ? "Verifying Code" : "Verify"}
                </Button>

                <FooterLink
                    text="Didn't receive the code"
                    linkText='Resend'
                    href='/secure'
                />
            </form>
        </div>
    )
}

export default SecurePage;
