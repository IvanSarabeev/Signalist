import React, {useState} from 'react'
import {Label} from "@/components/ui/label";
import {Input} from "@/components//ui/input";
import {cn} from "@/lib/utils";
import {Eye} from "lucide-react";
import {Button} from "@/components/ui/button";

const InputField = ({
    name,
    label,
    placeholder,
    type = "text",
    register,
    error,
    validation,
    disabled,
    value
}: FormInputProps) =>  {
    const [isPasswordVisible, setIsPasswordVisible] = useState(false);
    const isInputTypePassword = type === "password";
    const inputType = isInputTypePassword && isPasswordVisible ? "text" : type;

    return (
        <div className="space-y-2">
            <Label htmlFor={name} className="form-label">{label}</Label>

            <div className="relative size-full">
                <Input
                    type={inputType}
                    id={name}
                    placeholder={placeholder}
                    disabled={disabled}
                    value={value}
                    className={cn('form-input', {'opacity-50 cursor-not-allowed': disabled})}
                    {...register(name as any, validation as any)}
                />
                {isInputTypePassword && (
                    <Button
                        size="sm"
                        type="button"
                        variant="password"
                        className={cn("absolute size-fit inset-y-1/3 right-[2.5%]")}
                        onClick={() => setIsPasswordVisible((prevState) => !prevState)}
                    >
                        <Eye className='size-6 text-white' height={24} width={24}/>
                    </Button>
                )}
            </div>

            {error && (
                <p className="text-sm text-red-500 font-medium">{error.message}</p>
            )}
        </div>
    )
}

export default InputField
