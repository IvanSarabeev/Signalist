import React, {FC, memo, SetStateAction, useEffect} from 'react'
import {Dialog, DialogContent, DialogHeader, DialogTitle} from "@/components/ui/dialog";
import {Button} from "@/components/ui/button";
import {SubmitHandler, useForm} from "react-hook-form";
import {createAlert, updateAlert} from "@/app/api/alerts";
import InputField from "@/components/forms/InputField";
import SelectField from "@/components/forms/SelectField";
import {ALERT_TYPE_OPTIONS, CONDITION_OPTIONS, FREQUENCY_OPTIONS} from "@/lib/constants";
import {addNotification} from "@/lib/utils";

type AddAlertModalProps = {
    type: 'edit' | 'create';
    isOpen: boolean;
    setAlertDialogOpen:  (value: boolean) => void;
    selectedStock: StockWithData | null;
    alertPrice: number;
    setAlerts: (value: SetStateAction<Alert[]>) => void;
    selectedAlert: Alert | null;
}

const defaultValues = {
    symbol: '',
    alertName: '',
    alertType: '',
    conditionQuality: '',
    frequency: '',
    thresholdValue: 0,
};

const AlertModal: FC<AddAlertModalProps> = ({
    type,
    isOpen,
    setAlertDialogOpen,
    selectedStock,
    alertPrice,
    setAlerts,
    selectedAlert,
}) => {
    const {
        control,
        register,
        handleSubmit,
        reset,
        formState: {errors, isSubmitting, isLoading}
    } = useForm<CreateAlertForm>({
        defaultValues,
        mode: 'onBlur'
    });

    useEffect(() => {
        if (!isOpen) return;

        reset({
            ...defaultValues,
            thresholdValue: alertPrice,
            symbol: selectedStock?.symbol ?? "",
        });
    }, [isOpen, alertPrice, selectedStock, reset]);

    const onSubmit: SubmitHandler<CreateAlertForm> = async (data: CreateAlertForm) => {
        try {
            if (type === 'edit' && selectedAlert) {
                const {status, data: alertData} = await updateAlert(selectedAlert.id, data);

                if (status) {
                    setAlerts((prev) => prev.map((a) => (a.id === alertData.id ? alertData : a)));

                    addNotification({
                        type: 'success',
                        message: 'Updated Alert',
                        description: `Successfully updated alert for tracking ${data.symbol}`,
                        duration: 2500
                    });
                    setAlertDialogOpen(false);
                }
            } else {
                const {status, data: alertData} = await createAlert(data);

                if (status) {
                    // Update the alertsData
                    setAlerts((prevState) => [alertData, ...prevState]);

                    addNotification({
                        type: 'success',
                        message: 'Created Alert',
                        description: `Successfully added alert for tracking ${data.symbol}`,
                        duration: 2500
                    });
                    setAlertDialogOpen(false);
                }
            }
        } catch (e: unknown) {
            const error = e as ApiError;

            addNotification({
                type: "error",
                message: error.message || 'Error',
                description: `Unable to ${type === 'edit' ? 'update' : 'create'} alert for ${data.alertName}`,
                duration: 3000
            });
        }
    };

    const alertTitle = type === 'create' ? 'Price Alert' : 'Edit Alert';
    const buttonLabel = type === 'edit' ? 'Save changes' : 'Create alert';

    return (
        <Dialog open={isOpen} onOpenChange={setAlertDialogOpen}>
            <DialogContent className="alert-dialog">
                <DialogHeader>
                    <DialogTitle className="text-2xl alert-bold text-gray-100 leading-8 tracking-tight">
                        {alertTitle}
                    </DialogTitle>
                </DialogHeader>

                <form onSubmit={handleSubmit(onSubmit)} method="POST" className="space-y-4">
                    <InputField
                        name="alertName"
                        label="Alert Name"
                        placeholder="Your alert name"
                        register={register}
                        error={errors.alertName}
                        validation={{required: true, maxLength: 150}}
                    />

                    {/*TODO: If the User didn't select a Stock we can add searching functionality for finding the stock*/}
                    <InputField
                        name="symbol"
                        label="Stock Identifier"
                        placeholder="Your stock - Apple Inc (AAPL)"
                        register={register}
                        error={errors.symbol}
                        value={selectedStock?.symbol}
                        validation={{required: true, maxLength: 5}}
                    />

                    <SelectField
                        name="alertType"
                        label="Alert type"
                        placeholder="Choose type"
                        required
                        error={errors.alertType}
                        control={control}
                        options={ALERT_TYPE_OPTIONS}
                    />

                    <SelectField
                        name="conditionQuality"
                        label="Condition"
                        placeholder="Choose condition"
                        required
                        error={errors.alertType}
                        control={control}
                        options={CONDITION_OPTIONS}
                    />

                    <InputField
                        name="thresholdValue"
                        label="Threshold value"
                        placeholder="Choose threshold value"
                        register={register}
                        error={errors.thresholdValue}
                        validation={{required: true, valueAsNumber: true}}
                    />

                    <SelectField
                        name="frequency"
                        label="Frequency"
                        placeholder="Choose frequency"
                        required
                        error={errors.frequency}
                        control={control}
                        options={FREQUENCY_OPTIONS}
                    />

                    <Button
                        type="submit"
                        size="lg"
                        aria-label="alert-button"
                        disabled={isSubmitting || isLoading}
                        className="w-full yellow-btn mt-4 text-sm leading-5 font-bold! tracking-normal"
                    >
                        {isLoading ? 'Loading...' : buttonLabel}
                    </Button>
                </form>
            </DialogContent>
        </Dialog>
    )
}

export default memo(AlertModal);
