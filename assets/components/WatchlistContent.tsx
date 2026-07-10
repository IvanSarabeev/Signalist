import React, {FC, use, useState, memo} from 'react'
import {AlertsResource, WatchlistsResource} from "@/app/pages/root/WatchlistPage";
import {addNotification} from "@/lib/utils";
import {deleteWatchlistItem} from "@/app/api/watchlist";
import {deleteAlert} from "@/app/api/alerts";
import WatchlistTable from "@/components/WatchlistTable";
import AlertPanel from "@/components/AlertsPanel";
import AlertModal from "@/components/modals/AlertModal";
import AddStockModal from "@/components/modals/AddStockModal";
import ConfirmationModal from "@/components/modals/ConfirmationModal";
import {Button} from "@/components/ui/button";

type WatchlistContentProps = {
    watchlistsPromise: Promise<WatchlistsResource>;
    alertsPromise: Promise<AlertsResource>;
};

type ConfirmState = {
    title: string;
    description: string;
    confirmLabel?: string;
    onConfirm: () => Promise<void> | void;
} | null;

const WatchlistContent: FC<WatchlistContentProps> = ({ watchlistsPromise, alertsPromise }) => {
    const watchlistsResource = use(watchlistsPromise);
    const alertsResource = use(alertsPromise);

    const [watchlistStocks, setWatchlistStocks] = useState<StockWithData[]>(watchlistsResource.data);
    const [alerts, setAlerts] = useState<Alert[]>(alertsResource.data);

    const [alertDialogOpen, setAlertDialogOpen] = useState(false);
    const [addStockOpen, setAddStockOpen] = useState(false);
    const [selectedStock, setSelectedStock] = useState<StockWithData | null>(null);
    const [alertPrice, setAlertPrice] = useState(0);
    const [newStock, setNewStock] = useState({ company: "", symbol: "", price: "", change: "", marketCap: "", peRatio: "" });

    const [alertModalType, setAlertModalType] = useState<"create" | "edit">("create");
    const [selectedAlert, setSelectedAlert] = useState<Alert | null>(null);

    const [confirmState, setConfirmState] = useState<ConfirmState>(null);

    // TODO: Introduce toggle star logic
    const toggleStar = (id: number) => {
        setWatchlistStocks((prev) =>
            prev.map((s) => (s.id === id ? { ...s, starred: !s.id } : s))
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

            setWatchlistStocks((prev) => prev.filter((s) => s.id !== stock.id));

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
        // setWatchlistStocks((prev) => [...prev, stock]);
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
    };

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
                stocks={watchlistStocks}
                toggleStar={toggleStar}
                setAddStockOpen={setAddStockOpen}
                openAlertDialog={openAlertDialog}
                requestStockDeletion={requestStockDeletion}
            />

            <AlertPanel
                alerts={alerts}
                stocks={watchlistStocks}
                onCreateAlert={openCreateAlert}
                onEditAlert={openEditAlert}
                onDeleteAlert={requestDeleteAlert}
            />

            <AlertModal
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
    )
}

export default memo(WatchlistContent);
