import React, {FC, memo} from 'react'
import StockLogo from "@/components/StockLogo";
import {Pencil, Trash2} from "lucide-react";

type AlertCardProps = {
    alert: {
        id: number;
        price: string;
        frequency: string;
        condition: "Price >" | "Price <" | "Price =";
    };
    stock: {
        symbol: string;
        company: string;
        price: number;
        exchange: number;
    };
    onEdit: (alert: {
        id: number;
        price: string;
        frequency: string;
        condition: "Price >" | "Price <" | "Price =";
    }) => void;
    onDelete: (id: number) => void;
}

const CONDITION_LABELS = {
    above: "Price >",
    below: "Price <",
    equals: "Price =",
};

const FREQUENCY_OPTIONS = [
    { value: "once_per_minute", label: "Once per minute" },
    { value: "once_per_hour",   label: "Once per hour"   },
    { value: "once_per_day",    label: "Once per day"    },
];

const AlertCard: FC<AlertCardProps> = ({
    alert,
    stock,
    onEdit,
    onDelete
}) => {
    const isPositive = stock.change >= 0;
    const freqLabel = FREQUENCY_OPTIONS.find((f) => f.value === alert.frequency)?.label || "Once per day";
    const condLabel = CONDITION_LABELS[alert.condition] || "Price >";

    return (
        <div className="max-w-92 max-h-38.5 bg-gray-800 border border-gray-700 rounded-xl p-4 space-y-3">
            <div className="flex items-center justify-between gap-3">
                <div className="flex items-center gap-3 min-w-0">
                    <StockLogo symbol={stock.symbol} size={40} />

                    <div className="min-w-0">
                        <p className="text-gray-100 font-semibold text-sm truncate">{stock.company}</p>
                        <p className="text-gray-400 text-xs">
                            ${stock.price.toLocaleString("en-US", { minimumFractionDigits: 2 })}
                        </p>
                    </div>
                </div>
                <div className="text-right shrink-0">
                    <p className="text-gray-300 text-sm font-semibold font-mono">{stock.symbol}</p>
                    <p className={`text-xs font-semibold ${isPositive ? "text-emerald-400" : "text-red-400"}`}>
                        {isPositive ? "+" : ""}{Number(stock.exchange).toFixed(2)}%
                    </p>
                </div>
            </div>

            <div className="border-t border-gray-700/60" />

            <div className="flex gap-y-2 items-center justify-between">
                <div className="flex flex-col items-start">
                    <p className="text-gray-400 text-xs">Alert:</p>
                    <p className="text-gray-100 text-sm font-bold">
                        {condLabel} ${Number.parseFloat(alert.price).toFixed(2)}
                    </p>
                </div>
                <div className="flex items-center justify-between gap-2">
                    <div className="flex flex-col items-center gap-2">
                        <div className="flex gap-2">
                            <button
                                onClick={() => onEdit(alert)}
                                className="text-gray-400 hover:text-gray-200 transition-colors cursor-pointer"
                                aria-label="Edit alert"
                            >
                                <Pencil className="size-3.5" />
                            </button>
                            <button
                                onClick={() => onDelete(alert.id)}
                                className="text-gray-400 hover:text-red-400 transition-colors cursor-pointer"
                                aria-label="Delete alert"
                            >
                                <Trash2 className="size-3.5" />
                            </button>
                        </div>
                        <span className="bg-yellow-500 text-gray-900 text-[10px] font-semibold px-2 py-0.5 rounded-full whitespace-nowrap">
                          {freqLabel}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    )
}

export default memo(AlertCard);
