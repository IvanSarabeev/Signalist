import React, {FC, memo, useEffect, useState} from "react";
import WatchlistTable from "@/components/WatchlistTable";
import AddStockModal from "@/components/modals/AddStockModal";
import AddAlertModal from "@/components/modals/AddAlertModal";
import AlertPanel from "@/components/AlertsPanel";
import {deleteWatchlistItem, getWatchlist} from "@/app/api/watchlist";
import {addNotification} from "@/lib/utils";
import {Button} from "@/components/ui/button";
import {Trash2} from "lucide-react";
import ConfirmationModal from "@/components/modals/ConfirmationModal";
import {getAlerts} from "@/app/api/alerts";

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
    const [selectedStock, setSelectedStock] = useState(null);
    const [alertPrice, setAlertPrice] = useState("");
    const [newStock, setNewStock] = useState({ company: "", symbol: "", price: "", change: "", marketCap: "", peRatio: "" });
    const [isOpen, setIsOpen] = useState(false);
    const [confirmStock, setConfirmStock] = useState<StockWithData | null>(null);

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
        loadStocks();
        loadAlerts();
    }, []);

    const toggleStar = (id: number) => {
        setStocks((prev) =>
            prev.map((s) => (s.id === id ? { ...s, starred: !s.starred } : s))
        );
    };

    const removeStock = async (stock: StockWithData) => {
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

    const openAlertDialog = (stock) => {
        setSelectedStock(stock);
        setAlertPrice(alerts[stock.id]?.price || "");
        setAlertDialogOpen(true);
    };

    const saveAlert = () => {
        if (selectedStock && alertPrice) {
            setAlerts((prev) => ({ ...prev, [selectedStock.id]: { price: alertPrice } }));
        } else if (selectedStock) {
            const updated = { ...alerts };
            delete updated[selectedStock.id];
            setAlerts(updated);
        }
        setAlertDialogOpen(false);
    };

    const handleAddStock = () => {
        if (!newStock.company || !newStock.symbol) return;
        const stock = {
            id: Date.now(),
            company: newStock.company,
            symbol: newStock.symbol.toUpperCase(),
            price: Number.parseFloat(newStock.price) || 0,
            change: Number.parseFloat(newStock.change) || 0,
            marketCap: newStock.marketCap || "—",
            peRatio: Number.parseFloat(newStock.peRatio) || 0,
            starred: false,
        };
        setStocks((prev) => [...prev, stock]);
        setNewStock({ company: "", symbol: "", price: "", change: "", marketCap: "", peRatio: "" });
        setAddStockOpen(false);
    };

    const openCreateAlert = () => { setAlertDialogOpen(true); };

    const openEditAlert = (alert) => { setAlertDialogOpen(true); };

    const deleteAlert = (id: number) => {
        console.log('Deleted an Alert');
    }

    return (
        <div className="min-h-screen w-full p-6 flex flex-col lg:flex-row gap-2.5 md:gap-4 overflow-hidden">
            <WatchlistTable
                stocks={stocks}
                toggleStar={toggleStar}
                setAddStockOpen={setAddStockOpen}
                openAlertDialog={openAlertDialog}
                setIsOpen={setIsOpen}
                setConfirmStock={setConfirmStock}
            />

            <AlertPanel
                alerts={alerts}
                stocks={stocks}
                onCreateAlert={openCreateAlert}
                onEditAlert={openEditAlert}
                onDeleteAlert={deleteAlert}
            />

            <AddAlertModal
                isOpen={alertDialogOpen}
                setAlertDialogOpen={setAlertDialogOpen}
                alerts={alerts}
                setAlerts={setAlerts}
                saveAlert={saveAlert}
                selectedStock={selectedStock}
                alertPrice={alertPrice}
                setAlertPrice={setAlertPrice}
            />

            <AddStockModal
                isOpen={addStockOpen}
                setAddStockOpen={setAddStockOpen}
                handleAddStock={handleAddStock}
                newStock={newStock}
                setNewStock={setNewStock}
            />

            {(isOpen && confirmStock !== null) && (
                <ConfirmationModal
                    title={`Remove ${confirmStock.symbol} from watchlist?`}
                    description={`This will permanently remove ${confirmStock.name}. You can always add it back later.`}
                    closeCallback={() => setConfirmStock(null)}
                    primaryButton={
                        <Button
                            size="sm"
                            variant="destructive"
                            onClick={async () => {
                                await removeStock(confirmStock);
                                setConfirmStock(null);
                            }}
                        >
                            <Trash2 className="size-4" />
                            Remove
                        </Button>
                    }
                    secondaryButton={
                        <Button size="sm" variant="outline" onClick={() => setConfirmStock(null)}>
                            Cancel
                        </Button>
                    }
                />
            )}
        </div>
    );
};

export default memo(WatchlistPage);
