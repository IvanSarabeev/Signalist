import React from 'react'
import {SubmitHandler, useForm} from "react-hook-form";
import InputField from "@/components/forms/InputField";
import {Button} from "@/components/ui/button";
import FooterLink from "@/components/forms/FooterLink";
import {authLogin} from "@/app/api/auth";
import {addNotification} from "@/lib/utils";
import {useNavigate} from "react-router";

const defaultValues = {
    email: '',
    password: '',
};

const SignInPage = () => {
    const navigate = useNavigate();
    const {
        register,
        handleSubmit,
        formState: {errors, isSubmitting, isLoading}
    } = useForm<SignInFormData>({
        defaultValues,
        mode: 'onBlur'
    });

    const onSubmit: SubmitHandler<SignInFormData> = async (data) => {
        try {
            const authResponse = await authLogin(data);
            if (authResponse?.status) {
                addNotification({
                    type: "success",
                    message: "Logged in successfully!",
                    description: "Welcome back, you are being redirected.",
                    duration: 4000
                });
                // TODO: Change the URL to the protected account/dashboard Page.
                navigate("/account");
                return;
            }
        } catch (error: unknown) {
            const apiError = error as ApiError;

            addNotification({
                type: "error",
                message: "Authentication Error!",
                description: apiError.message,
            });
        }
    };

    return (
        <div className='h-full flex flex-col justify-center'>
            <h1 className="form-title">
                Log in Your Account
            </h1>

            <form onSubmit={handleSubmit(onSubmit)} method='POST' className='space-y-5'>
                <InputField
                    type='email'
                    name='email'
                    label='Email'
                    placeholder='Enter your email'
                    register={register}
                    error={errors.email}
                    // validation={{required: 'Email is required', pattern: /^\w+@\.\w+$/, minLength: 4, maxLength: 55}}
                />

                <InputField
                    type='password'
                    name='password'
                    label='Password'
                    placeholder='Enter a strong password'
                    register={register}
                    error={errors.password}
                    validation={{required: 'Password is required', minLength: 6}}
                />

                <Button type='submit' disabled={isSubmitting || isLoading} className='yellow-btn w-full mt-5'>
                    {isSubmitting ? 'Logging in' : 'Log in'}
                </Button>

                <FooterLink
                    text="Don't have an account?"
                    linkText='Sign Up'
                    href='/sign-up'
                />
            </form>
        </div>
    )
}

export default SignInPage
