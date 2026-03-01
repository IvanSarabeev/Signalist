import React from 'react'
import {SubmitHandler, useForm} from "react-hook-form";
import InputField from "@/components/forms/InputField";
import {Button} from "@/components/ui/button";
import FooterLink from "@/components/forms/FooterLink";
import {addNotification} from "@/lib/utils";
import {useNavigate} from "react-router";
import {useAuth} from "@/hooks/useAuth";

const defaultValues = {
    email: '',
    password: '',
};

const SignInPage = () => {
    const {authenticate} = useAuth();
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
        const {status} = await authenticate(data);
        if (status) {
            addNotification({
                type: "success",
                message: "Successful!",
                description: "Welcome back, you are being redirected.",
                duration: 4000
            });
            navigate("/secure");
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
