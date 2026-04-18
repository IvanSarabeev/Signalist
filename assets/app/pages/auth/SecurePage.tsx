import React, {FC} from 'react'
import {Button} from "@/components/ui/button";
import InputField from "@/components/forms/InputField";
import {SubmitHandler, useForm} from "react-hook-form";
import FooterLink from "@/components/forms/FooterLink";
import {addNotification} from "@/lib/utils";
import {useNavigate} from "react-router";
import {useAuth} from "@/hooks/useAuth";

type SecurePageData = {otp: string};

const SecurePage: FC = () => {
    const {accessToken, otpVerification} = useAuth();
    const navigate = useNavigate();
    const {
        register,
        handleSubmit,
        formState: {errors, isSubmitting, isLoading},
    } = useForm<SecurePageData>({
        defaultValues: {otp: ''},
        mode: 'onBlur'
    });

    if (!accessToken) navigate('/');

    const onSubmit: SubmitHandler<SecurePageData> = async (data) => {
        if (data.otp.length < 4) {
            addNotification({
                type: 'error',
                message: 'Incorrect OTP!',
                description: 'Includes invalid length'
            });
            return;
        }

        try {
            const otpResponse = await otpVerification(data.otp);

            if (otpResponse.status) {
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
