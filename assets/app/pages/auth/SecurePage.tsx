import React, {FC, useState} from 'react'
import {Input} from "@/components/ui/input";
import {Button} from "@/components/ui/button";
import {verifyOtp} from "@/app/api/otp";

const SecurePage: FC = () => {
    const [otp, setOtp] = useState('');

    const onOtpSubmit = () => {
        console.log('Verify OTP');
        // return verifyOtp({userId: '', otp});
    };

    return (
        <section className='max-w-md flex items-center justify-center'>
            <h1 className="text-xl font-semibold mb-4">
                Verify your login
            </h1>

            <Input
                type='text'
                maxLength={6}
                value={otp}
                onChange={(event) => setOtp(event.target.value)}
                className='w-full border p-2 text-center text-xl'
                placeholder='Enter 6-digit code'
            />

            <Button onClick={onOtpSubmit} variant='default'>Verify</Button>
        </section>
    )
}

export default SecurePage;
