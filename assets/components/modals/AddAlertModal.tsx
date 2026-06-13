import React, {FC, memo, SetStateAction} from 'react'
import {Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle} from "@/components/ui/dialog";
import {Input} from "@/components/ui/input";
import {Button} from "@/components/ui/button";

type AddAlertModalProps = {
    isOpen: boolean;
    setAlertDialogOpen:  (value: boolean) => void;
    saveAlert: () => void;
    alerts: {};
    selectedStock: null|{id: number; symbol: string; price: number; currency: string;};
    alertPrice: string;
    setAlertPrice:  (value: SetStateAction<string>) => void;
    setAlerts: (value: SetStateAction<{}>) => void;
}

const AddAlertModal: FC<AddAlertModalProps> = ({
    isOpen,
    setAlertDialogOpen,
    saveAlert,
    selectedStock,
    alertPrice,
    setAlertPrice,
    alerts,
    setAlerts,
}) => {
    return (
        <Dialog open={isOpen} onOpenChange={setAlertDialogOpen}>
            <DialogContent className="bg-gray-900 border-gray-700 text-gray-100 sm:max-w-md">
                <DialogHeader>
                    <DialogTitle className="text-gray-100">
                        {Object.keys(alerts).length > 0 ? "Edit Alert" : "Add Alert"} — {selectedStock?.symbol}
                    </DialogTitle>
                </DialogHeader>

                <div className="space-y-4 py-2">
                    <div className="flex items-center justify-between text-sm text-gray-400">
                        <span>Current price</span>

                        <span className="text-gray-100 font-semibold">
                            {selectedStock?.currency}{" "}
                            {selectedStock?.price.toLocaleString("en-US", { minimumFractionDigits: 2 })}
                        </span>
                    </div>
                    <div className="space-y-1.5">
                        <label htmlFor="alert-price-id" className="text-sm text-gray-400">
                            Alert when price reaches
                        </label>

                        <Input
                            id="alert-price-id"
                            type="number"
                            placeholder="e.g. 250.00"
                            value={alertPrice}
                            onChange={(e) => setAlertPrice(e.target.value)}
                            className="bg-gray-800 border-gray-600 text-gray-100 placeholder:text-gray-500 focus:border-yellow-500"
                        />
                    </div>
                </div>

                <DialogFooter className="gap-2">
                    <Button
                        variant="ghost"
                        onClick={() => setAlertDialogOpen(false)}
                        className="text-gray-400 hover:text-gray-200 hover:bg-gray-800"
                    >
                        Cancel
                    </Button>
                    {alerts && (
                        <Button
                            onClick={() => {
                                const updated = { ...alerts };
                                setAlerts(updated);
                                setAlertDialogOpen(false);
                            }}
                            className="watchlist-remove bg-red-500 hover:bg-red-600 text-white border-0"
                        >
                            Remove Alert
                        </Button>
                    )}
                    <Button onClick={saveAlert} className="watchlist-btn w-auto! px-5">
                        Save Alert
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    )
}

export default memo(AddAlertModal);
