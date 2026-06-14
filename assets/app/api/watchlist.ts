import api from "@/lib/axiosApi";

export async function getWatchlist(): Promise<WatchlistResponse> {
    return api.get(`/watchlist`);
}

export async function addWatchlistItem(symbol: string): Promise<AddWatchlistItemResponse> {
    return api.post(`/watchlist/${symbol}`);
}

export async function deleteWatchlistItem(symbol: string): Promise<void> {
    return api.delete(`/watchlist/${symbol}`);
}
