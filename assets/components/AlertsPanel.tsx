import React, {FC, memo} from 'react'
import AlertCard from "@/components/AlertCard";
import {Bell} from "lucide-react";
import {Button} from "@/components/ui/button";

type AlertPanelProps = {
    alerts: Alert[];
    stocks: StockWithData[];
    onCreateAlert: () => void;
    onEditAlert: (alert: Alert) => void;
    onDeleteAlert: (alert: Alert) => void;
}

const AlertsPanel: FC<AlertPanelProps> = ({
    alerts,
    stocks,
    onCreateAlert,
    onEditAlert,
    onDeleteAlert
}) => {
    return (
        <section className="watchlist-alerts">
            <div className="flex items-center justify-between w-full">
                <h2 className="watchlist-title">Alerts</h2>

                <Button
                    type="button"
                    aria-label="Create new alert"
                    onClick={onCreateAlert}
                    className="watchlist-btn font-bold w-auto! px-4"
                >
                    Create Alert
                </Button>
            </div>

            <div className="alert-list">
                {(alerts.length === 0 || stocks.length === 0) ? (
                    <div className="watchlist-empty w-full mt-6">
                        <Bell className="size-12 text-gray-600 mb-3" />
                        <p className="alert-title">No alerts yet.</p>
                        <p className="text-gray-500 text-xs mt-1">
                            Create an alert to get notified when a stock hits your target price.
                        </p>
                    </div>
                ) : (
                    alerts.map((alert) => {
                        const stock = stocks.find((stock) => stock.symbol === alert.stock_symbol);
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
                    })
                )}
            </div>
        </section>
    );
}

export default memo(AlertsPanel);
