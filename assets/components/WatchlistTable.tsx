import React, {FC, memo} from 'react'
import {Table, TableBody, TableCell, TableHead, TableHeader, TableRow} from "@/components/ui/table";
import {Bell, Plus, Star, Trash2, TrendingDown, TrendingUp} from "lucide-react";
import {Badge} from "@/components/ui/badge";
import {Button} from "@/components/ui/button";
import {WATCHLIST_TABLE_HEADER} from "@/lib/constants";
import {formatMarketCapValue, formatPrice, getChangeColorClas} from "@/lib/helpers";
import WatchlistButton from "@/components/WatchlistButton";

type WatchlistTableProps = {
    stocks: StockWithData[];
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
                    size="sm"
                    onClick={() => setAddStockOpen(true)}
                    className="yellow-btn w-auto! px-5 font-bold! text-base!"
                >
                    Add Stock
                </Button>
            </div>

            <section aria-label="watchlist-section" className="watchlist-table">
                <Table aria-label="watchlist-table">
                    <TableHeader>
                        <TableRow className="border-gray-700 hover:bg-transparent bg-gray-600/90 pl-4 pr-2">
                            {WATCHLIST_TABLE_HEADER.map((header, index) => {
                                return (
                                    <TableHead
                                        key={`${header}-${index}`}
                                        aria-label={header}
                                        className="font-monospace text-base font-semibold text-gray-400 text-wrap"
                                    >
                                        {header}
                                    </TableHead>
                                )
                            })}
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        {stocks.map((stock) => {
                            const changeColor = getChangeColorClas(stock.change_percent);
                            const isPositive = stock.change_percent >= 0;
                            const hasAlert = false;

                            return (
                                <TableRow
                                    key={stock.id}
                                    className="border-gray-700/50 hover:bg-gray-700/20 transition-colors group"
                                >
                                    <TableCell className="px-2 w-10 border border-gray-600/90">
                                        <div className="watchlist-icon">
                                            <Star className={`star-icon ${stock.added_at ? "fill-yellow-500 text-yellow-500" : "text-gray-400"}`}/>
                                        </div>
                                    </TableCell>

                                    <TableCell className="pl-4 pr-2 border border-gray-600/90">
                                        <span className="text-white font-medium text-base text-wrap text-center">
                                            {stock.name}
                                        </span>
                                    </TableCell>

                                    <TableCell className="pl-4 pr-2 border border-gray-600/90">
                                        <Badge className="bg-gray-700 text-white font-medium text-base border-0 font-mono px-2 py-0.5 rounded">
                                            {stock.symbol}
                                        </Badge>
                                    </TableCell>

                                    <TableCell className="text-white font-medium text-base border border-gray-600/90 pl-4 pr-2">
                                        {formatPrice(stock.price, stock.currency)}
                                    </TableCell>

                                    <TableCell className="pl-4 pr-2 border border-gray-600/90">
                                        <span className={`flex items-center gap-1 text-sm font-semibold ${changeColor}`}>
                                            {isPositive ? (
                                                <TrendingUp className="size-3.5" />
                                            ) : (
                                                <TrendingDown className="size-3.5" />
                                            )}
                                            {stock.change_percent.toFixed(2)}%
                                        </span>
                                    </TableCell>

                                    <TableCell className="text-white font-medium text-base pl-4 pr-2 border border-gray-600/90">
                                        {formatMarketCapValue(Number(stock.market_cap))}
                                    </TableCell>

                                    <TableCell className="text-white font-medium text-base pl-4 pr-2 border border-gray-600/90">
                                        {stock.pe_ratio.toFixed(1)}
                                    </TableCell>

                                    <TableCell className="text-right pl-4 pr-2 border border-gray-600/90">
                                        <div className="flex items-center justify-end gap-2">
                                            <Button
                                                size="sm"
                                                aria-label={`Remove ${stock.name}`}
                                                onClick={() => removeStock(stock.id)}
                                                className="watchlist-remove transition-opacity cursor-pointer"
                                            >
                                                <Trash2 className="trash-icon" />
                                                Remove
                                            </Button>

                                            <Button
                                                size="sm"
                                                onClick={() => openAlertDialog(stock)}
                                                className={`text-xs h-8 px-3 font-semibold rounded transition-all cursor-pointer ${
                                                    hasAlert
                                                        ? "watchlist-remove bg-red-500 hover:bg-red-600 text-white border-0"
                                                        : "add-alert"
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
                                            aria-label="Add watchlist stock"
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
