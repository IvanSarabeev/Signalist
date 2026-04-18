import React, {FC, Fragment, memo, useEffect, useState} from 'react'
import {CommandDialog, CommandInput} from "@/components/ui/command";
import {Button} from "@/components/ui/button";
import {Loader2} from "lucide-react";
import {stocksSearch} from "@/app/api/stock";
import {useDebounce} from "@/hooks/useDebounce";
import {DialogTitle} from "@/components/ui/dialog";
import SearchCommandList from "@/components/SearchCommandList";

const SearchCommand: FC<SearchCommandProps> = ({renderAs = "button", label = "Add stock", initialStocks}) => {
    const [open, setOpen] = useState(false);
    const [searchTerm, setSearchTerm] = useState('');
    const [isLoading, setIsLoading] = useState(false);
    const [searchResults, setSearchResults] = useState<Stocks[]>(initialStocks ?? []);

    const isSearchMode = !!searchTerm.trim();
    const displayStocks = isSearchMode ? searchResults : searchResults?.slice(0, 10);

    useEffect(() => {
        const onKeyDown = (e: KeyboardEvent) => {
            if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                setOpen((prevState) => !prevState);
            }
        };

        window.addEventListener('keydown', onKeyDown);
        return () => window.removeEventListener('keydown', onKeyDown);
    }, []);

    const handleSearch = async () => {
        if (!isSearchMode) {
            setSearchResults(initialStocks ?? []);
            return;
        }

        setIsLoading(true);

        try {
            const searchResult = await stocksSearch(searchTerm.trim().toUpperCase());

            if (searchResult.status && searchResult.data !== null) {
                const data = Array.isArray(searchResult.data)
                    ? searchResult.data
                    : [searchResult.data];

                setSearchResults(data);
            }
        } catch {
            setSearchResults(initialStocks ?? []);
        } finally {
            setIsLoading(false);
        }
    };

    const debouncedSearch = useDebounce(handleSearch, 300);

    useEffect(() => {
        if (!searchTerm.trim()) return;
        debouncedSearch();
    }, [searchTerm]);

    const handleSelectStock = () => {
        setOpen(false);
        setSearchTerm('');
        setSearchResults([]);
    };

    return (
        <Fragment>
            <Button
                type='button'
                className={renderAs === 'text' ? 'search-text' : 'search-btn'}
                onClick={() => setOpen((prevState) => !prevState)}
            >
                {label}
            </Button>

            <CommandDialog open={open} onOpenChange={setOpen}>
                <DialogTitle title='Search for stock' className='hidden'/>

                <div className="search-field">
                    <CommandInput
                        value={searchTerm.toUpperCase()}
                        onValueChange={setSearchTerm}
                        placeholder="Search stocks ..."
                        className="search-input"
                    />
                    {isLoading && <Loader2 className="search-loader"/>}
                </div>

                <SearchCommandList
                    isLoading={isLoading}
                    isSearchMode={isSearchMode}
                    isEmptyResult={displayStocks?.length === 0}
                    loadedStocks={searchResults}
                    onStockSelect={handleSelectStock}
                />
            </CommandDialog>
        </Fragment>
    )
}

export default memo(SearchCommand);
