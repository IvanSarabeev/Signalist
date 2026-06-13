import React, {FC, Fragment, memo} from 'react'
import {Table, TableBody, TableCell, TableHead, TableHeader, TableRow} from "@/components/ui/table";
import {Bell, Plus, Star, Trash2, TrendingDown, TrendingUp} from "lucide-react";
import {Badge} from "@/components/ui/badge";
import {Button} from "@/components/ui/button";

type WatchlistTableProps = {
    stocks: {
        id: number
        company: string
        symbol: string
        price: number
        change: number
        marketCap: string
        peRatio: number
        starred: boolean
    }[];
    toggleStar: (id: number) => void;
    removeStock: (id: number) => void;
    openAlertDialog: (stock: Object) => void;
    setAddStockOpen: (value: boolean) => void;
}

const WatchlistTable: FC<WatchlistTableProps> = ({
    stocks,
    toggleStar,
    removeStock,
    openAlertDialog,
    setAddStockOpen,
}) => {
    return (
        <section className="flex flex-col">
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

            <section className="watchlist-table">
                <Table>
                    <TableHeader>
                        <TableRow className="border-gray-700 hover:bg-transparent">
                            <TableHead className="w-10 text-gray-400 font-medium text-sm pl-4" />
                            <TableHead className="text-gray-400 font-medium text-sm">Company</TableHead>
                            <TableHead className="text-gray-400 font-medium text-sm">Symbol</TableHead>
                            <TableHead className="text-gray-400 font-medium text-sm">Price</TableHead>
                            <TableHead className="text-gray-400 font-medium text-sm">Change</TableHead>
                            <TableHead className="text-gray-400 font-medium text-sm">Market Cap</TableHead>
                            <TableHead className="text-gray-400 font-medium text-sm">P/E Ratio</TableHead>
                            <TableHead className="text-gray-400 font-medium text-sm text-right pr-4">Alert</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {stocks.map((stock) => {
                            const isPositive = stock.change >= 0;
                            const hasAlert = true;
                            return (
                                <TableRow
                                    key={stock.id}
                                    className="border-gray-700/50 hover:bg-gray-700/20 transition-colors group"
                                >
                                    <TableCell className="pl-4 pr-2 w-10">
                                        <button
                                            onClick={() => toggleStar(stock.id)}
                                            className={`watchlist-icon-btn ${stock.starred ? "watchlist-icon-added" : ""}`}
                                            aria-label={stock.starred ? "Remove from favorites" : "Add to favorites"}
                                        >
                                            <div className="watchlist-icon">
                                                <Star className={`star-icon ${stock.starred ? "fill-yellow-500 text-yellow-500" : "text-gray-400"}`}/>
                                            </div>
                                        </button>
                                    </TableCell>

                                    <TableCell>
                                        <div className="flex items-center gap-2">
                                            <span className="text-gray-100 font-medium text-sm">{stock.company}</span>
                                        </div>
                                    </TableCell>

                                    <TableCell>
                                        <Badge className="bg-gray-700 text-gray-300 border-0 font-mono text-xs px-2 py-0.5 rounded">
                                            {stock.symbol}
                                        </Badge>
                                    </TableCell>

                                    <TableCell className="text-gray-100 font-semibold text-sm">
                                        ${stock.price.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                                    </TableCell>

                                    <TableCell>
                                        <span
                                            className={`flex items-center gap-1 text-sm font-semibold ${
                                                isPositive ? "text-emerald-400" : "text-red-400"
                                            }`}
                                        >
                                            {isPositive ? (
                                                <TrendingUp className="size-3.5" />
                                            ) : (
                                                <TrendingDown className="size-3.5" />
                                            )}
                                            {isPositive ? "+" : ""}
                                            {stock.change.toFixed(2)}%
                                        </span>
                                    </TableCell>

                                    <TableCell className="text-gray-300 text-sm">
                                        {stock.marketCap}
                                    </TableCell>

                                    <TableCell className="text-gray-300 text-sm">
                                        {stock.peRatio}
                                    </TableCell>

                                    <TableCell className="text-right pr-4">
                                        <div className="flex items-center justify-end gap-2">
                                            <button
                                                aria-label={`Remove ${stock.company}`}
                                                onClick={() => removeStock(stock.id)}
                                                className="opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer"
                                            >
                                                <Trash2 className="trash-icon" />
                                            </button>

                                            <Button
                                                size="sm"
                                                onClick={() => openAlertDialog(stock)}
                                                className={`text-xs h-8 px-3 font-semibold rounded transition-all cursor-pointer ${
                                                    hasAlert
                                                        ? "watchlist-remove bg-red-500 hover:bg-red-600 text-white border-0"
                                                        : "watchlist-btn w-auto!"
                                                }`}
                                            >
                                                <Bell className="size-3.5 mr-1" />
                                                {hasAlert ? "Remove Alert" : "Add Alert"}
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            );
                        })}

                        {stocks.length === 0 && (
                            <TableRow>
                                <TableCell colSpan={8}>
                                    <div className="watchlist-empty-container">
                                        <Star className="watchlist-star" />

                                        <p className="text-gray-400 text-sm">Your watchlist is empty.</p>

                                        <Button
                                            onClick={() => setAddStockOpen(true)}
                                            className="watchlist-btn w-auto! px-6 mt-2"
                                        >
                                            <Plus className="size-4 mr-1.5" />
                                            Add your first stock
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        )}
                    </TableBody>
                </Table>
            </section>
        </section>
    )
}

export default memo(WatchlistTable);
