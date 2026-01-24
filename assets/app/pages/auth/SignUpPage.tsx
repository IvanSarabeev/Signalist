import React, {Fragment, useState} from 'react'
import {SubmitHandler, useForm} from "react-hook-form";
import {Button} from "@/components/ui/button";
import InputField from "@/components/forms/InputField";
import SelectField from "@/components/forms/SelectField";
import {INVESTMENT_GOALS, PREFERRED_INDUSTRIES, RISK_TOLERANCE_OPTIONS} from "@/lib/constants";
import CountrySelectField from "@/components/forms/CountrySelectField";
import FooterLink from "@/components/forms/FooterLink";
import {authRegister} from "@/app/api/auth";

const defaultValues = {
    fullName: 'John Ali',
    email: 'test@example.com',
    password: 'Test123.',
    country: 'BG',
    investmentGoals: 'Growth',
    riskTolerance: 'Medium',
    preferredIndustry: 'Technology'
};

const SignUpPage = () => {
    const [viewPassword, setViewPassword] = useState(false);

    const {
        register,
        handleSubmit,
        control,
        formState: {errors, isLoading, isSubmitting}
    } = useForm<SignUpFormData>({
        defaultValues,
        mode: "onBlur",
    });

    const onSubmit: SubmitHandler<SignUpFormData> = async (data) => {
        try {
            console.log('Request Data: ', data);
            const authenticationResponse = await authRegister(data);

            console.log('Authentication Response: ', authenticationResponse);
        } catch (e) {
            console.log('Error: ', e);
        }
    };

    const isBtnDisabled = isSubmitting || isLoading;

    return (
        <Fragment>
            <h1 className="form-title">
                SignUpPage & Personalize
            </h1>

            <form onSubmit={handleSubmit(onSubmit)} method='POST' className="space-y-5">
                <InputField
                    name="fullName"
                    label="Full Name"
                    placeholder="John Doe"
                    register={register}
                    error={errors.fullName}
                    validation={{required: true, minLength: 2}}
                />

                <InputField
                    type='email'
                    name="email"
                    label="Email"
                    placeholder="contact@signalist.com"
                    register={register}
                    error={errors.email}
                    // validation={{required: 'Email is required', pattern: /^\w+@\.\w+$/, minLength: 4, maxLength: 55}}
                />

                <InputField
                    type={viewPassword ? "text" : "password"}
                    name="password"
                    label="Password"
                    placeholder="Enter a strong password"
                    register={register}
                    error={errors.password}
                    validation={{required: 'Password is required', minLength: 6}}
                />

                <Button type='button' onClick={() => setViewPassword((prevState) => !prevState)}>View Password</Button>

                <CountrySelectField
                    name="country"
                    label="Country"
                    control={control}
                    error={errors.country}
                    required
                />

                <SelectField
                    name="investmentGoals"
                    label="Investment Goals"
                    placeholder="Select your investment goal"
                    options={INVESTMENT_GOALS}
                    control={control}
                    error={errors.investmentGoals}
                    required
                />

                <SelectField
                    name="riskTolerance"
                    label="Risk Tolerance"
                    placeholder="Select your risk level"
                    options={RISK_TOLERANCE_OPTIONS}
                    control={control}
                    error={errors.riskTolerance}
                    required
                />

                <SelectField
                    name="preferredIndustry"
                    label="Preferred Industry"
                    placeholder="Select your preffered industry"
                    options={PREFERRED_INDUSTRIES}
                    control={control}
                    error={errors.preferredIndustry}
                    required
                />

                <Button type="submit" disabled={isBtnDisabled} className="yellow-btn w-full mt-5">
                    {isSubmitting ? "Creating Account" : "Start Your Investing Journey"}
                </Button>

                <FooterLink
                    text='Already have an account ?'
                    linkText='Log in'
                    href={'/sign-in'}
                />
            </form>
        </Fragment>
    )
}

export default SignUpPage
