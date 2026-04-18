import React, {FC, memo} from 'react'
import {CommandEmpty, CommandList} from "@/components/ui/command";
import {Link} from "react-router-dom";
import {Star, TrendingUp} from "lucide-react";

type SearchCommandListProps = {
    isLoading: boolean;
    isSearchMode: boolean;
    isEmptyResult: boolean;
    loadedStocks: Stocks[] | [];
    onStockSelect: () => void;
}

const SearchCommandList: FC<SearchCommandListProps> = ({isLoading, isSearchMode, isEmptyResult, loadedStocks, onStockSelect}) => {
    return (
        <CommandList className="search-list">
            {isLoading && (
                <CommandEmpty className="search-list-empty">Loading stocks ...</CommandEmpty>
            )}

            {isEmptyResult ? (
                <div className="search-list-indicator">
                    {isSearchMode ? 'No results found.' : 'No stocks available.'}
                </div>
            ) : (
                <ul>
                    <div className="search-count">
                        (<strong>{loadedStocks.length || 0}</strong>){" "}
                        {isSearchMode ? 'Search results' : 'Popular stocks'}
                    </div>

                    {loadedStocks.map((stock, index) => (
                        <li key={`${stock.symbol}-${index}`} className='group/item search-item'>
                            <Link
                                to={`/stocks/${stock.symbol}`}
                                onClick={onStockSelect}
                                className='search-item-link'
                            >
                                <TrendingUp className="size-4 text-gray-500 group-hover/item:text-slate-500"/>

                                <div className="flex-1">
                                    <div className="search-item-name group-hover/item:text-gray-800 group-hover/item:font-bold">{stock.name}</div>

                                    <div className="text-sm text-gray-500 group-hover/item:text-gray-600">
                                        {stock.symbol} | {stock.exchange} | {stock.type}
                                    </div>
                                </div>

                                <Star className='size-5 text-slate-100 group-hover/item:size-6 group-hover/item:fill-white'/>
                            </Link>
                        </li>
                    ))}
                </ul>
            )}
        </CommandList>
    )
}

export default memo(SearchCommandList);
