import React, {FC, SetStateAction, memo} from 'react'
import {Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle} from "@/components/ui/dialog";
import {Input} from "@/components/ui/input";
import {Button} from "@/components/ui/button";
import {Plus} from "lucide-react";

type AddStockModalProps = {
    isOpen: boolean;
    setAddStockOpen: (value: SetStateAction<boolean>) => void;
    handleAddStock: () => void;
    newStock: {
        company: string;
        symbol: string;
        price: string;
        change: string;
        marketCap: string;
        peRatio: string;
    };
    setNewStock: (value: SetStateAction<{
        company: string
        symbol: string
        price: string
        change: string
        marketCap: string
        peRatio: string
    }>) => void
};

const AddStockModal: FC<AddStockModalProps> = ({
    isOpen,
    setAddStockOpen,
    handleAddStock,
    newStock,
    setNewStock,
}) => {
    // @ts-ignore
    return (
        <Dialog open={isOpen} onOpenChange={setAddStockOpen}>
            <DialogContent className="bg-gray-900 border-gray-700 text-gray-100 sm:max-w-md">
                <DialogHeader>
                    <DialogTitle className="text-gray-100">Add Stock</DialogTitle>
                </DialogHeader>
                <div className="space-y-3 py-2">
                    {[
                        { key: "company", label: "Company Name", placeholder: "e.g. Apple Inc" },
                        { key: "symbol", label: "Symbol", placeholder: "e.g. AAPL" },
                        { key: "price", label: "Price ($)", placeholder: "e.g. 233.16", type: "number" },
                        { key: "change", label: "Change (%)", placeholder: "e.g. 1.54 or -0.24", type: "number" },
                        { key: "marketCap", label: "Market Cap", placeholder: "e.g. $3.56T" },
                        { key: "peRatio", label: "P/E Ratio", placeholder: "e.g. 35.5", type: "number" },
                    ].map(({ key, label, placeholder, type }) => (
                        <div key={key} className="space-y-1">
                            <label className="text-xs text-gray-400">{label}</label>
                            <Input
                                type={type || "text"}
                                placeholder={placeholder}
                                // value={newStock[key]}
                                value={newStock.symbol}
                                onChange={(e) => setNewStock((prev) => ({ ...prev, [key]: e.target.value }))}
                                className="bg-gray-800 border-gray-600 text-gray-100 placeholder:text-gray-500 focus:border-yellow-500 h-9 text-sm"
                            />
                        </div>
                    ))}
                </div>
                <DialogFooter className="gap-2">
                    <Button
                        variant="ghost"
                        onClick={() => setAddStockOpen(false)}
                        className="text-gray-400 hover:text-gray-200 hover:bg-gray-800"
                    >
                        Cancel
                    </Button>
                    <Button
                        onClick={handleAddStock}
                        disabled={!newStock.company || !newStock.symbol}
                        className="watchlist-btn w-auto! px-5 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <Plus className="size-4 mr-1.5" />
                        Add Stock
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    )
}

export default memo(AddStockModal);
