import React, {FC, memo} from 'react'
import AlertCard from "@/components/AlertCard";
import {Bell, Plus} from "lucide-react";
import {Button} from "@/components/ui/button";

type AlertPanelProps = {
    alerts: {
        id: number;
        stockId: number;
        price: string;
        frequency: string;
        condition: "Price >" | "Price <" | "Price =";
    }[];
    stocks: {
        id: number;
        symbol: string;
        company: string;
        price: number;
        change: number;
    }[];
    onCreateAlert: () => void;
    onEditAlert: (alert: {}) => void;
    onDeleteAlert: (id: number) => void;
}

const AlertsPanel: FC<AlertPanelProps> = ({
    alerts,
    stocks,
    onCreateAlert,
    onEditAlert,
    onDeleteAlert
}) => {
    return (
        <div className="watchlist-alerts">
            <div className="flex items-center justify-between w-full">
                <h2 className="watchlist-title">Alerts</h2>
                <Button
                    onClick={onCreateAlert}
                    className="watchlist-btn w-auto! px-4"
                >
                    <Plus className="size-4 mr-1" />
                    Create Alert
                </Button>
            </div>

            {/* Cards */}
            {alerts.length === 0 ? (
                <div className="watchlist-empty w-full mt-6">
                    <Bell className="h-12 w-12 text-gray-600 mb-3" />
                    <p className="text-gray-400 text-sm">No alerts yet.</p>
                    <p className="text-gray-500 text-xs mt-1">
                        Create an alert to get notified when a stock hits your target price.
                    </p>
                </div>
            ) : (
                <div className="space-y-3 w-full">
                    {alerts.map((alert) => {
                        const stock = stocks.find((s) => s.id === alert.stockId);
                        if (!stock) return null;
                        return (
                            <AlertCard
                                key={alert.id}
                                alert={alert}
                                stock={stock}
                                onEdit={onEditAlert}
                                onDelete={onDeleteAlert}
                            />
                        );
                    })}
                </div>
            )}
        </div>
    );
}

export default memo(AlertsPanel);
