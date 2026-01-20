import React, {Fragment} from 'react'
import {SubmitHandler, useForm} from "react-hook-form";
import InputField from "@/components/forms/InputField";
import {Button} from "@/components/ui/button";
import FooterLink from "@/components/forms/FooterLink";

const defaultValues = {
    email: '',
    password: '',
};

const SignInPage = () => {
    const {
        register,
        handleSubmit,
        control,
        formState: {errors, isSubmitting, isLoading}
    } = useForm<SignInFormData>({
        defaultValues,
        mode: 'onBlur'
    });

    const onSubmit: SubmitHandler<SignInFormData> = async (data) => {
        try {
            console.log('Data: ', data);
        } catch (e) {
            console.log('Error: ', e);
        }
    };

    return (
        <div className='h-full flex flex-col justify-center'>
            <h1 className="form-title">
                Log in Your Account
            </h1>

            <form onSubmit={handleSubmit(onSubmit)} method='POST' className='space-y-5'>
                <InputField
                    name='email'
                    label='Email'
                    placeholder='Enter your email'
                    register={register}
                    error={errors.email}
                    validation={{required: 'Email is required', pattern: /^\w+@\.\w+$/, minLength: 4, maxLength: 55}}
                />

                <InputField
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
