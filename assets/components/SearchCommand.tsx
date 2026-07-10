import React, {FC, Fragment, memo, useEffect, useState, useCallback} from 'react'
import {CommandDialog, CommandInput} from "@/components/ui/command";
import {Button} from "@/components/ui/button";
import {Loader2} from "lucide-react";
import {stocksSearch} from "@/app/api/stock";
import {useDebounce} from "@/hooks/useDebounce";
import {DialogTitle} from "@/components/ui/dialog";
import SearchCommandList from "@/components/SearchCommandList";
import {Kbd, KbdGroup} from "@/components/ui/kbd";

function useModifierKey() {
    const [modifier, setModifier] = useState<'Ctrl' | '⌘'>('Ctrl');

    useEffect(() => {
        const platform =
            (navigator as any).userAgentData?.platform ?? navigator.platform ?? navigator.userAgent;

        if (/mac/i.test(platform)) {
            setModifier('⌘');
        }
    }, []);

    return modifier;
}

const SHORTCUT_KBD_CLASS = "bg-gray-700 text-gray-300 border-gray-600 h-5 px-1.5 text-[11px] font-medium";

const SearchShortcutHint: FC<{ className?: string }> = ({ className }) => {
    const modifier = useModifierKey();

    return (
        <KbdGroup className={className}>
            <Kbd className={SHORTCUT_KBD_CLASS}>{modifier}</Kbd>
            <span className="text-gray-600 text-xs">+</span>
            <Kbd className={SHORTCUT_KBD_CLASS}>K</Kbd>
        </KbdGroup>
    );
};

const SearchCommand: FC<SearchCommandProps> = ({renderAs = "button", label = "Add stock", initialStocks}) => {
    const [open, setOpen] = useState(false);
    const [searchTerm, setSearchTerm] = useState('');
    const [isLoading, setIsLoading] = useState(false);
    const [searchResults, setSearchResults] = useState<Stocks[]>(initialStocks);

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

    const triggerClassName =
        renderAs === 'text' ? 'search-text' : renderAs === 'shortcut'
            ? 'search-shortcut' : 'search-btn';

    return (
        <Fragment>
            <Button
                type='button'
                className={triggerClassName}
                onClick={() => setOpen((prevState) => !prevState)}
            >
                {renderAs === 'shortcut' ? (
                    <Fragment>
                        <span className="search-shortcut-label">{label}</span>

                        <SearchShortcutHint className="search-shortcut-hint"/>
                    </Fragment>
                ) : (
                    <Fragment>
                        {label}
                        {renderAs === 'text' && <SearchShortcutHint />}
                    </Fragment>
                )}
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
