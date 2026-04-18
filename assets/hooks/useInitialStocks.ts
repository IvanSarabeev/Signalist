import {useMemo} from "react";
import {useStocksFetch} from "@/hooks/useStocksFetch";

type initialStocksResponse = { initialStocks: Stocks[], apiErrors: [], isLoading: boolean, error: Error | null };

/**
 * Custom hook that derives UI-ready stock data from the raw API response.
 *
 * Consumes `useStocksFetch` internally and applies business logic:
 * validates the response status, guards against null data, and separates
 * network-level errors from API-level errors returned in the response body.
 *
 * @example
 * const { initialStocks, apiErrors, isLoading, error } = useInitialStocks();
 *
 * if (error) return <p>Network error: {error.message}</p>;
 * if (apiErrors.length) return <p>API error occurred</p>;
 * if (isLoading) return <Spinner />;
 *
 * return <StockList stocks={initialStocks} />;
 */
export function useInitialStocks(): initialStocksResponse {
    const {response, isLoading, error} = useStocksFetch();

    const initialStocks: Stocks[] = useMemo(() => {
        return response?.status && Array.isArray(response.data)
            ? response.data
            : [];
    }, [response?.data])

    const apiErrors = response?.errors ?? [];

    return {initialStocks, apiErrors, isLoading, error};
}
