import React, {FC, memo, useEffect, useState} from "react";
import WatchlistTable from "@/components/WatchlistTable";
import AddStockModal from "@/components/modals/AddStockModal";
import AddAlertModal from "@/components/modals/AddAlertModal";
import AlertPanel from "@/components/AlertsPanel";
import {deleteWatchlistItem, getWatchlist} from "@/app/api/watchlist";
import {addNotification} from "@/lib/utils";
import {Button} from "@/components/ui/button";
import ConfirmationModal from "@/components/modals/ConfirmationModal";
import {deleteAlert, getAlerts} from "@/app/api/alerts";

type ConfirmState = {
    title: string;
    description: string;
    confirmLabel?: string;
    onConfirm: () => Promise<void> | void;
} | null;

const WatchlistPage: FC = () => {
    const [stocks, setStocks] = useState<StockWithData[]>([]);
    const [pagination, setPagination] = useState({
        page: 1,
        limit: 10,
        totalPages: 1,
        hasNextPage: false,
        hasPreviousPage: false,
    });

    const [alerts, setAlerts] = useState<Alert[]>([]);
    const [alertDialogOpen, setAlertDialogOpen] = useState(false);
    const [addStockOpen, setAddStockOpen] = useState(false);
    const [selectedStock, setSelectedStock] = useState<StockWithData | null>(null);
    const [alertPrice, setAlertPrice] = useState(0);
    const [newStock, setNewStock] = useState({ company: "", symbol: "", price: "", change: "", marketCap: "", peRatio: "" });

    const [alertModalType, setAlertModalType] = useState<"create" | "edit">("create");
    const [selectedAlert, setSelectedAlert] = useState<Alert | null>(null);

    const [confirmState, setConfirmState] = useState<ConfirmState>(null);

    /**
     * Retrieve all watchlist items
     *
     * @return Promise<void>
     */
    const loadStocks: () => Promise<void> = async () => {
        try {
            const watchlistResult = await getWatchlist();

            if (watchlistResult.status && watchlistResult.data.length > 0) {
                setStocks(watchlistResult.data);

                if (Object.keys(watchlistResult.meta).length > 0) {
                    setPagination((prevState) => ({
                        ...prevState,
                        // TODO: How should I access the meta properties - page, limit and etc...
                    }));
                }
            }

        } catch {
            setStocks([]);
        }
    };

    /**
     * Retrieve all available alerts
     *
     * @return Promise<void>
     */
    const loadAlerts: () => Promise<void> = async () => {
        try {
            const alertsResult = await getAlerts();

            if (alertsResult.status && alertsResult.data?.length > 0) {
                setAlerts(alertsResult.data);
            }

            if (Object.keys(alertsResult.meta).length > 0) {
                setPagination((prevState) => ({
                    ...prevState,
                    // TODO: How should I access the meta properties - page, limit and etc...
                }));
            }
        } catch {
            setAlerts([]);
        }
    };

    useEffect(() => {
        Promise.all([
            loadStocks(),
            loadAlerts(),
        ]);
    }, []);

    const toggleStar = (id: number) => {
        setStocks((prev) =>
            prev.map((s) => (s.id === id ? { ...s, starred: !s.starred } : s))
        );
    };

    const removeStock: (stock: StockWithData) => Promise<void> = async (stock: StockWithData) => {
        if (!stock) {
            addNotification({
                type: "error",
                duration: 3000,
                message: "Error",
                description: "Invalid stock. Please try again later!",
            });
            return;
        }

        try {
            await deleteWatchlistItem(stock.symbol);

            setStocks((prev) => prev.filter((s) => s.id !== stock.id));

            addNotification({
                type: "success",
                message: "Success",
                description: `Successfully delete your ${stock.name}`,
                duration: 2500,
            });
        } catch (error: unknown) {
            const message = (error as ApiError)?.message ?? `Unable to delete ${stock.symbol}`;

            addNotification({
                type: "error",
                duration: 3000,
                message: "Error",
                description: `${message}. Please try again later or contact the support center`,
            });
        }
    };

    const openAlertDialog: (stock: StockWithData) => void = (stock: StockWithData) => {
        setSelectedStock(stock);
        setAlertPrice(stock.price);
        setAlertDialogOpen(true);
    };

    const handleAddStock = () => {
        if (!newStock.company || !newStock.symbol) return;
        // const stock = {
        //     id: Date.now(),
        //     company: newStock.company,
        //     symbol: newStock.symbol.toUpperCase(),
        //     price: Number.parseFloat(newStock.price) || 0,
        //     change: Number.parseFloat(newStock.change) || 0,
        //     marketCap: newStock.marketCap || "—",
        //     peRatio: Number.parseFloat(newStock.peRatio) || 0,
        //     starred: false,
        // };
        // setStocks((prev) => [...prev, stock]);
        setNewStock({ company: "", symbol: "", price: "", change: "", marketCap: "", peRatio: "" });
        setAddStockOpen(false);
    };

    const openCreateAlert = () => {
        setAlertModalType("create");
        setSelectedAlert(null);
        setAlertDialogOpen(true);
    };

    const openEditAlert = (alert: Alert) => {
        setAlertModalType("edit");
        setSelectedAlert(alert);
        setAlertDialogOpen(true);
    };

    const onAlertDelete: (id: number) => Promise<void> = async (id: number) => {
        const alert = alerts.find((alert) => alert.id === id);

        if (!alert) {
            addNotification({
                type: "error",
                duration: 3000,
                message: "Error",
                description: "Invalid alert. Please try again later!",
            });
            return;
        }

        try {
            await deleteAlert(id);

            setAlerts((prevState) => prevState.filter((a) => a.id !== alert.id));

            addNotification({
                type: "success",
                message: "Success",
                description: `Successfully delete your alert: ${alert.name}`,
                duration: 2500,
            });
        } catch (error: unknown) {
            const message = (error as ApiError)?.message ?? `Unable to delete ${alert.name} (${alert.stock_symbol})`;

            addNotification({
                type: "error",
                duration: 3000,
                message: "Error",
                description: `${message}. Please try again later or contact the support center`,
            });
        }
    }

    const requestStockDeletion: (stock: StockWithData) => void = (stock: StockWithData) => {
        const alert = alerts.find((a) => a.stock_symbol === stock.symbol);

        setConfirmState({
            title: `Remove ${stock.symbol} from watchlist ?`,
            description: `This will remove ${stock.name} and disable your alert ${alert?.name}.
                If you want you can contact our customer support center.`,
            confirmLabel: "Confirm",
            onConfirm: () => removeStock(stock),
        });
    };

    const requestDeleteAlert: (alert: Alert) => void = (alert: Alert) => {
        setConfirmState({
            title: `Remove "${alert.name}" from alerts ?`,
            description: `This will delete your alert for (${alert.name} - ${alert.stock_symbol}).
                If you want you can contact our customer support center.`,
            confirmLabel: "Confirm",
            onConfirm: () => onAlertDelete(alert.id),
        });
    };

    return (
        <div className="min-h-screen w-full p-6 flex flex-col lg:flex-row gap-2.5 md:gap-4 overflow-hidden">
            <WatchlistTable
                stocks={stocks}
                toggleStar={toggleStar}
                setAddStockOpen={setAddStockOpen}
                openAlertDialog={openAlertDialog}
                requestStockDeletion={requestStockDeletion}
            />

            <AlertPanel
                alerts={alerts}
                stocks={stocks}
                onCreateAlert={openCreateAlert}
                onEditAlert={openEditAlert}
                onDeleteAlert={requestDeleteAlert}
            />

            <AddAlertModal
                type={alertModalType}
                isOpen={alertDialogOpen}
                alertPrice={alertPrice}
                selectedStock={selectedStock}
                setAlertDialogOpen={setAlertDialogOpen}
                setAlerts={setAlerts}
                selectedAlert={selectedAlert}
            />

            <AddStockModal
                isOpen={addStockOpen}
                setAddStockOpen={setAddStockOpen}
                handleAddStock={handleAddStock}
                newStock={newStock}
                setNewStock={setNewStock}
            />

            {confirmState && (
                <ConfirmationModal
                    title={confirmState.title}
                    description={confirmState.description}
                    closeCallback={() => setConfirmState(null)}
                    primaryButton={
                        <Button
                            size="sm"
                            variant="destructive"
                            onClick={async () => {
                                await confirmState?.onConfirm();
                                setConfirmState(null);
                            }}
                            className="confirm-dialog-primary-btn"
                        >
                            {confirmState.confirmLabel ?? "Confirm"}
                        </Button>
                    }
                    secondaryButton={
                        <Button
                            size="sm"
                            variant="secondary"
                            className="confirm-dialog-secondary-btn"
                            onClick={() => setConfirmState(null)}
                        >
                            Cancel
                        </Button>
                    }
                />
            )}
        </div>
    );
};

export default memo(WatchlistPage);
