import React from 'react'
import {Label} from "../ui/label";

const CountrySelectField = ({
    name,
    label,
    control,
    error,
    required = false
}: CountrySelectProps) => {
    return (
        <div className="space-y-2">
            <Label htmlFor={name} className="form-label">{label}</Label>


        </div>
    )
}

export default CountrySelectField
