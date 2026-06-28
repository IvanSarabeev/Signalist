import React, {FC, memo} from 'react'
import StockLogo from "@/components/StockLogo";
import {Pencil, Trash2} from "lucide-react";
import {formatPrice} from "@/lib/helpers";
import {Button} from "@/components/ui/button";
import {Tooltip, TooltipContent, TooltipTrigger} from "@/components/ui/tooltip";

type AlertCardProps = {
    alert: Alert;
    stock: StockWithData;
    onEdit: (alert: Alert) => void;
    onDelete: (id: number) => void;
}

const AlertCard: FC<AlertCardProps> = ({
    alert,
    stock,
    onEdit,
    onDelete
}) => {
    const isPositive = stock.change_percent >= 0;

    return (
        <div className="max-w-92 max-h-38.5 alert-item">
            <div className="alert-details">
                <div className="flex items-center gap-3 min-w-0">
                    <StockLogo symbol={stock.symbol} size={40} />

                    <div className="min-w-0">
                        <p className="text-gray-100 font-semibold text-sm truncate">
                            {stock.name}
                        </p>
                        <p className="text-gray-400 text-base font-bold">
                            ${stock.price.toLocaleString("en-US", { minimumFractionDigits: 2 })}
                        </p>
                    </div>
                </div>

                <div className="text-right shrink-0">
                    <p className="alert-company">
                        {stock.symbol}
                    </p>
                    <p className={`text-xs font-semibold ${isPositive ? "text-emerald-400" : "text-red-400"}`}>
                        {isPositive ? "+" : ""}{stock.change_percent.toFixed(2)}%
                    </p>
                </div>
            </div>

            <div className="border-t border-gray-700/90" />

            <div className="flex items-center justify-between">
                <div className="gap-y-2 flex flex-col items-start">
                    <p className="text-gray-400 text-xs">Alert:</p>
                    <p className="alert-price">
                        Price {alert.condition_symbol} {formatPrice(stock.price, stock.currency)}
                    </p>
                </div>

                <div className="flex items-center justify-between">
                    <div className="flex flex-col items-center gap-2">
                        <div className="flex gap-2">
                            <Tooltip>
                                <TooltipTrigger>
                                    <Button
                                        type="button"
                                        size="icon"
                                        name="edit-btn"
                                        aria-label="Edit alert"
                                        disabled={!alert.is_active}
                                        onClick={() => onEdit(alert)}
                                        className="alert-update-btn"
                                    >
                                        <Pencil className="size-3.5" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    {alert.is_active ? `Edit alert` : "Unable to edit"}
                                </TooltipContent>
                            </Tooltip>

                            <Tooltip>
                                <TooltipTrigger>
                                    <Button
                                        type="button"
                                        size="icon"
                                        name="delete-btn"
                                        aria-label="Delete alert"
                                        disabled={!alert.is_active}
                                        onClick={() => onDelete(alert.id)}
                                        className="alert-delete-btn"
                                    >
                                        <Trash2 className="size-3.5" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent className="text-sm font-medium">
                                    Delete alert
                                </TooltipContent>
                            </Tooltip>
                        </div>

                        <span className="bg-yellow-500 text-gray-900 text-[10px] font-semibold px-2 py-0.5 rounded-md whitespace-nowrap">
                          {alert.frequency_label}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    )
}

export default memo(AlertCard);
