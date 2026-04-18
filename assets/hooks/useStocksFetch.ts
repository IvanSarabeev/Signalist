import {useEffect, useState} from "react";
import {stocksSearch} from "@/app/api/stock";
import {addNotification} from "@/lib/utils";

type StocksFetchResult = { response: StocksResponse | null, isLoading: boolean, error: Error | null };

/**
 * Custom hook for fetching raw stock data from the API.
 *
 * @param {string} [symbol=''] - Optional stock symbol to filter results. Defaults to an empty string (returns all stocks).
 */
export function useStocksFetch(symbol: string = ''): StocksFetchResult {
    const [response, setResponse] = useState<StocksResponse | null>(null);
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState<Error | null>(null);

    const fetchStocks = async () => {
        setIsLoading(true);
        setError(null);

        try {
            const response = await stocksSearch(symbol);
            setResponse(response);
        } catch (error) {
            setError(error instanceof Error ? error : new Error('Request failed'));
            addNotification({
                type: 'error',
                message: 'No stocks were found.',
                description: 'Please try again later or feel free to contact our customer support.',
                duration: 3000
            });
        } finally {
            setIsLoading(false);
        }
    };

    useEffect(() => {
        fetchStocks();
    }, [symbol]);

    return {response, isLoading, error};
}
