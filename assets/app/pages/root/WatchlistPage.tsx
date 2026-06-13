import {FC, memo, useState} from "react";
import {Button} from "@/components/ui/button";
import {Plus} from "lucide-react";
import WatchlistTable from "@/components/WatchlistTable";
import AddStockModal from "@/components/modals/AddStockModal";
import AddAlertModal from "@/components/modals/AddAlertModal";

const initialStocks = [
    { id: 1, company: "Apple Inc", symbol: "AAPL", price: 233.16, change: 1.54, marketCap: "$3.56T", peRatio: 35.5, starred: true },
    { id: 2, company: "Microsoft Corp", symbol: "MSFT", price: 520.42, change: -0.24, marketCap: "$3.75T", peRatio: 32.6, starred: true },
    { id: 3, company: "Alphabet Inc", symbol: "GOOGL", price: 201.56, change: 2.65, marketCap: "$2.52T", peRatio: 21.5, starred: true },
    { id: 4, company: "Amazon.com Inc", symbol: "AMZN", price: 244.16, change: -1.53, marketCap: "$1.45T", peRatio: 33.5, starred: true },
    { id: 5, company: "Tesla Inc", symbol: "TSLA", price: 339.62, change: 1.72, marketCap: "$1.56T", peRatio: 161.2, starred: true },
    { id: 6, company: "Meta Platforms Inc", symbol: "META", price: 762.96, change: -2.54, marketCap: "$2.63T", peRatio: 45.6, starred: true },
    { id: 7, company: "NVIDIA Corp", symbol: "NVDA", price: 181.46, change: 2.21, marketCap: "$1.36T", peRatio: 16.8, starred: true },
    { id: 8, company: "Netflix Inc", symbol: "NFLX", price: 1214.45, change: -2.62, marketCap: "$4.74T", peRatio: 45.9, starred: true },
    { id: 9, company: "Oracle Corp", symbol: "ORCL", price: 244.63, change: 1.78, marketCap: "$265.1B", peRatio: 58.9, starred: true },
    { id: 10, company: "Salesforce Inc", symbol: "CRM", price: 254.45, change: 1.72, marketCap: "$1.45T", peRatio: 58.9, starred: true },
    { id: 11, company: "Intel Corporation", symbol: "INTC", price: 254.45, change: -2.54, marketCap: "$1.56T", peRatio: 16.8, starred: true },
    { id: 12, company: "Johnson & Johnson", symbol: "JNJ", price: 254.45, change: 2.31, marketCap: "$2.63T", peRatio: 45.9, starred: true },
];

const WatchlistPage: FC = () => {
    const [stocks, setStocks] = useState(initialStocks);
    const [alerts, setAlerts] = useState({});
    const [alertDialogOpen, setAlertDialogOpen] = useState(false);
    const [addStockOpen, setAddStockOpen] = useState(false);
    const [selectedStock, setSelectedStock] = useState(null);
    const [alertPrice, setAlertPrice] = useState("");
    const [newStock, setNewStock] = useState({ company: "", symbol: "", price: "", change: "", marketCap: "", peRatio: "" });

    const toggleStar = (id: number) => {
        setStocks((prev) =>
            prev.map((s) => (s.id === id ? { ...s, starred: !s.starred } : s))
        );
    };

    const removeStock = (id: number) => {
        setStocks((prev) => prev.filter((s) => s.id !== id));
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

    return (
        <div className="min-h-screen bg-gray-950 p-6">
            <div className="flex items-center justify-between mb-6">
                <h1 className="watchlist-title">Watchlist</h1>
                <Button
                    onClick={() => setAddStockOpen(true)}
                    className="watchlist-btn w-auto! px-5"
                >
                    <Plus className="size-4 mr-1.5" />
                    Add Stock
                </Button>
            </div>

            <WatchlistTable
                stocks={stocks}
                toggleStar={toggleStar}
                removeStock={removeStock}
                setAddStockOpen={setAddStockOpen}
                openAlertDialog={openAlertDialog}
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
        </div>
    );
};

export default memo(WatchlistPage);
