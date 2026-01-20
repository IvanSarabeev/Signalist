import React, {useState} from 'react'
import {Label} from "@/components/ui/label";
import countryList from "react-select-country-list";
import {Popover, PopoverContent, PopoverTrigger} from "@/components/ui/popover";
import {Button} from "@/components/ui/button";
import {Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList} from "@/components/ui/command";
import {Check} from "lucide-react";
import {cn} from "@/lib/utils";
import {Controller} from "react-hook-form";

type CountrySelectProp = {
    value: string;
    onChange: (value: string) => void;
}

const CountrySelect = ({value, onChange}: CountrySelectProp) => {
    const [isOpen, setIsOpen] = useState(false);

    const countries = countryList().getData();

    const getCountryFlag = (countryCode: string) => {
        const codePoints = countryCode
            .toUpperCase()
            .split('')
            .map((flag) => 127397 + flag.charCodeAt(0))

        return String.fromCodePoint(...codePoints);
    };

    const countryItem = value ? (
        <span className="flex items-center gap-2">
            <span>{getCountryFlag(value)}</span>
            <span>{countries?.find((country) => country.value === value)?.label}</span>
        </span>
        ) : (
            <p>Select your country...</p>
        )
    ;

    return (
        <Popover open={isOpen} onOpenChange={setIsOpen}>
            <PopoverTrigger asChild>
                <Button
                    variant="outline"
                    role='combobox'
                    aria-expanded={isOpen}
                    className='country-select-trigger'
                >
                    {countryItem}
                </Button>
            </PopoverTrigger>

            <PopoverContent align='start' className='w-full p-0 bg-gray-800 border-gray-600'>
                <Command className='w-full bg-gray-800 border-gray-600'>
                    <CommandInput className='country-select-input' placeholder='Search countries...'/>

                    <CommandEmpty className='country-select-empty'>No country found.</CommandEmpty>

                    <CommandList className='w-full max-h-60 bg-gray-800 scrollbar-hide-default'>
                        <CommandGroup className='bg-gray-800'>
                            {countries?.map((country) => (
                                <CommandItem
                                    key={country.value}
                                    value={`${country.label} ${country.value}`}
                                    onSelect={() => {
                                        onChange(country.value);
                                        setIsOpen((prevState) => !prevState);
                                    }}
                                    className='country-select-item'
                                >
                                    <Check
                                        className={cn(
                                            'mr-2 size-4 text-yellow-500',
                                            value === country.value ? 'opacity-100' : 'opacity-0'
                                        )}
                                    />
                                    <span className="flex items-center gap-2">
                                        <span>{getCountryFlag(country.value)}</span>
                                        <span>{country.label}</span>
                                    </span>
                                </CommandItem>
                            ))}
                        </CommandGroup>
                    </CommandList>
                </Command>
            </PopoverContent>
        </Popover>
    )
};

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

            <Controller
                control={control}
                name={name}
                rules={{
                    required: required ? `Please select ${label.toLowerCase()}` : false,
                }}
                render={({field}) => (
                    <CountrySelect value={field.value} onChange={field.onChange} />
                )}
            />

            {error && (
                <p className='text-sm text-red-500'>{error.message}</p>
            )}

            <p className="text-xs text-gray-500">Help us show market data and news relevant to you.</p>
        </div>
    )
}

export default CountrySelectField;
